<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index()
    {
        try {
            $auth = auth()->user();
            if (!$auth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $paymentMethods = PaymentMethod::all();
            $cartItems = Cart::where('user_id', $auth->user_id)
                ->with('sizeProduct.product', 'sizeProduct.size')
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cart is empty'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $auth,
                    'cart_items' => $cartItems,
                    'payment_methods' => $paymentMethods
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch checkout data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function post_checkout(Request $request)
    {
        try {
            $auth = auth()->user();
            if (!$auth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $cartItems = Cart::where('user_id', $auth->user_id)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cart is empty'
                ], 404);
            }

            $totalPrice = $cartItems->sum('sub_price');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_price' => $totalPrice,
                    'cart_items' => $cartItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Checkout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function vnpay_payment(Request $request)
    {
        try {
            $auth = auth()->user();
            if (!$auth) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $cartItems = Cart::where('user_id', $auth->user_id)->get();
            $totalPrice = $cartItems->sum('sub_price');

            // Create order
            $order = Order::create([
                'user_id' => $auth->user_id,
                'total_price' => $totalPrice,
                'status' => 0,
                'order_date' => now(),
                'shipping_method_id' => $request->input('shipping_method_id', 1),
                'payment_method_id' => 1,
            ]);

            // Create order details
            foreach ($cartItems as $cart) {
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'product_id' => $cart->product_id,
                    'price' => $cart->price,
                    'quantity' => $cart->quantity,
                    'sub_price' => $cart->sub_price,
                    'size_product_id' => $cart->size_product_id,
                ]);
            }

            // Clear cart
            Cart::where('user_id', $auth->user_id)->delete();

            // VNPAY Payment setup
            $vnp_TxnRef = uniqid('order_' . $order->order_id . '_');
            $vnp_Amount = $totalPrice * 100;
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = "http://127.0.0.1:8000/api/order/payment/callback";

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => "1VYBIYQP",
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => request()->ip(),
                "vnp_Locale" => "vn",
                "vnp_OrderInfo" => 'Payment for order #' . $order->order_id,
                "vnp_OrderType" => "billpayment",
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnpSecureHash = hash_hmac('sha512', $hashdata, "NOH6MBGNLQL9O9OMMFMZ2AX8NIEP50W1");

            return response()->json([
                'status' => 'success',
                'data' => [
                    'payment_url' => $vnp_Url . "?" . $query . 'vnp_SecureHash=' . $vnpSecureHash,
                    'order_id' => $order->order_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment initialization failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Add callback handler for VNPAY
    public function vnpay_callback(Request $request)
    {
        try {
            if ($request->vnp_ResponseCode == '00') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment successful',
                    'data' => $request->all()
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed',
                'data' => $request->all()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment callback processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
