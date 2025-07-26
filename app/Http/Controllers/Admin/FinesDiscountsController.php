<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FineDiscount;
use App\Models\FineSetting;
use App\Models\User;
use App\Models\Provider;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class FinesDiscountsController extends Controller
{
    /**
     * Display a listing of fines and discounts
     */
    public function index(Request $request)
    {
        $query = FineDiscount::with(['user', 'provider', 'appointment', 'admin']);


        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by entity type
        if ($request->filled('entity_type')) {
            if ($request->entity_type === 'user') {
                $query->whereNotNull('user_id');
            } elseif ($request->entity_type === 'provider') {
                $query->whereNotNull('provider_id');
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('provider', function($pq) use ($search) {
                      $pq->where('name_of_manager', 'like', "%{$search}%");
                  });
            });
        }

        $finesDiscounts = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total_fines' => FineDiscount::count(),
            'pending_amount' => FineDiscount::pending()->sum('amount'),
            'applied_amount' => FineDiscount::applied()->sum('amount'),
            'total_revenue_impact' => FineDiscount::applied()->sum('amount'),
        ];

        return view('admin.fines-discounts.index', compact('finesDiscounts', 'stats'));
    }

    /**
     * Show manual fine/discount creation form
     */
    public function create(Request $request)
    {
        $users = User::where('activate', 1)->get();
        $providers = Provider::where('activate', 1)->get();
        
        $preselected = [
            'entity_type' => $request->get('entity_type'),
            'entity_id' => $request->get('entity_id'),
        ];

        return view('admin.fines-discounts.create', compact('users', 'providers', 'preselected'));
    }

    /**
     * Store manual fine/discount
     */
    public function store(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:user,provider',
            'entity_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'apply_immediately' => 'nullable|boolean'
        ]);

        // Validate entity exists
        if ($request->entity_type === 'user') {
            $entity = User::findOrFail($request->entity_id);
        } else {
            $entity = Provider::findOrFail($request->entity_id);
        }

         $fineDiscount = FineDiscount::createManualFine(
                $request->entity_type,
                $request->entity_id,
                $request->amount,
                $request->reason,
                $request->notes
            );

        // Apply immediately if requested
        if ($request->apply_immediately) {
            $fineDiscount->apply();
        }

        $message = __('messages.fine_created_successfully');

        return redirect()->route('fines-discounts.index')
                        ->with('success', $message);
    }

    /**
     * Show fine/discount details
     */
    public function show(FineDiscount $fineDiscount)
    {
        $fineDiscount->load(['user', 'provider', 'appointment.providerType', 'admin', 'walletTransaction']);
        
        return view('admin.fines-discounts.show', compact('fineDiscount'));
    }

    /**
     * Apply pending fine/discount
     */
    public function apply(FineDiscount $fineDiscount)
    {
        if (!$fineDiscount->canBeApplied()) {
            return redirect()->back()->with('error', __('messages.cannot_apply_fine_discount'));
        }

        if ($fineDiscount->apply()) {
            return redirect()->back()->with('success', __('messages.fine_discount_applied_successfully'));
        } else {
            return redirect()->back()->with('error', __('messages.fine_discount_application_failed'));
        }
    }

    /**
     * Reverse applied fine/discount
     */
    public function reverse(FineDiscount $fineDiscount)
    {
        if ($fineDiscount->status != 2) {
            return redirect()->back()->with('error', __('messages.can_only_reverse_applied'));
        }

        if ($fineDiscount->reverse()) {
            return redirect()->back()->with('success', __('messages.fine_discount_reversed_successfully'));
        } else {
            return redirect()->back()->with('error', __('messages.fine_discount_reversal_failed'));
        }
    }

    /**
     * Delete fine/discount (only if not applied)
     */
    public function destroy(FineDiscount $fineDiscount)
    {
        if ($fineDiscount->status == 2) {
            return redirect()->back()->with('error', __('messages.cannot_delete_applied'));
        }

        $fineDiscount->delete();
        
        return redirect()->route('fines-discounts.index')
                        ->with('success', __('messages.fine_discount_deleted_successfully'));
    }

  
    /**
     * Process all pending fines
     */
    public function processAllPending()
    {
        $result = AppointmentService::processPendingFines();
        
        return response()->json([
            'message' => __('messages.pending_fines_processed', $result),
            'data' => $result
        ]);
    }

    /**
     * Show fine settings
     */
    public function settings()
    {
        $settings = FineSetting::getAllSettings();
        
        return view('admin.fines-discounts.settings', compact('settings'));
    }

    /**
     * Update fine settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'late_cancellation_hours' => 'required|integer|min:1|max:168', // Max 1 week
            'fine_percentage' => 'required|numeric|min:0|max:100',
            'minimum_fine_amount' => 'required|numeric|min:0',
            'maximum_fine_amount' => 'required|numeric|min:0',
            'auto_apply_fines' => 'required|in:1,2',
            'allow_negative_balance' => 'required|in:1,2'
        ]);

        // Validate minimum < maximum
        if ($request->minimum_fine_amount > $request->maximum_fine_amount) {
            return redirect()->back()
                           ->withErrors(['minimum_fine_amount' => __('messages.minimum_must_be_less_than_maximum')])
                           ->withInput();
        }

        $settings = [
            'late_cancellation_hours' => $request->late_cancellation_hours,
            'fine_percentage' => $request->fine_percentage,
            'minimum_fine_amount' => $request->minimum_fine_amount,
            'maximum_fine_amount' => $request->maximum_fine_amount,
            'auto_apply_fines' => $request->auto_apply_fines,
            'allow_negative_balance' => $request->allow_negative_balance
        ];

        foreach ($settings as $key => $value) {
            FineSetting::set($key, $value);
        }

        return redirect()->back()->with('success', __('messages.settings_updated_successfully'));
    }

  
}