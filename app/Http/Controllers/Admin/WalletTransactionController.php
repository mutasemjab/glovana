<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Provider;
use App\Models\ProviderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WalletTransactionController extends Controller
{
      public function index()
    {
        $transactions = WalletTransaction::with(['user', 'provider', 'admin'])->orderBy('created_at', 'desc')->get();
        $users = User::all();
        $providers = Provider::with('providerTypes')->get();
        $providerTypes = ProviderType::all(); // Add this line
        
        return view('admin.wallet_transactions.index', compact('transactions', 'users', 'providers', 'providerTypes'));
    }

    /**
     * Show the form for creating a new transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $providers = Provider::all();
        return view('admin.wallet_transactions.create', compact('users', 'providers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:user,provider',
            'entity_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'type_of_transaction' => 'required|in:1,2',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('wallet_transactions.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Prepare transaction data
        $transactionData = [
            'amount' => $request->amount,
            'type_of_transaction' => $request->type_of_transaction,
            'note' => $request->note,
            'admin_id' => Auth::id(), // Current logged in admin
        ];

        // Set the appropriate entity (user or provider)
        if ($request->entity_type == 'user') {
            $user = User::findOrFail($request->entity_id);
            $transactionData['user_id'] = $user->id;
            $transactionData['provider_id'] = null;
            
            // Update user balance
            if ($request->type_of_transaction == 1) {
                // Add to balance
                $user->balance += $request->amount;
            } else {
                // Withdraw from balance
                if ($user->balance < $request->amount) {
                    return redirect()
                        ->route('wallet_transactions.create')
                        ->with('error', __('messages.Insufficient_Balance'))
                        ->withInput();
                }
                $user->balance -= $request->amount;
            }
            $user->save();
        } else {
            $provider = Provider::findOrFail($request->entity_id);
            $transactionData['provider_id'] = $provider->id;
            $transactionData['user_id'] = null;
            
            // Update provider balance
            if ($request->type_of_transaction == 1) {
                // Add to balance
                $provider->balance += $request->amount;
            } else {
                // Withdraw from balance
                if ($provider->balance < $request->amount) {
                    return redirect()
                        ->route('wallet_transactions.create')
                        ->with('error', __('messages.Insufficient_Balance'))
                        ->withInput();
                }
                $provider->balance -= $request->amount;
            }
            $provider->save();
        }

        // Create the transaction
        WalletTransaction::create($transactionData);

        return redirect()
            ->route('wallet_transactions.index')
            ->with('success', __('messages.Transaction_Created_Successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = WalletTransaction::with(['user', 'provider', 'admin'])->findOrFail($id);
        return view('admin.wallet_transactions.show', compact('transaction'));
    }

    /**
     * Filter transactions by entity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|in:all,user,provider',
            'entity_id' => 'nullable|numeric',
            'provider_type_id' => 'nullable|numeric|exists:provider_types,id',
            'transaction_type' => 'nullable|in:all,1,2',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('wallet_transactions.index')
                ->withErrors($validator);
        }

        $query = WalletTransaction::with(['user', 'provider.providerTypes', 'admin']);

        // Filter by entity type and ID
        if ($request->entity_type == 'user' && $request->entity_id) {
            $query->where('user_id', $request->entity_id);
        } elseif ($request->entity_type == 'provider') {
            // Filter by provider type if specified
            if ($request->provider_type_id) {
                $query->whereHas('provider.providerTypes', function($q) use ($request) {
                    $q->where('provider_types.id', $request->provider_type_id);
                });
            }
            
            // Filter by specific provider if specified
            if ($request->entity_id) {
                $query->where('provider_id', $request->entity_id);
            } else {
                // Show all provider transactions
                $query->whereNotNull('provider_id');
            }
        } elseif ($request->entity_type == 'user') {
            $query->whereNotNull('user_id');
        }

        // Filter by transaction type
        if ($request->transaction_type && $request->transaction_type != 'all') {
            $query->where('type_of_transaction', $request->transaction_type);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get the filtered transactions
        $transactions = $query->orderBy('created_at', 'desc')->get();
        
        // Get users, providers, and provider types for the filter dropdowns
        $users = User::all();
        $providers = Provider::with('providerTypes')->get();
        $providerTypes = ProviderType::all();

        return view('admin.wallet_transactions.index', compact('transactions', 'users', 'providers', 'providerTypes'));
    }

}