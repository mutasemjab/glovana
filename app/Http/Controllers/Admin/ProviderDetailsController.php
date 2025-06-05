<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderServiceType;
use App\Models\ProviderImage;
use App\Models\ProviderGallery;
use App\Models\ProviderAvailability;
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
        $providerServiceTypes = ProviderServiceType::with(['service', 'type', 'images'])
            ->where('provider_id', $providerId)
            ->get();

        return view('admin.providerDetails.index', compact('provider', 'providerServiceTypes'));
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
            'service_id' => 'required|exists:services,id',
            'type_id' => 'required|exists:types,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'nullable|string',
            'price_per_hour' => 'required|numeric|min:0',
            'activate' => 'required|in:1,2',
            'status' => 'required|in:1,2',
            'is_vip' => 'required|in:1,2',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'galleries.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create provider service type
        $providerServiceType = ProviderServiceType::create([
            'provider_id' => $providerId,
            'service_id' => $request->service_id,
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

        // Handle images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = uploadImage('assets/admin/uploads', $image);
                ProviderImage::create([
                    'provider_service_type_id' => $providerServiceType->id,
                    'photo' => $imagePath,
                ]);
            }
        }

        // Handle galleries upload
        if ($request->hasFile('galleries')) {
            foreach ($request->file('galleries') as $gallery) {
                $galleryPath = uploadImage('assets/admin/uploads', $gallery);
                ProviderGallery::create([
                    'provider_service_type_id' => $providerServiceType->id,
                    'photo' => $galleryPath,
                ]);
            }
        }

        return redirect()->route('admin.providerDetails.index', $providerId)
            ->with('success', __('messages.Provider_Service_Created'));
    }

    public function edit($providerId, $serviceTypeId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerServiceType = ProviderServiceType::with(['images', 'galleries'])->findOrFail($serviceTypeId);
        $services = Service::all();
        $types = Type::all();

        return view('admin.providerDetails.edit', compact('provider', 'providerServiceType', 'services', 'types'));
    }

    public function update(Request $request, $providerId, $serviceTypeId)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'type_id' => 'required|exists:types,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'nullable|string',
            'price_per_hour' => 'required|numeric|min:0',
            'activate' => 'required|in:1,2',
            'status' => 'required|in:1,2',
            'is_vip' => 'required|in:1,2',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'galleries.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $providerServiceType = ProviderServiceType::findOrFail($serviceTypeId);

        // Update provider service type
        $providerServiceType->update([
            'service_id' => $request->service_id,
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

        // Handle new images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = uploadImage('assets/admin/uploads', $image);
                ProviderImage::create([
                    'provider_service_type_id' => $providerServiceType->id,
                    'photo' => $imagePath,
                ]);
            }
        }

        // Handle new galleries upload
        if ($request->hasFile('galleries')) {
            foreach ($request->file('galleries') as $gallery) {
                $galleryPath = uploadImage('assets/admin/uploads', $gallery);
                ProviderGallery::create([
                    'provider_service_type_id' => $providerServiceType->id,
                    'photo' => $galleryPath,
                ]);
            }
        }

        return redirect()->route('admin.providerDetails.index', $providerId)
            ->with('success', __('messages.Provider_Service_Updated'));
    }

    public function destroy($providerId, $serviceTypeId)
    {
        $providerServiceType = ProviderServiceType::findOrFail($serviceTypeId);
        $providerServiceType->delete();

        return redirect()->route('admin.providerDetails.index', $providerId)
            ->with('success', __('messages.Provider_Service_Deleted'));
    }

    // Availability Management
    public function availabilities($providerId, $serviceTypeId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerServiceType = ProviderServiceType::findOrFail($serviceTypeId);
        $availabilities = ProviderAvailability::where('provider_service_type_id', $serviceTypeId)->get();

        return view('admin.providerDetails.availabilities', compact('provider', 'providerServiceType', 'availabilities'));
    }

    public function storeAvailability(Request $request, $providerId, $serviceTypeId)
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
            'provider_service_type_id' => $serviceTypeId,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->back()->with('success', __('messages.Availability_Added'));
    }

    public function destroyAvailability($providerId, $serviceTypeId, $availabilityId)
    {
        $availability = ProviderAvailability::findOrFail($availabilityId);
        $availability->delete();

        return redirect()->back()->with('success', __('messages.Availability_Deleted'));
    }

    // Unavailability Management
    public function unavailabilities($providerId, $serviceTypeId)
    {
        $provider = Provider::findOrFail($providerId);
        $providerServiceType = ProviderServiceType::findOrFail($serviceTypeId);
        $unavailabilities = ProviderUnavailability::where('provider_service_type_id', $serviceTypeId)->get();

        return view('admin.providerDetails.unavailabilities', compact('provider', 'providerServiceType', 'unavailabilities'));
    }

    public function storeUnavailability(Request $request, $providerId, $serviceTypeId)
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
            'provider_service_type_id' => $serviceTypeId,
            'unavailable_date' => $request->unavailable_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->back()->with('success', __('messages.Unavailability_Added'));
    }

    public function destroyUnavailability($providerId, $serviceTypeId, $unavailabilityId)
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