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

    /**
     * Display a listing of user addresses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        $addresses = UserAddress::with('delivery')->where('user_id', $user_id)->get();
        
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
            'address' => 'required|string',
            'lat' => 'required|string',
            'lng' => 'required|string',
            'delivery_id' => 'required|exists:deliveries,id',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // If user_id is not provided, use authenticated user's ID
        if (!$request->has('user_id')) {
            $request->merge(['user_id' => Auth::id()]);
        }

        $address = UserAddress::create($request->only([
            'user_id', 'address', 'lat', 'lng', 'delivery_id'
        ]));

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
        $address = UserAddress::with('delivery')->find($id);
        
        if (!$address) {
            return $this->error_response('Address not found', null);
        }

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
            'address' => 'sometimes|string',
            'lat' => 'sometimes|string',
            'lng' => 'sometimes|string',
            'delivery_id' => 'sometimes|exists:deliveries,id',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        $address->update($request->only([
            'address', 'lat', 'lng', 'delivery_id'
        ]));

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
     * Calculate delivery fee based on distance between user address and provider
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function calculateDeliveryFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:user_addresses,id',
            'provider_type_id' => 'required|exists:provider_types,id',
        ]);

        if ($validator->fails()) {
            return $this->error_response('Validation error', $validator->errors());
        }

        // Check if calculation method is set to distance-based (2)
        $calculation_method = DB::table('settings')
            ->where('key', 'calculate_delivery_fee_depend_on_the_place_or_distance')
            ->value('value');

        if ($calculation_method != 2) {
            return $this->error_response('Distance-based calculation is not enabled', null);
        }

        // Get user address coordinates
        $userAddress = UserAddress::find($request->address_id);
        if (!$userAddress) {
            return $this->error_response('User address not found', null);
        }

        // Get provider coordinates
        $provider = DB::table('provider_types')
            ->select('lat', 'lng')
            ->where('id', $request->provider_type_id)
            ->first();

        if (!$provider) {
            return $this->error_response('Provider type not found', null);
        }

        // Get pricing settings
        $settings = DB::table('settings')
            ->whereIn('key', ['start_price', 'price_per_km'])
            ->pluck('value', 'key')
            ->toArray();

        $start_price = floatval($settings['start_price'] ?? 0.25);
        $price_per_km = floatval($settings['price_per_km'] ?? 0.15);

        // Calculate distance using Haversine formula
        $distance_km = $this->calculateDistance(
            floatval($userAddress->lat),
            floatval($userAddress->lng),
            floatval($provider->lat),
            floatval($provider->lng)
        );

        // Calculate delivery fee: start_price + (distance * price_per_km)
        $delivery_fee = $start_price + ($distance_km * $price_per_km);
        $delivery_fee = round($delivery_fee, 2);

        return $this->success_response('Delivery fee calculated successfully', [
            'delivery_fee' => $delivery_fee,
            'distance_km' => round($distance_km, 2),
            'start_price' => $start_price,
            'price_per_km' => $price_per_km,
            'user_address' => [
                'lat' => $userAddress->lat,
                'lng' => $userAddress->lng
            ],
            'provider_location' => [
                'lat' => $provider->lat,
                'lng' => $provider->lng
            ]
        ]);
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
}