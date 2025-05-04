<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ProductSize;
use App\Models\Size_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartsController extends Controller
{
    /**
     * Lấy thông tin giỏ hàng của người dùng đã đăng nhập.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = Cart::where('user_id', $user->id)
            ->with('productSize.product', 'productSize.size') // Eager load các relationship cần thiết
            ->get();

        return response()->json(['cart' => $cartItems]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng của người dùng đã đăng nhập.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // 1. Kiểm tra người dùng đã đăng nhập
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng'], 401);
            }

            // 2. Validate input
            $request->validate([
                'product_size_id' => 'required|exists:size_products,size_product_id',
                'quantity' => 'required|integer|min:1'
            ]);

            $productSizeId = $request->input('product_size_id');
            $quantity = $request->input('quantity');

            // 3. Lấy thông tin sản phẩm theo kích thước, kèm quan hệ product
            $sizeProduct = Size_product::with('product')->find($productSizeId);

            if (!$sizeProduct) {
                return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
            }

            // 4. Kiểm tra tồn kho (nếu có)
            if (isset($sizeProduct->stock) && $sizeProduct->stock < $quantity) {
                return response()->json(['message' => 'Không đủ hàng trong kho'], 400);
            }

            // 5. Kiểm tra item đã có trong giỏ hàng của người dùng chưa
            $existingCartItem = Cart::where('user_id', $userId)
                ->where('size_product_id', $productSizeId)
                ->first();

            // 6. Tính toán giá
            $unitPrice = $sizeProduct->product->price ?? 0; // Lấy giá từ bảng products
            $subPrice = $unitPrice * $quantity;

            // 7. Cập nhật hoặc thêm mới item vào giỏ hàng
            if ($existingCartItem) {
                $existingCartItem->quantity += $quantity;
                $existingCartItem->sub_price = $unitPrice * $existingCartItem->quantity;
                $existingCartItem->save();
                $cartItem = $existingCartItem;
            } else {
                $cartItem = Cart::create([
                    'user_id' => $userId,
                    'size_product_id' => $productSizeId,
                    'quantity' => $quantity,
                    'sub_price' => $subPrice,
                ]);
            }

            // 8. Trả về response
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công',
                'data' => [
                    'cart' => $cartItem->load('sizeProduct.product', 'sizeProduct.size'), // Load relations để có thông tin đầy đủ
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thêm sản phẩm vào giỏ hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }




}
