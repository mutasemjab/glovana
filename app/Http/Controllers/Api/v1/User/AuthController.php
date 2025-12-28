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


    public function updatePhone(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone,' . $user->id,
        ]);

        if ($validator->fails()) {
            return $this->error_response(
                'Validation error',
                $validator->errors()
            );
        }

        $user->phone = $request->phone;
        $user->save();

        return $this->success_response(
            'Phone number updated successfully',
            [
                'phone' => $user->phone
            ]
        );
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

            if ($userApi) {
                // Regular user account deactivation
                $userApi->update(['activate' => 2]);

                // Revoke all tokens for the user
                $userApi->tokens()->delete();

                return $this->success_response('User account deleted successfully', null);
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Exception $e) {
            \Log::error('Account deletion error: ' . $e->getMessage());
            return $this->error_response('Failed to delete account', ['error' => $e->getMessage()]);
        }
    }




   public function login(Request $request)
    {
        $userType = $request->user_type ?? 'user';

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $credentials = $request->only('phone', 'password');

        if ($userType == 'provider') {
            $user = \App\Models\Provider::where('phone', $credentials['phone'])->first();
        } else {
            $user = \App\Models\User::where('phone', $credentials['phone'])->first();
        }
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found or not active'
            ], 401);
        }

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->error_response('Invalid phone or password', []);
        }

        // Optional: Update FCM token if provided
        if ($request->filled('fcm_token')) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }

        $accessToken = $user->createToken('authToken')->accessToken;

        $responseData = [
            'token' => $accessToken,
            'user' => $user,
        ];

        // Add ban info for banned providers
        if ($userType == 'provider' && $user->activate == 2) {
            $activeBan = $user->activeBan;
            if ($activeBan) {
                $lang = $request->header('lang', 'en');
                $responseData['ban_info'] = [
                    'is_permanent' => $activeBan->is_permanent,
                    'reason' => $activeBan->getReasonText($lang),
                    'description' => $activeBan->ban_description,
                    'banned_at' => $activeBan->banned_at->toDateTimeString(),
                    'ban_until' => $activeBan->ban_until ? $activeBan->ban_until->toDateTimeString() : null,
                ];
            }
        }

        return $this->success_response('Login successful', $responseData);
    }


    public function register(Request $request)
    {
        $userType = $request->user_type ?? 'user';

        // Different validation rules based on user type
        if ($userType == 'provider') {
            $validator = Validator::make($request->all(), [
                'name_of_manager' => 'required|string|max:255',
                'phone' => 'required|string|unique:providers',
                'password' => 'required',
                'email' => 'nullable|email|unique:providers',
                'fcm_token' => 'nullable|string',
                'photo_of_manager' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|unique:users',
                'password' => 'required',
                'email' => 'nullable|email|unique:users',
                'fcm_token' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        }

        if ($validator->fails()) {
            // Get all error messages as a flat array and join them
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            
            return $this->error_response($errorMessage, []);
        }

        // Prepare data for user creation
        $userData = [
           // 'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'fcm_token' => $request->fcm_token,
            'balance' => 0,  // Default balance
        ];

        $userData['password'] = Hash::make($request->password);

        // Add photo if uploaded
        if ($request->hasFile('photo')) {
            $userData['photo'] = uploadImage('assets/admin/uploads', $request->file('photo'));
        }

        // Create user with the data
        if ($userType == 'provider') {
            // Add provider-specific fields
            $userData['name_of_manager'] = $request->name_of_manager;
            $userData['activate'] = 3; // waiting approve from admin

            if ($request->hasFile('photo_of_manager')) {
                $userData['photo_of_manager'] = uploadImage('assets/admin/uploads', $request->file('photo_of_manager'));
            }

            // Create provider
            $user = \App\Models\Provider::create($userData);
        } else {
            $userData['name'] = $request->name;
            $userData['referral_code'] = $this->generateReferralCode();
            $user = User::create($userData);
        }

        // Generate access token
        $accessToken = $user->createToken('authToken')->accessToken;

        return $this->success_response('Registration successful', [
            'token' => $accessToken,
            'user' => $user,
        ]);
    }


    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_id' => 'required|string',
            'access_token' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'photo' => 'nullable|string', // URL from Google
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // Check if user already exists with this Google ID
        $user = User::where('google_id', $request->google_id)->first();

        if ($user) {
            // User exists, update access token and FCM token if provided
            $user->access_token = $request->access_token;
            
            if ($request->filled('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
            }
            
            $user->save();
        } else {
            // Check if user exists with same email
            $existingUser = User::where('email', $request->email)->first();
            
            if ($existingUser) {
                // Link Google account to existing user
                $existingUser->google_id = $request->google_id;
                $existingUser->access_token = $request->access_token;
                $existingUser->type = 1; // Google login
                
                if ($request->filled('fcm_token')) {
                    $existingUser->fcm_token = $request->fcm_token;
                }
                
                $existingUser->save();
                $user = $existingUser;
            } else {
                // Create new user
                $userData = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'google_id' => $request->google_id,
                    'access_token' => $request->access_token,
                    'fcm_token' => $request->fcm_token,
                    'balance' => 0,
                    'referral_code' => $this->generateReferralCode(),
                    'type' => 1, // Google login
                    'activate' => 1, // Active by default for social login
                ];

                // Handle photo from Google
                if ($request->filled('photo')) {
                    // You might want to download and store the image locally
                    // For now, we'll store the URL
                    $userData['photo'] = $request->photo;
                }

                $user = User::create($userData);
            }
        }

        // Generate access token
        $accessToken = $user->createToken('authToken')->accessToken;

        return $this->success_response('Google login successful', [
            'token' => $accessToken,
            'user' => $user,
        ]);
    }

    public function appleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apple_id' => 'required|string', // This would be the Apple user identifier
            'access_token' => 'required|string', // Apple identity token
            'name' => 'nullable|string|max:255', // Apple might not always provide name
            'email' => 'nullable|email', // Apple might provide private relay email
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $user = User::where('apple_id', $request->apple_id)->where('type', 2)->first();

        if ($user) {
            // User exists, update access token and FCM token if provided
            $user->access_token = $request->access_token;
            
            if ($request->filled('fcm_token')) {
                $user->fcm_token = $request->fcm_token;
            }
            
            $user->save();
        } else {
            // Check if user exists with same email (if email is provided)
            $existingUser = null;
            if ($request->filled('email')) {
                $existingUser = User::where('email', $request->email)->first();
            }
            
            if ($existingUser) {
                // Link Apple account to existing user
                $existingUser->apple_id = $request->apple_id; 
                $existingUser->access_token = $request->access_token;
                $existingUser->type = 2; // Apple login
                
                if ($request->filled('fcm_token')) {
                    $existingUser->fcm_token = $request->fcm_token;
                }
                
                $existingUser->save();
                $user = $existingUser;
            } else {
                // Create new user
                $userData = [
                    'name' => $request->name ?? 'Apple User', // Default name if not provided
                    'email' => $request->email, // Can be null
                    'apple_id' => $request->apple_id, // Using google_id field for Apple ID
                    'access_token' => $request->access_token,
                    'fcm_token' => $request->fcm_token,
                    'balance' => 0,
                    'referral_code' => $this->generateReferralCode(),
                    'type' => 2, // Apple login
                    'activate' => 1, // Active by default for social login
                ];

                $user = User::create($userData);
            }
        }

        // Generate access token
        $accessToken = $user->createToken('authToken')->accessToken;

        return $this->success_response('Apple login successful', [
            'token' => $accessToken,
            'user' => $user,
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
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
        } catch (\Throwable $th) {
            \Log::error('Profile retrieval error: ' . $th->getMessage());
            return $this->error_response('Failed to retrieve profile', []);
        }
    }

    public function proivderProfile()
    {
        try {
            $proivderApi = auth('proivder-api')->user();

            if ($proivderApi) {
                return $this->success_response('proivder profile retrieved', $proivderApi);
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
            $providerApi = auth('provider-api')->user();
            
            // Determine which type of user is authenticated
            if ($userApi) {
                $user = $userApi;
                $userType = 'user';
                $table = 'users';
            } elseif ($providerApi) {
                $user = $providerApi;
                $userType = 'provider';
                $table = 'providers';
            } else {
                return $this->error_response('Unauthenticated', [], 401);
            }
            
            // Base validation rules for both user types
            $validationRules = [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:' . $table . ',email,' . $user->id,
                'phone' => 'nullable|string',
                'fcm_token' => 'nullable|string',
                'password' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ];
            
            // Add provider-specific validation rules if the user is a provider
            if ($userType == 'provider') {
                $providerRules = [
                    'photo_of_manager' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                    'name_of_manager' => 'nullable|string|max:255',
                ];
                $validationRules = array_merge($validationRules, $providerRules);
            }
            
            // Validate input data
            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }
            
            // Initialize data array
            $data = [];
            
            // Get basic fields for both user types (only filled fields)
            if ($request->filled('name')) {
                $data['name'] = $request->name;
            }
            
            if ($request->filled('email')) {
                $data['email'] = $request->email;
            }
            
            if ($request->filled('phone')) {
                $data['phone'] = $request->phone;
            }
            
            if ($request->filled('fcm_token')) {
                $data['fcm_token'] = $request->fcm_token;
            }
            
            // Hash password if provided
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }
            
            // Handle basic profile photo upload (for both user types)
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->photo) {
                    $oldPhotoPath = 'assets/admin/uploads/' . $user->photo;
                    if (file_exists($oldPhotoPath)) {
                        @unlink($oldPhotoPath);
                    }
                }
                $data['photo'] = uploadImage('assets/admin/uploads', $request->file('photo'));
            }
            
            // Handle provider-specific fields and photos if the user is a provider
            if ($userType == 'provider') {
                // Add text field (only if filled)
                if ($request->filled('name_of_manager')) {
                    $data['name_of_manager'] = $request->name_of_manager;
                }
                
                // Handle provider photo upload
                if ($request->hasFile('photo_of_manager')) {
                    // Delete old photo if exists
                    if ($user->photo_of_manager) {
                        $oldPhotoPath = 'assets/admin/uploads/' . $user->photo_of_manager;
                        if (file_exists($oldPhotoPath)) {
                            @unlink($oldPhotoPath);
                        }
                    }
                    $data['photo_of_manager'] = uploadImage('assets/admin/uploads', $request->file('photo_of_manager'));
                }
            }
            
            // Update user data
            $user->update($data);
            
            // Refresh user data to get updated values
            $user->refresh();
            
            return $this->success_response(ucfirst($userType) . ' profile updated successfully', $user);
            
        } catch (\Throwable $th) {
            \Log::error('Profile update error: ' . $th->getMessage());
            return $this->error_response('Failed to update profile', ['message' => $th->getMessage()]);
        }
    }

       public function notifications()
        {
            $user = auth()->user();

            $notifications = Notification::query()
                ->where(function ($query) use ($user) {
                    $query->where('type', 0)
                        ->orWhere('type', 1)
                        ->orWhere('user_id', $user->id);
                })
                ->orderBy('id', 'DESC')
                ->get();

            return $this->success_response('Notifications retrieved successfully', $notifications);
        }



    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:providers,id',
            'title' => 'required|string',
            'body' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            $response = FCMController::sendMessageToProvider(
                $request->title,
                $request->body,
                $request->provider_id
            );

            if ($response) {
                return $this->success_response('Notification sent successfully to the provider',[]);
            } else {
                return $this->error_response('Notification was not sent to the provider',[]);
            }
            
        } catch (\Exception $e) {
            \Log::error('FCM Error: ' . $e->getMessage());
            return $this->error_response('An error occurred', ['error' => $e->getMessage()]);
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
