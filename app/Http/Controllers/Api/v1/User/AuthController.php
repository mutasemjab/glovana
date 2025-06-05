<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\ClassTeacher;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Admin\FCMController; // <-- Import the FCMController here
use App\Models\ParentStudent;
use App\Traits\Responses;
use Auth;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    use Responses;


    public function updateStatusOnOff()
    {
        $driver = auth('driver-api')->user();

        // Check if driver exists and has a valid status
        if (!in_array($driver->status, [1, 2])) {
            return response()->json(['message' => 'Invalid status value.'], 400);
        }

        // Toggle status
        $driver->status = $driver->status == 1 ? 2 : 1;
        $driver->save();
         return $this->success_response('Status updated successfully.', $driver->status);
     
    }

    public function active()
    {
        $user = auth()->user();
        if ($user->activate == 2) {
            return $this->error_response('Your account has been InActive', null);
        }

        return $this->success_response('User retrieved successfully', $user);
    }

    public function deleteAccount(Request $request)
    {
        try {
            // Check both authentication guards
            $userApi = auth('user-api')->user();
            $driverApi = auth('driver-api')->user();
            
            if ($userApi) {
                // Regular user account deactivation
                $userApi->update(['activate' => 2]);
                
                // Revoke all tokens for the user
                $userApi->tokens()->delete();
                
                return $this->success_response('User account deleted successfully', null);
            } elseif ($driverApi) {
                // Driver account deactivation
                $driverApi->update(['activate' => 2]);
                
                // Revoke all tokens for the driver
                $driverApi->tokens()->delete();
                
                return $this->success_response('Driver account deleted successfully', null);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Exception $e) {
            \Log::error('Account deletion error: ' . $e->getMessage());
            return $this->error_response('Failed to delete account', ['error' => $e->getMessage()]);
        }
    }
       public function logout()
    {
        try {
            // Check if the request is authenticated with user-api guard
            $userApi = auth('user-api')->user();
            
            // Check if the request is authenticated with driver-api guard
            $driverApi = auth('driver-api')->user();
            
            if ($userApi) {
                // Revoke all tokens for user
                $userApi->tokens()->delete();
                return $this->success_response('User logout successful', []);
            } elseif ($driverApi) {
                // Revoke all tokens for driver
                $driverApi->tokens()->delete();
                return $this->success_response('Driver logout successful', []);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Throwable $th) {
            // Log the error for debugging
            \Log::error('Logout error: ' . $th->getMessage());
            return $this->error_response('Failed to logout', []);
        }
    }

     public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'country_code' => 'required|string', // Added country_code validation
            'fcm_token' => 'nullable|string',
            'user_type' => 'nullable|in:user,driver'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $phone = $request->phone;
        $countryCode = $request->country_code; // Get country_code from request
        $userType = $request->user_type ?? 'user';
        
        // Determine which model to check based on user_type
        $model = ($userType == 'driver') ? 'App\Models\Driver' : 'App\Models\User';
        
        // Check both phone and country_code
        $user = $model::where('phone', $phone)
                    ->where('country_code', $countryCode)
                    ->first();

        if ($user) {
            // Check if user is active
            if ($user->activate == 2) {
                return $this->error_response('Account is inactive', [
                    'user_exists' => true,
                    'account_status' => 'inactive'
                ]);
            }

            // Update FCM token if provided
            if ($request->has('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            // Create access token
            $accessToken = $user->createToken('authToken')->accessToken;
            
            return $this->success_response('Success', [
                'user_exists' => true,
                'account_status' => 'active',
                'user_type' => $userType,
                'user' => $user,
                'token' => $accessToken,
            ]);
        }

        // User doesn't exist
        return $this->success_response('Phone number not registered. OTP sent for registration', [
            'user_exists' => false,
            'user_type' => $userType,
            'country_code' => $countryCode, // Also return country_code in response
        ]);
    }

    public function register(Request $request)
    {
        $userType = $request->user_type ?? 'user';
        
        // Different validation rules based on user type
        if ($userType == 'driver') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'country_code' => 'required',
                'phone' => 'required|string|unique:drivers',
                'email' => 'nullable|email|unique:drivers',
                'fcm_token' => 'nullable|string',
                'sos_phone' => 'nullable|string',
                'option_ids' => 'required|array', // Changed to array
                'option_ids.*' => 'required|exists:options,id', // Validate each option ID
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                
                // Car details
                'passenger_number' => 'nullable',
                'photo_of_car' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'model' => 'nullable|string|max:255',
                'production_year' => 'nullable|string|max:4',
                'color' => 'nullable|string|max:255',
                'plate_number' => 'nullable|string|max:255',
                
                // Documents
                'driving_license_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'driving_license_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'car_license_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'car_license_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'country_code' => 'required',
                'phone' => 'required|string|unique:users',
                'email' => 'nullable|email|unique:users',
                'fcm_token' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        }
        
        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }
        
        // Prepare data for user creation
        $userData = [
            'name' => $request->name,
            'country_code' => $request->country_code,
            'phone' => $request->phone,
            'email' => $request->email,
            'fcm_token' => $request->fcm_token,
            'balance' => 0,  // Default balance
        ];
        
        // Add photo if uploaded
        if ($request->hasFile('photo')) {
            $userData['photo'] = uploadImage('assets/admin/uploads', $request->file('photo'));
        }
        
        // Create user with the data
        if ($userType == 'driver') {
            // Add driver-specific fields
            $userData['sos_phone'] = $request->sos_phone;
            $userData['activate'] = 3; // waiting approve from admin
            
            // Handle car image uploads
            if ($request->hasFile('photo_of_car')) {
                $userData['photo_of_car'] = uploadImage('assets/admin/uploads', $request->file('photo_of_car'));
            }
            
            // Add car details
            $userData['passenger_number'] = $request->passenger_number;
            $userData['model'] = $request->model;
            $userData['production_year'] = $request->production_year;
            $userData['color'] = $request->color;
            $userData['plate_number'] = $request->plate_number;
            
            // Handle document uploads
            if ($request->hasFile('driving_license_front')) {
                $userData['driving_license_front'] = uploadImage('assets/admin/uploads', $request->file('driving_license_front'));
            }
            
            if ($request->hasFile('driving_license_back')) {
                $userData['driving_license_back'] = uploadImage('assets/admin/uploads', $request->file('driving_license_back'));
            }
            
            if ($request->hasFile('car_license_front')) {
                $userData['car_license_front'] = uploadImage('assets/admin/uploads', $request->file('car_license_front'));
            }
            
            if ($request->hasFile('car_license_back')) {
                $userData['car_license_back'] = uploadImage('assets/admin/uploads', $request->file('car_license_back'));
            }
            
            // Create driver
            $user = \App\Models\Driver::create($userData);
            
            // Attach options to the driver
            if ($request->has('option_ids') && is_array($request->option_ids)) {
                foreach ($request->option_ids as $optionId) {
                    $user->options()->attach($optionId);
                }
            }
        } else {
            $userData['referral_code'] = $this->generateReferralCode();
            $user = User::create($userData);
        }
        
        // Generate access token
        $accessToken = $user->createToken('authToken')->accessToken;
        
        return $this->success_response('Registration successful', [
            'token' => $accessToken,
            'user' => $user,
            'new_user' => true
        ]);
    }

    public function userProfile()
    {
        try {
            // Check both authentication guards
            $userApi = auth('user-api')->user();
            
            if ($userApi) {
                // If it's a regular user
                return $this->success_response('User profile retrieved', $userApi);
            }else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Throwable $th) {
            \Log::error('Profile retrieval error: ' . $th->getMessage());
            return $this->error_response('Failed to retrieve profile', []);
        }
    }
   
    public function driverProfile()
    {
        try {
            $driverApi = auth('driver-api')->user();
            
           if ($driverApi) {
                $driverApi->load('options');
                return $this->success_response('Driver profile retrieved', $driverApi);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Throwable $th) {
            \Log::error('Profile retrieval error: ' . $th->getMessage());
            return $this->error_response('Failed to retrieve profile', []);
        }
    }

        public function updateProfile(Request $request)
    {
        try {
            // Check both authentication guards
            $userApi = auth('user-api')->user();
            $driverApi = auth('driver-api')->user();
            
            // Determine which type of user is authenticated
            if ($userApi) {
                $user = $userApi;
                $userType = 'user';
                $table = 'users';
            } elseif ($driverApi) {
                $user = $driverApi;
                $userType = 'driver';
                $table = 'drivers';
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
            
            // Base validation rules for both user types
            $validationRules = [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:' . $table . ',email,' . $user->id,
                'phone' => 'nullable|string',
                'sos_phone' => 'nullable|string',
                'country_code' => 'nullable|string',
                'photo' => 'nullable|image|max:2048',
            ];
            
            // Add driver-specific validation rules if the user is a driver
            if ($userType == 'driver') {
                $driverRules = [
                    'photo_of_car' => 'nullable|image|max:2048',
                    'model' => 'nullable|string|max:255',
                    'production_year' => 'nullable|string|max:4',
                    'color' => 'nullable|string|max:255',
                    'plate_number' => 'nullable|string|max:255',
                    'driving_license_front' => 'nullable|image|max:2048',
                    'driving_license_back' => 'nullable|image|max:2048',
                    'car_license_front' => 'nullable|image|max:2048',
                    'car_license_back' => 'nullable|image|max:2048',
                    'option_ids' => 'nullable|array', // Add validation for option_ids array
                    'option_ids.*' => 'nullable|exists:options,id' // Validate each option ID
                ];
                
                // Merge driver-specific rules with base rules
                $validationRules = array_merge($validationRules, $driverRules);
            }
            
            // Validate input data
            $validator = Validator::make($request->all(), $validationRules);
            
            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }
            
            // Get basic fields for both user types
            $data = $request->only(['name', 'email', 'phone', 'sos_phone', 'country_code']);
            
            // Handle basic profile photo upload (for both user types)
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->photo && file_exists('assets/admin/uploads/' . $user->photo)) {
                    unlink('assets/admin/uploads/' . $user->photo);
                }
                $data['photo'] = uploadImage('assets/admin/uploads', $request->file('photo'));
            }
            
            // Handle driver-specific fields and photos if the user is a driver
            if ($userType == 'driver') {
                // Add text fields
                $data = array_merge($data, $request->only([
                    'model',
                    'production_year',
                    'color',
                    'plate_number'
                ]));
                
                // Handle all driver-specific photo uploads
                $photoFields = [
                    'photo_of_car' => 'assets/admin/uploads',
                    'driving_license_front' => 'assets/admin/uploads',
                    'driving_license_back' => 'assets/admin/uploads',
                    'car_license_front' => 'assets/admin/uploads',
                    'car_license_back' => 'assets/admin/uploads'
                ];
                
                foreach ($photoFields as $field => $path) {
                    if ($request->hasFile($field)) {
                        // Delete old photo if exists
                        if ($user->$field && file_exists($path . '/' . $user->$field)) {
                            unlink($path . '/' . $user->$field);
                        }
                        $data[$field] = uploadImage($path, $request->file($field));
                    }
                }
                
                // Update options if provided
                if ($request->has('option_ids') && is_array($request->option_ids)) {
                    // Sync the options (removes old ones and adds new ones)
                    $user->options()->sync($request->option_ids);
                }
            }
            
            // Update user data
            $user->update($data);
            
            // Reload the user with the options relationship
            if ($userType == 'driver') {
                $user->load('options');
            }
            
            return $this->success_response(ucfirst($userType) . ' profile updated successfully', $user);
        } catch (\Throwable $th) {
            \Log::error('Profile update error: ' . $th->getMessage());
            return $this->error_response('Failed to update profile', ['message' => $th->getMessage()]);
        }
    }

    public function notifications()
    {
        $user = auth()->user();

        // Define user_type-based notification types
        $userTypeMapping = [
            1 => 1, // Regular Users
            2 => 3, // Teachers
            3 => 2, // Parents
        ];

        // Fetch notifications
        $notifications = Notification::query()
            ->where(function ($query) use ($user, $userTypeMapping) {
                $query->where('type', 0) // Global notifications (for all users)
                      ->orWhere(function ($q) use ($user) {
                          // Notifications specifically for this user
                          $q->where('type', 4)->where('user_id', $user->id);
                      });

                // Include user_type-specific notifications if applicable
                if (isset($userTypeMapping[$user->user_type])) {
                    $query->orWhere('type', $userTypeMapping[$user->user_type]);
                }
            })
            ->orderBy('id', 'DESC')
            ->get();

        return $this->success_response('Notifications retrieved successfully', $notifications);
    }

    public function sendToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'title' => 'required|string',
            'body' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            // Call the sendMessageToUser method in the FCMController
            $response = FCMController::sendMessageToUser(
                $request->title,
                $request->body,
                $request->user_id,
            );

            if ($response) {
                return redirect()->back()->with('message', 'Notification sent successfully to the user');
            } else {
                return redirect()->back()->with('error', 'Notification was not sent to the user');
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function generateReferralCode()
    {
        do {
            $referralCode = strtoupper(substr(md5(time() . rand(1000, 9999)), 0, 8));
        } while (User::where('referral_code', $referralCode)->exists());
        
        return $referralCode;
    }
}
