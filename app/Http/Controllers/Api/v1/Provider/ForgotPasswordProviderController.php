<?php

namespace App\Http\Controllers\Api\v1\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\Responses;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordProviderController extends Controller
{

    use Responses;

    // 1) Check phone exists
    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->error_response("Validation error", $validator->errors());
        }

        $exists = Provider::where('phone', $request->phone)->exists();

        if (! $exists) {
            return $this->error_response("This phone is not registered", []);
        }

        return $this->success_response("Phone exists", []);
    }



    // 2) Update password
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|exists:providers,phone',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return $this->error_response("Validation error", $validator->errors());
        }

        DB::beginTransaction();

        try {
            $provider = Provider::where('phone', $request->phone)->first();

            $provider->password = Hash::make($request->password);
            $provider->save();

            DB::commit();

            return $this->success_response("Password updated successfully", [
                "provider" => $provider
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error($e->getMessage());

            return $this->error_response("Something went wrong", $e->getMessage());
        }
    }
}
