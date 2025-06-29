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
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:types,id',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'nullable|string',
            'price_per_hour' => 'required|numeric|min:0',
            'activate' => 'required|in:1,2',
            'status' => 'required|in:1,2',
            'is_vip' => 'required|in:1,2',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'galleries.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

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
            'address' => $request->address,
            'price_per_hour' => $request->price_per_hour,
            'activate' => $request->activate,
            'status' => $request->status,
            'is_vip' => $request->is_vip,
        ]);

        // Create provider services for selected services
        foreach ($request->service_ids as $serviceId) {
            ProviderServiceType::create([
                'provider_type_id' => $providerType->id,
                'service_id' => $serviceId,
            ]);
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
        $providerType = ProviderType::with(['images', 'galleries', 'services'])->findOrFail($providerTypeId);
        $services = Service::all();
        $types = Type::all();
        $selectedServiceIds = $providerType->services->pluck('service_id')->toArray();

        return view('admin.providerDetails.edit', compact('provider', 'providerType', 'services', 'types', 'selectedServiceIds'));
    }

    public function update(Request $request, $providerId, $providerTypeId)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:types,id',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'nullable|string',
            'price_per_hour' => 'required|numeric|min:0',
            'activate' => 'required|in:1,2',
            'status' => 'required|in:1,2',
            'is_vip' => 'required|in:1,2',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'galleries.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $providerType = ProviderType::findOrFail($providerTypeId);

        // Update provider type
        $providerType->update([
            'type_id' => $request->type_id,
            'name' => $request->name,
            'description' => $request->description,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'address' => $request->address,
            'price_per_hour' => $request->price_per_hour,
            'activate' => $request->activate,
            'status' => $request->status,
            'is_vip' => $request->is_vip,
        ]);

        // Update services: Delete old services and create new ones
        ProviderServiceType::where('provider_type_id', $providerType->id)->delete();
        foreach ($request->service_ids as $serviceId) {
            ProviderServiceType::create([
                'provider_type_id' => $providerType->id,
                'service_id' => $serviceId,
            ]);
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

    // Availability Management
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

    // Unavailability Management
    public function unavailabilities($providerId, $providerTypeId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerType = ProviderType::findOrFail($providerTypeId);
        $unavailabilities = ProviderUnavailability::where('provider_type_id', $providerTypeId)->get();

        return view('admin.providerDetails.unavailabilities', compact('provider', 'providerType', 'unavailabilities'));
    }

    public function storeUnavailability(Request $request, $providerId, $providerTypeId)
    {
        $validator = Validator::make($request->all(), [
            'unavailable_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ProviderUnavailability::create([
            'provider_type_id' => $providerTypeId,
            'unavailable_date' => $request->unavailable_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->back()->with('success', __('messages.Unavailability_Added'));
    }

    public function destroyUnavailability($providerId, $providerTypeId, $unavailabilityId)
    {
        $unavailability = ProviderUnavailability::findOrFail($unavailabilityId);
        $unavailability->delete();

        return redirect()->back()->with('success', __('messages.Unavailability_Deleted'));
    }

    // Image Management
    public function deleteImage($imageId)
    {
        $image = ProviderImage::findOrFail($imageId);
        $image->delete();

        return response()->json(['success' => true]);
    }

    public function deleteGallery($galleryId)
    {
        $gallery = ProviderGallery::findOrFail($galleryId);
        $gallery->delete();

        return response()->json(['success' => true]);
    }
}