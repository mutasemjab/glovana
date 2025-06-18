<?php


namespace App\Http\Controllers\Api\v1\Provider;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\WithdrawalRequest;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WithdrawalRequestProviderController extends Controller
{
    use Responses;

  public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        
           $provider = auth('provider-api')->user();
        
            // Check if provider has enough balance
            if ($provider->balance < $request->amount) {
                return $this->error_response('Insufficient balance',[]);
            }
            // Create withdrawal request
            WithdrawalRequest::create([
                'provider_id' => $provider->id,
                'amount' => $request->amount,
            ]);
          return $this->success_response('Withdrawal request submitted successfully',[]);
       
    }
}
