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

    // Calculate cart summary with taxes
    $totalTax = 0;
    $totalBeforeTax = 0;
    $totalDiscount = 0;

    foreach ($cart as $item) {
        $product = $item->product;
        
        // Calculate base price (after product discount)
        $basePrice = $product->price_after_discount ?? $product->price;
        $productSubtotal = $basePrice * $item->quantity;
        
        // Calculate tax
        $taxRate = $product->tax ?? 16; // Default 10% if no tax set
        $taxValue = $productSubtotal * ($taxRate / 100);
        
        // Calculate discount
        $discountValue = ($product->price - $basePrice) * $item->quantity;
        
        $totalBeforeTax += $productSubtotal;
        $totalTax += $taxValue;
        $totalDiscount += $discountValue;
    }

    $summary = [
        'total_items' => $cart->count(),
        'total_quantity' => $cart->sum('quantity'),
        'subtotal' => $totalBeforeTax, // Total before tax
        'total_tax' => round($totalTax, 2), // Total tax amount
        'total_discount' => $totalDiscount, // Product discounts
        'total_before_tax' => $totalBeforeTax,
        'total_after_tax' => round($totalBeforeTax + $totalTax, 2),
        'total' => round($totalBeforeTax + $totalTax, 2), // Final total with tax
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
        
        // Use price_after_discount if available, otherwise use regular price
        $price = $product->price_after_discount ?? $product->price;
        
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