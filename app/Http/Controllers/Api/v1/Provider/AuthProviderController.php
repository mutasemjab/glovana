<?php

namespace App\Http\Controllers\Api\v1\Provider;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AuthProviderController extends Controller
{
    use Responses;


    public function updateStatusOnOff()
    {
        $provider = auth('provider-api')->user();

        // Check if driver exists and has a valid status
        if (!in_array($provider->status, [1, 2])) {
            return response()->json(['message' => 'Invalid status value.'], 400);
        }

        // Toggle status
        $provider->status = $provider->status == 1 ? 2 : 1;
        $provider->save();
        return $this->success_response('Status updated successfully.', $provider->status);
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
            $providerApi = auth('provider-api')->user();

                // provider account deactivation
                $providerApi->update(['activate' => 2]);

                // Revoke all tokens for the provider
                $providerApi->tokens()->delete();

                return $this->success_response('provider account deleted successfully', null);
        } catch (\Exception $e) {
            \Log::error('Account deletion error: ' . $e->getMessage());
            return $this->error_response('Failed to delete account', ['error' => $e->getMessage()]);
        }
    }
   




   public function updateProviderProfile(Request $request)
    {
        $provider = auth()->user();
        
        // Check if user is a provider
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can update provider profile');
        }

        $validator = Validator::make($request->all(), [
            'name_of_manager' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:providers,phone,' . $provider->id,
            'email' => 'nullable|email|unique:providers,email,' . $provider->id,
            'fcm_token' => 'nullable|string',
            'photo_of_manager' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // Update provider basic info
        $updateData = $request->only(['name_of_manager', 'phone', 'email', 'fcm_token']);
        
        if ($request->hasFile('photo_of_manager')) {
            // Delete old photo if exists
            if ($provider->photo_of_manager) {
                // Add your delete file logic here if needed
            }
            $updateData['photo_of_manager'] = uploadImage('assets/admin/uploads', $request->file('photo_of_manager'));
        }

        $provider->update($updateData);

        return $this->success_response('Provider profile updated successfully', [
            'provider' => $provider->fresh()
        ]);
    }

    public function completeProviderProfile(Request $request)
    {
        $provider = auth()->user();
        
        // Check if user is a provider
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can complete provider profile');
        }

        $validator = Validator::make($request->all(), [
            'provider_types' => 'required|array|min:1',
            'provider_types.*.type_id' => 'required|exists:types,id',
            'provider_types.*.name' => 'required|string|max:255',
            'provider_types.*.description' => 'required|string',
            'provider_types.*.lat' => 'required|numeric|between:-90,90',
            'provider_types.*.lng' => 'required|numeric|between:-180,180',
            'provider_types.*.address' => 'nullable|string',
            'provider_types.*.price_per_hour' => 'required|numeric|min:0',
            'provider_types.*.is_vip' => 'sometimes|in:1,2',
            'provider_types.*.service_ids' => 'required|array|min:1',
            'provider_types.*.service_ids.*' => 'exists:services,id',
            'provider_types.*.images' => 'nullable|array',
            'provider_types.*.images.*' => 'image|mimes:jpeg,png,jpg|max:4048',
            'provider_types.*.galleries' => 'nullable|array',
            'provider_types.*.galleries.*' => 'image|mimes:jpeg,png,jpg|max:4048',
            'provider_types.*.availability' => 'required|array|min:1',
            'provider_types.*.availability.*.day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'provider_types.*.availability.*.start_time' => 'required|date_format:H:i',
            'provider_types.*.availability.*.end_time' => 'required|date_format:H:i|after:provider_types.*.availability.*.start_time',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            DB::beginTransaction();

            foreach ($request->provider_types as $providerTypeData) {
                // Create provider type
                $providerType = \App\Models\ProviderType::create([
                    'provider_id' => $provider->id,
                    'type_id' => $providerTypeData['type_id'],
                    'name' => $providerTypeData['name'],
                    'description' => $providerTypeData['description'],
                    'lat' => $providerTypeData['lat'],
                    'lng' => $providerTypeData['lng'],
                    'address' => $providerTypeData['address'] ?? null,
                    'price_per_hour' => $providerTypeData['price_per_hour'],
                    'is_vip' => $providerTypeData['is_vip'] ?? 2,
                    'activate' => 1,
                    'status' => 1,
                ]);

                // Add services for this provider type
                foreach ($providerTypeData['service_ids'] as $serviceId) {
                    \App\Models\ProviderServiceType::create([
                        'provider_type_id' => $providerType->id,
                        'service_id' => $serviceId,
                    ]);
                }

                // Upload and save images
                if (isset($providerTypeData['images']) && is_array($providerTypeData['images'])) {
                    foreach ($providerTypeData['images'] as $image) {
                        if ($image instanceof \Illuminate\Http\UploadedFile) {
                            $imagePath = uploadImage('assets/admin/uploads', $image);
                            \App\Models\ProviderImage::create([
                                'provider_type_id' => $providerType->id,
                                'photo' => $imagePath,
                            ]);
                        }
                    }
                }

                // Upload and save gallery images
                if (isset($providerTypeData['galleries']) && is_array($providerTypeData['galleries'])) {
                    foreach ($providerTypeData['galleries'] as $galleryImage) {
                        if ($galleryImage instanceof \Illuminate\Http\UploadedFile) {
                            $galleryPath = uploadImage('assets/admin/uploads', $galleryImage);
                            \App\Models\ProviderGallery::create([
                                'provider_type_id' => $providerType->id,
                                'photo' => $galleryPath,
                            ]);
                        }
                    }
                }

                // Add availability
                foreach ($providerTypeData['availability'] as $availability) {
                    \App\Models\ProviderAvailability::create([
                        'provider_type_id' => $providerType->id,
                        'day_of_week' => $availability['day_of_week'],
                        'start_time' => $availability['start_time'],
                        'end_time' => $availability['end_time'],
                    ]);
                }
            }

            // Update provider activation status to pending approval
            $provider->update(['activate' => 3]); // waiting approve from admin

            DB::commit();

            return $this->success_response('Provider profile completed successfully', [
                'provider' => $provider->fresh()->load([
                    'providerTypes.services',
                    'providerTypes.images',
                    'providerTypes.galleries',
                    'providerTypes.availability'
                ])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Error completing profile', $e->getMessage());
        }
    }

    public function getProviderProfile(Request $request)
    {
        $provider = auth()->user();
        
        // Check if user is a provider
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can view provider profile');
        }

        $provider->load([
            'providerTypes.type',
            'providerTypes.services',
            'providerTypes.images',
            'providerTypes.galleries',
            'providerTypes.availability'
        ]);

        return $this->success_response('Provider profile retrieved successfully', [
            'provider' => $provider
        ]);
    }

    public function updateProviderType(Request $request, $providerTypeId)
    {
        $provider = auth()->user();
        
        // Check if user is a provider
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can update provider types');
        }

        $providerType = \App\Models\ProviderType::where('id', $providerTypeId)
            ->where('provider_id', $provider->id)
            ->first();

        if (!$providerType) {
            return $this->error_response('Not found', 'Provider type not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'address' => 'nullable|string',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'is_vip' => 'sometimes|in:1,2',
            'status' => 'sometimes|in:1,2',
            'service_ids' => 'sometimes|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:4048',
            'galleries' => 'nullable|array',
            'galleries.*' => 'image|mimes:jpeg,png,jpg|max:4048',
            'availability' => 'sometimes|array|min:1',
            'availability.*.day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'availability.*.start_time' => 'required|date_format:H:i',
            'availability.*.end_time' => 'required|date_format:H:i|after:availability.*.start_time',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update provider type basic info
            $updateData = $request->only(['name', 'description', 'lat', 'lng', 'address', 'price_per_hour', 'is_vip', 'status']);
            $providerType->update($updateData);

            // Update services if provided
            if ($request->has('service_ids')) {
                // Delete existing services
                \App\Models\ProviderServiceType::where('provider_type_id', $providerType->id)->delete();
                
                // Add new services
                foreach ($request->service_ids as $serviceId) {
                    \App\Models\ProviderServiceType::create([
                        'provider_type_id' => $providerType->id,
                        'service_id' => $serviceId,
                    ]);
                }
            }

            // Add new images if provided
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = uploadImage('assets/admin/uploads', $image);
                        \App\Models\ProviderImage::create([
                            'provider_type_id' => $providerType->id,
                            'photo' => $imagePath,
                        ]);
                    }
                }
            }

            // Add new gallery images if provided
            if ($request->has('galleries') && is_array($request->galleries)) {
                foreach ($request->galleries as $galleryImage) {
                    if ($galleryImage instanceof \Illuminate\Http\UploadedFile) {
                        $galleryPath = uploadImage('assets/admin/uploads', $galleryImage);
                        \App\Models\ProviderGallery::create([
                            'provider_type_id' => $providerType->id,
                            'photo' => $galleryPath,
                        ]);
                    }
                }
            }

            // Update availability if provided
            if ($request->has('availability')) {
                // Delete existing availability
                \App\Models\ProviderAvailability::where('provider_type_id', $providerType->id)->delete();
                
                // Add new availability
                foreach ($request->availability as $availability) {
                    \App\Models\ProviderAvailability::create([
                        'provider_type_id' => $providerType->id,
                        'day_of_week' => $availability['day_of_week'],
                        'start_time' => $availability['start_time'],
                        'end_time' => $availability['end_time'],
                    ]);
                }
            }

            DB::commit();

            $providerType->load([
                'type',
                'services',
                'images',
                'galleries',
                'availability'
            ]);

            return $this->success_response('Provider type updated successfully', [
                'provider_type' => $providerType
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Error updating provider type', $e->getMessage());
        }
    }

        public function notifications()
        {
            $provider = auth()->user();

            $notifications = Notification::query()
                ->where(function ($query) use ($provider) {
                    $query->where('type', 0)
                        ->orWhere('type', 2)
                        ->orWhere('provider_id', $provider->id);
                })
                ->orderBy('id', 'DESC')
                ->get();

            return $this->success_response('Notifications retrieved successfully', $notifications);
        }

}
