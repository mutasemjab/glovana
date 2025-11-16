<?php
namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Traits\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use Responses;

    public function index(Request $request)
    {
        $cart = Cart::with('product', 'product.images')
            ->where('user_id', $request->user()->id)
            ->where('status', 1)
            ->get();

        // Calculate cart summary
        $summary = [
            'total_items' => $cart->count(),
            'total_quantity' => $cart->sum('quantity'),
            'subtotal' => $cart->sum('total_price_product'),
            'total_discount' => $cart->sum('discount_coupon'),
            'total' => $cart->sum('total_price_product') - $cart->sum('discount_coupon'),
        ];

        return $this->success_response('Cart retrieved successfully', [
            'cart_items' => $cart,
            'summary' => $summary
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer', // can be negative
        ]);

        $product = Product::find($request->product_id);
        $price = $product->price;
        $userId = $request->user()->id;

        // Find existing cart item
        $cart = Cart::where('user_id', $userId)
                    ->where('product_id', $request->product_id)
                    ->where('status', 1)
                    ->first();

        // If cart exists → update by adding the quantity (can be negative)
        if ($cart) {
            $cart->quantity += $request->quantity;

            // If quantity becomes 0 or below → delete item
            if ($cart->quantity <= 0) {
                $cart->delete();
                return $this->success_response('Item removed from cart', null);
            }

            $cart->total_price_product = $cart->price * $cart->quantity;
            $cart->save();

            return $this->success_response('Cart updated successfully', $cart);
        }

        // If cart does NOT exist and quantity <= 0 → nothing to add
        if ($request->quantity <= 0) {
            return $this->error_response('Quantity must be greater than 0 to add to cart', null);
        }

        // Create new cart item
        $cart = Cart::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'quantity' => $request->quantity,
            'price' => $price,
            'total_price_product' => $price * $request->quantity,
            'status' => 1
        ]);

        return $this->success_response('Product added to cart', $cart);
    }



    public function delete($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return $this->error_response('Cart item not found', []);
        }

        $cart->delete();

        return $this->success_response('Cart item deleted', []);
    }
   
}