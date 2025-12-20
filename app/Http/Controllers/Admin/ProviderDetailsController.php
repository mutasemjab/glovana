<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderServiceType;
use App\Models\ProviderImage;
use App\Models\ProviderGallery;
use App\Models\ProviderAvailability;
use App\Models\ProviderType;
use App\Models\ProviderUnavailability;
use App\Models\Service;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ProviderDetailsController extends Controller
{
    public function index($providerId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerTypes = ProviderType::with(['type', 'images', 'services.service'])
            ->where('provider_id', $providerId)
            ->get();

        return view('admin.providerDetails.index', compact('provider', 'providerTypes'));
    }

    public function create($providerId)
    {
        $provider = Provider::findOrFail($providerId);
        $services = Service::all();
        $types = Type::all();

        return view('admin.providerDetails.create', compact('provider', 'services', 'types'));
    }

    public function store(Request $request, $providerId)
    {
        // Get the selected type to check booking type
        $selectedType = Type::findOrFail($request->type_id);
        
        $validationRules = [
            'type_id' => 'required|exists:types,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'number_of_work' => 'required|numeric',
            'address' => 'nullable|string',
            'activate' => 'required|in:1,2',
            'status' => 'required|in:1,2',
            'is_vip' => 'required|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'galleries.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];

        // Add validation rules based on booking type
        if ($selectedType->booking_type == 'hourly') {
            $validationRules['service_ids'] = 'required|array|min:1';
            $validationRules['service_ids.*'] = 'exists:services,id';
            $validationRules['price_per_hour'] = 'required|numeric|min:0';
        } else {
            $validationRules['service_prices'] = 'required|array|min:1';
           $validationRules['service_prices.*'] = 'nullable|numeric|min:0';
            $validationRules['price_per_hour'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create provider type
        $providerType = ProviderType::create([
            'provider_id' => $providerId,
            'type_id' => $request->type_id,
            'name' => $request->name,
            'description' => $request->description,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'number_of_work' => $request->number_of_work,
            'address' => $request->address,
            'price_per_hour' => $request->price_per_hour ?? 0,
            'activate' => $request->activate,
            'status' => $request->status,
            'is_vip' => $request->boolean('is_vip'),
        ]);

        // Handle services based on booking type
        if ($selectedType->booking_type == 'hourly') {
            // Create provider services for hourly booking (old way)
            foreach ($request->service_ids as $serviceId) {
                ProviderServiceType::create([
                    'provider_type_id' => $providerType->id,
                    'service_id' => $serviceId,
                ]);
            }
        } else {
            // Create provider services with individual pricing (new way)
            foreach ($request->service_prices as $serviceId => $price) {
               if (!empty($price) && $price > 0) {
                    DB::table('provider_services')->insert([
                        'provider_type_id' => $providerType->id,
                        'service_id' => $serviceId,
                        'price' => $price,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Handle images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = uploadImage('assets/admin/uploads', $image);
                ProviderImage::create([
                    'provider_type_id' => $providerType->id,
                    'photo' => $imagePath,
                ]);
            }
        }

        // Handle galleries upload
        if ($request->hasFile('galleries')) {
            foreach ($request->file('galleries') as $gallery) {
                $galleryPath = uploadImage('assets/admin/uploads', $gallery);
                ProviderGallery::create([
                    'provider_type_id' => $providerType->id,
                    'photo' => $galleryPath,
                ]);
            }
        }

        return redirect()->route('admin.providerDetails.index', $providerId)
            ->with('success', __('messages.Provider_Type_Created'));
    }

    public function edit($providerId, $providerTypeId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerType = ProviderType::with(['type', 'images', 'galleries', 'services'])->findOrFail($providerTypeId);
        $services = Service::all();
        $types = Type::all();
        $selectedServiceIds = $providerType->services->pluck('service_id')->toArray();
        
        // Get provider services with pricing for service-based types
        $providerServices = [];
        if ($providerType->type->booking_type == 'service') {
            $providerServices = DB::table('provider_services')
                ->where('provider_type_id', $providerTypeId)
                ->pluck('price', 'service_id')
                ->toArray();
        }

        return view('admin.providerDetails.edit', compact('provider', 'providerType', 'services', 'types', 'selectedServiceIds', 'providerServices'));
    }

    public function update(Request $request, $providerId, $providerTypeId)
    {
        $providerType = ProviderType::with('type')->findOrFail($providerTypeId);
        
        $validationRules = [
            'type_id' => 'required|exists:types,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'number_of_work' => 'required|numeric',
            'address' => 'nullable|string',
            'activate' => 'required|in:1,2',
            'status' => 'required|in:1,2',
            'is_vip' => 'required|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'galleries.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];

        // Add validation rules based on booking type
        if ($providerType->type->booking_type == 'hourly') {
            $validationRules['service_ids'] = 'required|array|min:1';
            $validationRules['service_ids.*'] = 'exists:services,id';
            $validationRules['price_per_hour'] = 'required|numeric|min:0';
        } else {
            $validationRules['service_prices'] = 'required|array|min:1';
           $validationRules['service_prices.*'] = 'nullable|numeric|min:0';
            $validationRules['price_per_hour'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update provider type
        $providerType->update([
            'type_id' => $request->type_id,
            'name' => $request->name,
            'description' => $request->description,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'number_of_work' => $request->number_of_work,
            'address' => $request->address,
            'price_per_hour' => $request->price_per_hour ?? 0,
            'activate' => $request->activate,
            'status' => $request->status,
            'is_vip' => $request->boolean('is_vip'),,
        ]);

        // Update services based on booking type
        if ($providerType->type->booking_type == 'hourly') {
            // Update services for hourly booking (old way)
            ProviderServiceType::where('provider_type_id', $providerType->id)->delete();
            foreach ($request->service_ids as $serviceId) {
                ProviderServiceType::create([
                    'provider_type_id' => $providerType->id,
                    'service_id' => $serviceId,
                ]);
            }
        } else {
            // Update services with individual pricing (new way)
            DB::table('provider_services')->where('provider_type_id', $providerType->id)->delete();
            foreach ($request->service_prices as $serviceId => $price) {
                 if (!empty($price) && $price > 0) {
                    DB::table('provider_services')->insert([
                        'provider_type_id' => $providerType->id,
                        'service_id' => $serviceId,
                        'price' => $price,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Handle new images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = uploadImage('assets/admin/uploads', $image);
                ProviderImage::create([
                    'provider_type_id' => $providerType->id,
                    'photo' => $imagePath,
                ]);
            }
        }

        // Handle new galleries upload
        if ($request->hasFile('galleries')) {
            foreach ($request->file('galleries') as $gallery) {
                $galleryPath = uploadImage('assets/admin/uploads', $gallery);
                ProviderGallery::create([
                    'provider_type_id' => $providerType->id,
                    'photo' => $galleryPath,
                ]);
            }
        }

        return redirect()->route('admin.providerDetails.index', $providerId)
            ->with('success', __('messages.Provider_Type_Updated'));
    }

    public function destroy($providerId, $providerTypeId)
    {
        $providerType = ProviderType::findOrFail($providerTypeId);
        $providerType->delete();

        return redirect()->route('admin.providerDetails.index', $providerId)
            ->with('success', __('messages.Provider_Type_Deleted'));
    }

    // Provider Services Management
    public function manageServices($providerId, $providerTypeId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerType = ProviderType::with('type')->findOrFail($providerTypeId);
        
        if (!isset($providerType->type->booking_type) || $providerType->type->booking_type !== 'service') {
            return redirect()->back()->with('error', __('messages.Not_Service_Based_Type'));
        }

        $providerServices = DB::table('provider_services')
            ->join('services', 'provider_services.service_id', '=', 'services.id')
            ->where('provider_services.provider_type_id', $providerTypeId)
            ->select('provider_services.*', 'services.name_en', 'services.name_ar')
            ->get();

        $availableServices = DB::table('services')
            ->whereNotIn('id', function($query) use ($providerTypeId) {
                $query->select('service_id')
                    ->from('provider_services')
                    ->where('provider_type_id', $providerTypeId);
            })
            ->get();

        return view('admin.providerDetails.services', compact('provider', 'providerType', 'providerServices', 'availableServices'));
    }

    public function storeService(Request $request, $providerId, $providerTypeId)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|in:1,0',
        ]);

        // Check if service already exists for this provider type
        $exists = DB::table('provider_services')
            ->where('provider_type_id', $providerTypeId)
            ->where('service_id', $request->service_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', __('messages.Service_Already_Added'));
        }

        DB::table('provider_services')->insert([
            'provider_type_id' => $providerTypeId,
            'service_id' => $request->service_id,
            'price' => $request->price,
            'is_active' => $request->is_active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', __('messages.Service_Added_Successfully'));
    }

    public function updateService(Request $request, $providerId, $providerTypeId, $serviceId)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|in:1,0',
        ]);

        DB::table('provider_services')
            ->where('provider_type_id', $providerTypeId)
            ->where('service_id', $serviceId)
            ->update([
                'price' => $request->price,
                'is_active' => $request->is_active,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', __('messages.Service_Updated_Successfully'));
    }

    public function destroyService($providerId, $providerTypeId, $serviceId)
    {
        DB::table('provider_services')
            ->where('provider_type_id', $providerTypeId)
            ->where('service_id', $serviceId)
            ->delete();

        return redirect()->back()->with('success', __('messages.Service_Removed_Successfully'));
    }

    // Availability Management (existing methods remain the same)
    public function availabilities($providerId, $providerTypeId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerType = ProviderType::findOrFail($providerTypeId);
        $availabilities = ProviderAvailability::where('provider_type_id', $providerTypeId)->get();

        return view('admin.providerDetails.availabilities', compact('provider', 'providerType', 'availabilities'));
    }

    public function storeAvailability(Request $request, $providerId, $providerTypeId)
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ProviderAvailability::create([
            'provider_type_id' => $providerTypeId,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->back()->with('success', __('messages.Availability_Added'));
    }

    public function destroyAvailability($providerId, $providerTypeId, $availabilityId)
    {
        $availability = ProviderAvailability::findOrFail($availabilityId);
        $availability->delete();

        return redirect()->back()->with('success', __('messages.Availability_Deleted'));
    }

    public function deleteImage($imageId)
    {
        try {
            $image = ProviderImage::findOrFail($imageId);
            
            // Delete the physical file if it exists
            if ($image->photo && file_exists(base_path('assets/admin/uploads/' . $image->photo))) {
                unlink(base_path('assets/admin/uploads/' . $image->photo));
            }
            
            // Delete the database record
            $image->delete();
            
            return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting image'], 500);
        }
    }

    public function deleteGallery($galleryId)
    {
        try {
            $gallery = ProviderGallery::findOrFail($galleryId);
            
            // Delete the physical file if it exists
            if ($gallery->photo && file_exists(base_path('assets/admin/uploads/' . $gallery->photo))) {
                unlink(base_path('assets/admin/uploads/' . $gallery->photo));
            }
            
            // Delete the database record
            $gallery->delete();
            
            return response()->json(['success' => true, 'message' => 'Gallery image deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting gallery image'], 500);
        }
    }
}