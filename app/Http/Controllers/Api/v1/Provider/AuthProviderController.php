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
use App\Models\ProviderDeleteRequest;
use App\Models\ProviderType;
use App\Traits\Responses;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AuthProviderController extends Controller
{
    use Responses;


    public function updateStatusOnOff($id)
    {
        $providerType = ProviderType::findOrFail($id);

        // Toggle status: if 1 => 2, if 2 => 1
        $providerType->status = $providerType->status == 1 ? 2 : 1;
        $providerType->save();

        return $this->success_response('Status updated successfully.', $providerType);
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
        $request->validate([
            'reason' => 'nullable|string|max:1000'
        ]);

        try {
            // Check provider authentication
            $providerApi = auth('provider-api')->user();
            
            if (!$providerApi) {
                return $this->error_response('Unauthorized', null, 401);
            }

            // Check if provider already has a pending delete request
            $existingRequest = ProviderDeleteRequest::where('provider_id', $providerApi->id)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return $this->error_response('Delete request already submitted and pending review', null, 409);
            }

            // Check if provider has any pending appointments
            $pendingAppointments = $providerApi->appointments()
                ->whereIn('appointment_status', [1, 2, 3, 6, 7]) // Pending, Accepted, OnTheWay, StartWork, Arrived
                ->count();

            if ($pendingAppointments > 0) {
                return $this->error_response('Cannot delete account with pending appointments. Please complete or cancel all pending appointments first.', [
                    'pending_appointments_count' => $pendingAppointments
                ], 400);
            }

            DB::beginTransaction();

            // Collect provider statistics at the time of delete request
            $providerStats = [
                'total_appointments' => $providerApi->appointments()->count(),
                'completed_appointments' => $providerApi->appointments()->where('appointment_status', 4)->count(),
                'cancelled_appointments' => $providerApi->appointments()->where('appointment_status', 5)->count(),
                'current_balance' => $providerApi->balance,
                'total_points' => $providerApi->total_points,
                'total_services' => $providerApi->providerTypes()->count(),
                'active_services' => $providerApi->providerTypes()->where('activate', 1)->count(),
                'account_age_days' => $providerApi->created_at->diffInDays(now()),
                'last_login' => $providerApi->updated_at, // Assuming updated_at tracks last activity
                'total_earnings' => $providerApi->walletTransactions()
                    ->where('type_of_transaction',1)
                    ->sum('amount'),
                'average_rating' => $this->calculateAverageRating($providerApi),
                'total_reviews' => $this->getTotalReviews($providerApi)
            ];

            // Create delete request record
            $deleteRequest = ProviderDeleteRequest::create([
                'provider_id' => $providerApi->id,
                'reason' => $request->input('reason'),
                'status' => 'pending',
                'additional_data' => $providerStats
            ]);

            // Deactivate provider account (set to waiting status)
            $providerApi->update(['activate' => 3]); // 3 = waiting approve (in this case, waiting for delete approval)

            // Log the delete request
            \Log::info('Provider delete request submitted', [
                'provider_id' => $providerApi->id,
                'provider_name' => $providerApi->name_of_manager,
                'delete_request_id' => $deleteRequest->id,
                'reason' => $request->input('reason'),
                'stats' => $providerStats
            ]);

           
            DB::commit();

            return $this->success_response('Delete request submitted successfully. Your account will be reviewed by our team.', [
                'request_id' => $deleteRequest->id,
                'status' => 'pending_review',
                'message' => 'Your account deletion request has been submitted and is pending admin review. You will be notified once a decision is made.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Account deletion request error: ' . $e->getMessage(), [
                'provider_id' => $providerApi->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->error_response('Failed to submit delete request', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Calculate average rating for the provider across all their services
     */
    private function calculateAverageRating($provider)
    {
        $allRatings = collect();
        
        foreach ($provider->providerTypes as $providerType) {
            $allRatings = $allRatings->merge($providerType->ratings);
        }
        
        return $allRatings->isNotEmpty() ? round($allRatings->avg('rating'), 2) : 0;
    }

    /**
     * Get total number of reviews for the provider
     */
    private function getTotalReviews($provider)
    {
        $totalReviews = 0;
        
        foreach ($provider->providerTypes as $providerType) {
            $totalReviews += $providerType->ratings()->whereNotNull('review')->count();
        }
        
        return $totalReviews;
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
            'photo_of_manager' => 'nullable|image|mimes:jpeg,png,jpg',
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
            'provider_types.*.price_per_hour' => 'required_if:provider_types.*.booking_type,hourly|nullable|numeric|min:0',
            'provider_types.*.is_vip' => 'sometimes|in:1,2',
            'provider_types.*.practice_license' => 'nullable',
            'provider_types.*.identity_photo' => 'nullable',
            'provider_types.*.service_ids' => 'required|array|min:1',
            'provider_types.*.service_ids.*' => 'exists:services,id',
            // New validation for service pricing (for service-based types)
            'provider_types.*.services_with_prices' => 'sometimes|array',
            'provider_types.*.services_with_prices.*.service_id' => 'required|exists:services,id',
            'provider_types.*.services_with_prices.*.price' => 'required|numeric|min:0',
            'provider_types.*.services_with_prices.*.is_active' => 'sometimes|boolean',
            // Images and galleries
            'provider_types.*.images' => 'nullable|array',
            'provider_types.*.images.*' => 'image|mimes:jpeg,png,jpg',
            'provider_types.*.galleries' => 'nullable|array',
            'provider_types.*.galleries.*' => 'image|mimes:jpeg,png,jpg',
            // Availabilities
            'provider_types.*.availabilities' => 'required|array|min:1',
            'provider_types.*.availabilities.*.day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'provider_types.*.availabilities.*.start_time' => 'required|date_format:H:i',
            'provider_types.*.availabilities.*.end_time' => 'required|date_format:H:i|after:provider_types.*.availabilities.*.start_time',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            DB::beginTransaction();

            foreach ($request->provider_types as $providerTypeData) {
                // Get the type to check booking_type
                $type = \App\Models\Type::find($providerTypeData['type_id']);
                
                // Validate pricing based on booking type
                if ($type->booking_type === 'hourly' && empty($providerTypeData['price_per_hour'])) {
                    DB::rollback();
                    return $this->error_response('Validation error', 'Price per hour is required for hourly booking types');
                }
                
                if ($type->booking_type === 'service' && empty($providerTypeData['services_with_prices'])) {
                    DB::rollback();
                    return $this->error_response('Validation error', 'Services with prices are required for service booking types');
                }

                 $practice_license_path = uploadImage('assets/admin/uploads', $providerTypeData['practice_license']);
                 $identity_photo_path = uploadImage('assets/admin/uploads', $providerTypeData['identity_photo']);
                // Create provider type
                $providerType = \App\Models\ProviderType::create([
                    'provider_id' => $provider->id,
                    'type_id' => $providerTypeData['type_id'],
                    'name' => $providerTypeData['name'],
                    'description' => $providerTypeData['description'],
                    'lat' => $providerTypeData['lat'],
                    'lng' => $providerTypeData['lng'],
                    'practice_license' => $practice_license_path,
                    'identity_photo' =>  $identity_photo_path,
                    'address' => $providerTypeData['address'] ?? null,
                    'price_per_hour' => $type->booking_type === 'hourly' ? $providerTypeData['price_per_hour'] : 0,
                    'is_vip' => $providerTypeData['is_vip'] ?? 2,
                    'activate' => 1,
                    'status' => 1,
                ]);

                // Handle services based on booking type
                if ($type->booking_type === 'hourly') {
                    // For hourly types, use the existing service_ids approach
                    foreach ($providerTypeData['service_ids'] as $serviceId) {
                        \App\Models\ProviderServiceType::create([
                            'provider_type_id' => $providerType->id,
                            'service_id' => $serviceId,
                        ]);
                    }
                } else {
                    // For service types, use services_with_prices
                    if (isset($providerTypeData['services_with_prices'])) {
                        foreach ($providerTypeData['services_with_prices'] as $serviceData) {
                            // Create in ProviderServiceType for compatibility
                            \App\Models\ProviderServiceType::create([
                                'provider_type_id' => $providerType->id,
                                'service_id' => $serviceData['service_id'],
                            ]);

                            // Create in ProviderService with pricing
                            \App\Models\ProviderService::create([
                                'provider_type_id' => $providerType->id,
                                'service_id' => $serviceData['service_id'],
                                'price' => $serviceData['price'],
                                'is_active' => $serviceData['is_active'] ?? 1,
                            ]);
                        }
                    }
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
                foreach ($providerTypeData['availabilities'] as $availability) {
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
                    'providerTypes.type',
                    'providerTypes.services',
                    'providerTypes.providerServices.service',
                    'providerTypes.images',
                    'providerTypes.galleries',
                    'providerTypes.availabilities'
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
            'providerTypes.providerServices.service',
            'providerTypes.images',
            'providerTypes.galleries',
            'providerTypes.availabilities'
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
            ->with('type')
            ->first();

        if (!$providerType) {
            return $this->error_response('Not found', 'Provider type not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'practice_license' => 'sometimes',
            'identity_photo' => 'sometimes',
            'address' => 'nullable|string',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'is_vip' => 'sometimes|in:1,2',
            'status' => 'sometimes|in:1,2',
            'service_ids' => 'sometimes|array|min:1',
            'service_ids.*' => 'exists:services,id',
            // New validation for service pricing updates
            'services_with_prices' => 'sometimes|array',
            'services_with_prices.*.service_id' => 'required|exists:services,id',
            'services_with_prices.*.price' => 'required|numeric|min:0',
            'services_with_prices.*.is_active' => 'sometimes|boolean',
            // Images and galleries
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg',
            'galleries' => 'nullable|array',
            'galleries.*' => 'image|mimes:jpeg,png,jpg',
            // Availabilities
            'availabilities' => 'sometimes|array|min:1',
            'availabilities.*.day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'availabilities.*.start_time' => 'required',
            'availabilities.*.end_time' => 'required|after:availabilities.*.start_time',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            DB::beginTransaction();

            // Update provider type basic info
            $updateData = $request->only(['name', 'description', 'lat', 'lng', 'address', 'is_vip', 'status']);
            
            // Handle price_per_hour based on booking type
            if ($providerType->type->booking_type === 'hourly' && $request->has('price_per_hour')) {
                $updateData['price_per_hour'] = $request->price_per_hour;
            }
            
            $providerType->update($updateData);

            // ✅ Handle new file uploads for practice_license and identity_photo
            if ($request->hasFile('practice_license')) {
                $practiceLicensePath = uploadImage('assets/admin/uploads', $request->file('practice_license'));
                $providerType->update(['practice_license' => $practiceLicensePath]);
            }

            if ($request->hasFile('identity_photo')) {
                $identityPhotoPath = uploadImage('assets/admin/uploads', $request->file('identity_photo'));
                $providerType->update(['identity_photo' => $identityPhotoPath]);
            }

            // Update services based on booking type
            if ($providerType->type->booking_type === 'hourly') {
                // For hourly types, use traditional service_ids approach
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
            } else {
                // For service types, handle services_with_prices
                if ($request->has('services_with_prices')) {
                    // Delete existing services and provider services
                    \App\Models\ProviderServiceType::where('provider_type_id', $providerType->id)->delete();
                    \App\Models\ProviderService::where('provider_type_id', $providerType->id)->delete();
                    
                    // Add new services with prices
                    foreach ($request->services_with_prices as $serviceData) {
                        // Create in ProviderServiceType for compatibility
                        \App\Models\ProviderServiceType::create([
                            'provider_type_id' => $providerType->id,
                            'service_id' => $serviceData['service_id'],
                        ]);

                        // Create in ProviderService with pricing
                        \App\Models\ProviderService::create([
                            'provider_type_id' => $providerType->id,
                            'service_id' => $serviceData['service_id'],
                            'price' => $serviceData['price'],
                            'is_active' => $serviceData['is_active'] ?? 1,
                        ]);
                    }
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

            // Update availabilities if provided
            if ($request->has('availabilities')) {
                // Delete existing availability
                \App\Models\ProviderAvailability::where('provider_type_id', $providerType->id)->delete();
                
                // Add new availability
                foreach ($request->availabilities as $availability) {
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
                'providerServices.service',
                'images',
                'galleries',
                'availabilities'
            ]);

            return $this->success_response('Provider type updated successfully', [
                'provider_type' => $providerType
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Error updating provider type', $e->getMessage());
        }
    }

    /**
     * Add or update services with prices for a provider type
     * POST /api/v1/providers/types/{providerTypeId}/services
     */
    public function updateProviderTypeServices(Request $request, $providerTypeId)
    {
        $provider = auth()->user();
        
        if (!$provider instanceof \App\Models\Provider) {
            return $this->error_response('Unauthorized', 'Only providers can update services');
        }

        $providerType = \App\Models\ProviderType::where('id', $providerTypeId)
            ->where('provider_id', $provider->id)
            ->with('type')
            ->first();

        if (!$providerType) {
            return $this->error_response('Not found', 'Provider type not found');
        }

        if ($providerType->type->booking_type !== 'service') {
            return $this->error_response('Invalid operation', 'This provider type does not support service pricing');
        }

        $validator = Validator::make($request->all(), [
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        try {
            DB::beginTransaction();

            // Delete existing services
            \App\Models\ProviderServiceType::where('provider_type_id', $providerType->id)->delete();
            \App\Models\ProviderService::where('provider_type_id', $providerType->id)->delete();

            // Add new services with prices
            foreach ($request->services as $serviceData) {
                // Create in ProviderServiceType for compatibility
                \App\Models\ProviderServiceType::create([
                    'provider_type_id' => $providerType->id,
                    'service_id' => $serviceData['service_id'],
                ]);

                // Create in ProviderService with pricing
                \App\Models\ProviderService::create([
                    'provider_type_id' => $providerType->id,
                    'service_id' => $serviceData['service_id'],
                    'price' => $serviceData['price'],
                    'is_active' => $serviceData['is_active'] ?? 1,
                ]);
            }

            DB::commit();

            $providerType->load('providerServices.service');

            return $this->success_response('Provider services updated successfully', [
                'provider_type' => $providerType,
                'services' => $providerType->providerServices
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Error updating services', $e->getMessage());
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


        public function deleteProviderImages(Request $request)
        {
            $provider = auth()->user();
            
            // Check if user is a provider
            if (!$provider instanceof \App\Models\Provider) {
                return $this->error_response('Unauthorized', 'Only providers can delete images');
            }

            $validator = Validator::make($request->all(), [
                'image_ids' => 'required|array|min:1',
                'image_ids.*' => 'required|integer|exists:provider_images,id',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            try {
                DB::beginTransaction();

                $deletedCount = 0;
                $notFoundIds = [];

                foreach ($request->image_ids as $imageId) {
                    // Find the image and verify it belongs to the provider
                    $image = \App\Models\ProviderImage::whereHas('providerType', function ($query) use ($provider) {
                        $query->where('provider_id', $provider->id);
                    })->find($imageId);

                    if ($image) {
                        // Delete the physical file if it exists
                        if ($image->photo && file_exists(base_path($image->photo))) {
                            unlink(base_path($image->photo));
                        }
                        
                        // Delete the database record
                        $image->delete();
                        $deletedCount++;
                    } else {
                        $notFoundIds[] = $imageId;
                    }
                }

                DB::commit();

                $message = "Successfully deleted {$deletedCount} image(s)";
                if (!empty($notFoundIds)) {
                    $message .= ". Images with IDs [" . implode(', ', $notFoundIds) . "] were not found or don't belong to you";
                }

                return $this->success_response($message, [
                    'deleted_count' => $deletedCount,
                    'not_found_ids' => $notFoundIds
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                return $this->error_response('Error deleting images', $e->getMessage());
            }
        }

        public function deleteProviderGalleries(Request $request)
        {
            $provider = auth()->user();
            
            // Check if user is a provider
            if (!$provider instanceof \App\Models\Provider) {
                return $this->error_response('Unauthorized', 'Only providers can delete gallery images');
            }

            $validator = Validator::make($request->all(), [
                'gallery_ids' => 'required|array|min:1',
                'gallery_ids.*' => 'required|integer|exists:provider_galleries,id',
            ]);

            if ($validator->fails()) {
                return $this->error_response('Validation error', $validator->errors());
            }

            try {
                DB::beginTransaction();

                $deletedCount = 0;
                $notFoundIds = [];

                foreach ($request->gallery_ids as $galleryId) {
                    // Find the gallery image and verify it belongs to the provider
                    $gallery = \App\Models\ProviderGallery::whereHas('providerType', function ($query) use ($provider) {
                        $query->where('provider_id', $provider->id);
                    })->find($galleryId);

                    if ($gallery) {
                        // Delete the physical file if it exists
                        if ($gallery->photo && file_exists(base_path($gallery->photo))) {
                            unlink(base_path($gallery->photo));
                        }
                        
                        // Delete the database record
                        $gallery->delete();
                        $deletedCount++;
                    } else {
                        $notFoundIds[] = $galleryId;
                    }
                }

                DB::commit();

                $message = "Successfully deleted {$deletedCount} gallery image(s)";
                if (!empty($notFoundIds)) {
                    $message .= ". Gallery images with IDs [" . implode(', ', $notFoundIds) . "] were not found or don't belong to you";
                }

                return $this->success_response($message, [
                    'deleted_count' => $deletedCount,
                    'not_found_ids' => $notFoundIds
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                return $this->error_response('Error deleting gallery images', $e->getMessage());
            }
        }

}
