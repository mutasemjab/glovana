<?php
namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Traits\Responses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use Responses;

      public function index(Request $request)
    {
        $orders = Order::with('orderProducts','orderProducts.product','orderProducts.product.images')->where('user_id', $request->user()->id)->get();
        return $this->success_response('Orders retrieved successfully', $orders);
    }

   public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'payment_type' => 'required|in:cash,card',
            'coupon_code' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $user = $request->user();
            $cartItems = Cart::where('user_id', $user->id)->where('status', 1)->get();

            if ($cartItems->isEmpty()) {
                return $this->error_response('Cart is empty', []);
            }

            $deliveryAddress = \App\Models\UserAddress::find($request->address_id);
            $deliveryFee = $deliveryAddress->delivery->price ?? 0;

            $totalTax = 0;
            $totalBeforeTax = 0;
            $totalDiscount = 0;
            $orderProducts = [];

            foreach ($cartItems as $item) {
                $product = Product::find($item->product_id);

                $basePrice = $product->price_after_discount ?? $product->price;
                $discountValue = $product->price - $basePrice;
                $productSubtotal = $basePrice * $item->quantity;

                $taxRate = $product->tax ?? 10; // default 10%
                $taxValue = $productSubtotal * ($taxRate / 100);

                $orderProducts[] = [
                    'order_id' => null,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $basePrice,
                    'total_price_before_tax' => $productSubtotal,
                    'tax_percentage' => $taxRate,
                    'tax_value' => $taxValue,
                    'total_price_after_tax' => $productSubtotal + $taxValue,
                    'discount_percentage' => $product->discount_percentage ?? 0,
                    'discount_value' => $discountValue * $item->quantity,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $totalBeforeTax += $productSubtotal;
                $totalTax += $taxValue;
                $totalDiscount += $discountValue * $item->quantity;
            }

            $couponDiscount = 0;
            $couponId = null;

            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->whereDate('expired_at', '>=', today())
                    ->first();

                if ($coupon) {
                    $alreadyUsed = DB::table('user_coupons')
                        ->where('user_id', $user->id)
                        ->where('coupon_id', $coupon->id)
                        ->exists();

                    if (!$alreadyUsed && $totalBeforeTax >= $coupon->minimum_total) {
                        $couponDiscount = $coupon->amount;
                        $couponId = $coupon->id;

                        // Store user-coupon usage
                        DB::table('user_coupons')->insert([
                            'user_id' => $user->id,
                            'coupon_id' => $coupon->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $totalFinal = $totalBeforeTax + $totalTax + $deliveryFee - $couponDiscount;

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'total_prices' => $totalFinal,
                'total_taxes' => $totalTax,
                'delivery_fee' => $deliveryFee,
                'total_discounts' => $totalDiscount,
                'coupon_discount' => $couponDiscount,
                'payment_type' => $request->payment_type,
                'payment_status' => 2,
                'order_status' => 1,
                'date' => now(),
                'note' => $request->note
            ]);

            foreach ($orderProducts as &$op) {
                $op['order_id'] = $order->id;
            }

            OrderProduct::insert($orderProducts);

            Cart::where('user_id', $user->id)->where('status', 1)->update(['status' => 2]);

            DB::commit();
            return $this->success_response('Order created successfully', $order);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response('Failed to create order', $e->getMessage());
        }
    }


    public function details($id)
    {
        $order = Order::with('orderProducts','orderProducts.product','orderProducts.product.images')->find($id);

        if (!$order) {
            return $this->error_response('Order not found', []);
        }

        return $this->success_response('Order details', $order);
    }

    public function cancelOrder($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->error_response('Order not found', []);
        }

        $order->order_status = 5; // Cancelled
        $order->save();

        return $this->success_response('Order cancelled successfully', $order);
    }
   
}