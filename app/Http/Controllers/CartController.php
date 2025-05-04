<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Size_product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Validator;
class CartController extends Controller
{
    public function addToCarts(Request $request)
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng'], 401);
            }

            // Validate input
            $request->validate([
                'size_product_id' => 'required|exists:size_products,size_product_id',
                'quantity' => 'required|integer|min:1'
            ]);

            // Lấy sản phẩm từ request
            //$sizeProduct = Size_product::with('product')->find($request->size_product_id);
            $sizeProduct = Size_product::find($request->size_product_id);
            if (!$sizeProduct) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Kiểm tra tồn kho
            if ($sizeProduct->stock < $request->quantity) {
                return response()->json(['message' => 'Not enough stock'], 400);
            }

            // Kiểm tra và cập nhật giỏ hàng
            $cart = Cart::where('user_id', $userId)->where('size_product_id', $request->size_product_id)->first();

            // Cập nhật hoặc thêm giỏ hàng
            $quantity = ($cart ? $cart->quantity : 0) + $request->quantity;

            $cart = Cart::updateOrCreate(
                [
                    'user_id' => $userId,
                    'size_product_id' => $request->size_product_id
                ],
                [
                    'quantity' => $quantity,
                    'sub_price' => $sizeProduct->product->price * $quantity
                ]
            );

            // Cập nhật tồn kho
            $sizeProduct->update(['stock' => $sizeProduct->stock - $request->quantity]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'data' => [
                    'cart' => $cart,
                    'product' => $sizeProduct->product,
                    'size' => $sizeProduct->size
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }








public function getCart(Request $request)
    {
        $userId = auth()->id(); // Lấy ID của user đã đăng nhập

        if (!$userId) {
            return response()->json(['message' => 'Bạn chưa đăng nhập'], 401);
        }

        // Lấy giỏ hàng theo user_id, bao gồm thông tin liên quan
        $cartItems = Cart::where('user_id', $userId)
            ->with('sizeProduct.product')
            ->get();

        return response()->json([
            'cart' => $cartItems,
        ]);
    }








    public function updateCart(Request $request, $cart_id)
    {
        $userId = auth()->id(); // Lấy ID của user đang đăng nhập

        // Validate request
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        // Tìm sản phẩm trong giỏ hàng của user
        $cartItem = Cart::where('user_id', $userId)
                        ->where('cart_id', $cart_id)
                        ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ hàng'], 404);
        }

        // Cập nhật số lượng và giá tiền
        $cartItem->quantity = $request->quantity;
        $cartItem->sub_price = $cartItem->sizeProduct->product->price * $request->quantity; // Cập nhật tổng giá

        $cartItem->save();

        return response()->json([
            'message' => 'Cập nhật giỏ hàng thành công',
            'cart' => $cartItem
        ], 200);
    }

    public function destroy($size_product_id)
    {
        $userId = auth()->id();
        $cartItem = Cart::where('user_id', $userId)->where('size_product_id', $size_product_id)->first();
        if (!$cartItem) {
            return response()->json(['message' => 'Sản Phẩm không có để xóa'], 404);
        }
        $cartItem->delete();

        return response()->json(['message' => 'Sản Phẩm đã được xóa'], 200);
    }

    public function clearCart(Request $request)
    {
        dd([
            'session_id' => $request->session()->getId(),
            'session_data' => session()->all(),
        ]);
    }

    public function addToCartq(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'size_product_id' => 'required|exists:size_products,size_product_id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            Log::info('Thêm vào giỏ hàng thất bại (Validation):', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Log request nhận được
        Log::info('Request thêm vào giỏ hàng:', $request->all());

        // 2. Lấy thông tin sản phẩm từ bảng size_products
        Log::info('Tìm SizeProduct với ID:', ['size_product_id' => $request->size_product_id]);
        $sizeProduct = Size_product::with('product')->find($request->size_product_id);

        if (!$sizeProduct) {
            Log::warning('Không tìm thấy SizeProduct với ID:', ['size_product_id' => $request->size_product_id]);
            return response()->json(['error' => 'Product not found'], 404);
        }
        Log::info('Thông tin SizeProduct tìm thấy:', $sizeProduct->toArray());

        $product = Product::find($sizeProduct->product_id);
        Log::info('Thông tin Product liên kết:', $product ? $product->toArray() : 'Không tìm thấy Product');

        // 3. Kiểm tra số lượng hàng còn lại
        if ($sizeProduct->stock < $request->quantity) {
            Log::warning('Không đủ số lượng tồn kho:', [
                'size_product_id' => $request->size_product_id,
                'stock' => $sizeProduct->stock,
                'requested_quantity' => $request->quantity,
            ]);
            return response()->json(['error' => 'Not enough stock'], 400);
        }

        $user_id = Auth::id();
        $session_id = Session::getId();
        Log::info('Thông tin User/Session:', ['user_id' => $user_id, 'session_id' => $session_id]);

        Log::info('Tìm Cart item hiện có:', [
            'user_id' => $user_id,
            'size_product_id' => $request->size_product_id,
        ]);
        $cart = Cart::where('user_id', $user_id)
            ->where('size_product_id', $request->size_product_id)
            ->first();
        Log::info('Cart item hiện có:', $cart ? $cart->toArray() : 'Không có Cart item');

        $quantity = ($cart ? $cart->quantity : 0) + $request->quantity;
        Log::info('Số lượng mới trong giỏ hàng:', ['quantity' => $quantity]);

        // 4. Thêm hoặc cập nhật giỏ hàng
        Log::info('Thêm hoặc cập nhật Cart:', [
            'user_id' => $user_id,
            'session_id' => $user_id ? null : $session_id,
            'size_product_id' => $request->size_product_id,
            'quantity' => $quantity,
            'sub_price' => $product->price * $quantity,
        ]);
        $cart = Cart::updateOrCreate(
            [
                'user_id' => $user_id,
                'session_id' => $user_id ? null : $session_id,
                'size_product_id' => $request->size_product_id,
            ],
            [
                'quantity' => $quantity,
                'sub_price' => $product->price * $quantity,
            ]
        );
        Log::info('Cart sau khi thêm/cập nhật:', $cart->toArray());

        // 5. Trừ số lượng tồn kho trong bảng size_products
        Log::info('Cập nhật tồn kho SizeProduct:', [
            'size_product_id' => $request->size_product_id,
            'old_stock' => $sizeProduct->stock,
            'quantity_to_subtract' => $request->quantity,
            'new_stock' => $sizeProduct->stock - $request->quantity,
        ]);
        $sizeProduct->update([
            'stock' => $sizeProduct->stock - $request->quantity,
        ]);
        Log::info('SizeProduct sau khi cập nhật tồn kho:', $sizeProduct->toArray());

        // 6. Trả về response API thành công
        Log::info('Trả về response thành công:', ['message' => 'Product added to cart successfully', 'cart' => $cart->toArray()]);
        return response()->json(['message' => 'Product added to cart successfully', 'cart' => $cart], 200);
    }

}
?>