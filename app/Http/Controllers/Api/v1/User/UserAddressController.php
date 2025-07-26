<?php


namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    use Responses;

    public function index(Request $request)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        $addresses = UserAddress::with('delivery')->where('user_id', $user_id)->get();
        
        // Add delivery fee calculation to each address
        $addresses->transform(function ($address) {
            $address->delivery_fee = $this->calculateDeliveryFee($address);
            return $address;
        });
        
        return $this->success_response('Addresses retrieved successfully', $addresses);
    }

    /**
     * Store a newly created address in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'address' => 'nullable',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'delivery_id' => 'required',
            'provider_type_id' => 'sometimes|exists:provider_types,id', // Add this if needed
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // If user_id is not provided, use authenticated user's ID
        if (!$request->has('user_id')) {
            $request->merge(['user_id' => Auth::id()]);
        }

        $address = UserAddress::create($request->only([
            'user_id', 'name', 'address', 'lat', 'lng', 'delivery_id'
        ]));

        // Calculate and add delivery fee
        $address->delivery_fee = $this->calculateDeliveryFee($address);

        return $this->success_response('Address created successfully', $address);
    }

    /**
     * Display the specified address.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $address = UserAddress::find($id);
        
        if (!$address) {
            return $this->error_response('Address not found', null);
        }

        // Calculate and add delivery fee
        $address->delivery_fee = $this->calculateDeliveryFee($address);

        return $this->success_response('Address retrieved successfully', $address);
    }

    /**
     * Update the specified address in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $address = UserAddress::find($id);
        
        if (!$address) {
            return $this->error_response('Address not found', null);
        }

        $validator = Validator::make($request->all(), [
            'address' => 'nullable',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric',
            'delivery_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $address->update($request->only([
            'lat', 'lng', 'address', 'delivery_id'
        ]));

        // Calculate and add delivery fee
        $address->delivery_fee = $this->calculateDeliveryFee($address);

        return $this->success_response('Address updated successfully', $address);
    }

    /**
     * Remove the specified address from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $address = UserAddress::find($id);
        
        if (!$address) {
            return $this->error_response('Address not found', null);
        }

        // Check if the authenticated user is authorized to delete this address
        if (Auth::id() != $address->user_id) {
            return $this->error_response('Unauthorized access', null);
        }

        $address->delete();

        return $this->success_response('Address deleted successfully', null);
    }

    /**
     * Calculate delivery fee based on settings
     *
     * @param  UserAddress  $address
     * @param  int|null  $provider_type_id
     * @return float
     */
    private function calculateDeliveryFee($address, $provider_type_id = null)
    {
        // Get settings from database
        $settings = $this->getSettings([
            'calculate_delivery_fee_depend_on_the_place_or_distance',
            'start_price',
            'price_per_km'
        ]);

        $calculation_method = $settings['calculate_delivery_fee_depend_on_the_place_or_distance'] ?? 1;

        // If calculation method is 'place', return the delivery fee from the delivery record
        if ($calculation_method == 1) {
            return $address->delivery ? floatval($address->delivery->price ?? 0) : 0;
        }

        // If calculation method is 'distance', calculate based on distance
        if ($calculation_method == 2) {
            return $this->calculateDistanceBasedFee($address, $settings, $provider_type_id);
        }

        return 0;
    }

    /**
     * Calculate distance-based delivery fee
     *
     * @param  UserAddress  $address
     * @param  array  $settings
     * @param  int|null  $provider_type_id
     * @return float
     */
    private function calculateDistanceBasedFee($address, $settings, $provider_type_id = null)
    {
        $start_price = floatval($settings['start_price'] ?? 0.25);
        $price_per_km = floatval($settings['price_per_km'] ?? 0.15);

        // Get provider location - you'll need to adjust this based on your provider_types table structure
        $provider_location = $this->getProviderLocation($provider_type_id);
        
        if (!$provider_location) {
            // If no provider location found, return start price as fallback
            return $start_price;
        }

        // Calculate distance in kilometers
        $distance_km = $this->calculateDistance(
            $address->lat,
            $address->lng,
            $provider_location['lat'],
            $provider_location['lng']
        );

        // Calculate total fee: start_price + (distance * price_per_km)
        $total_fee = $start_price + ($distance_km * $price_per_km);

        return round($total_fee, 2);
    }

    /**
     * Get provider location coordinates
     *
     * @param  int|null  $provider_type_id
     * @return array|null
     */
    private function getProviderLocation($provider_type_id = null)
    {
        if (!$provider_type_id) {
            // You might want to get the default provider or handle this case differently
            return null;
        }

        // Adjust this query based on your provider_types table structure
        $provider = DB::table('provider_types')
            ->select('lat', 'lng')
            ->where('id', $provider_type_id)
            ->first();

        if (!$provider) {
            return null;
        }

        return [
            'lat' => $provider->lat,
            'lng' => $provider->lng
        ];
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param  float  $lat1
     * @param  float  $lng1
     * @param  float  $lat2
     * @param  float  $lng2
     * @return float Distance in kilometers
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earth_radius * $c;

        return $distance;
    }

    /**
     * Get settings from database
     *
     * @param  array  $keys
     * @return array
     */
    private function getSettings($keys)
    {
        $settings = DB::table('settings')
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        return $settings;
    }

    /**
     * Calculate delivery fee for a specific address and provider
     * This can be called externally when you have the provider_type_id
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function calculateFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:user_addresses,id',
            'provider_type_id' => 'sometimes|exists:provider_types,id',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $address = UserAddress::with('delivery')->find($request->address_id);
        $provider_type_id = $request->provider_type_id ?? null;

        $delivery_fee = $this->calculateDeliveryFee($address, $provider_type_id);

        return $this->success_response('Delivery fee calculated successfully', [
            'address' => $address,
            'delivery_fee' => $delivery_fee,
            'provider_type_id' => $provider_type_id
        ]);
    }
}