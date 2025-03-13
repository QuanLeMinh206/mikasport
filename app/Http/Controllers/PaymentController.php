<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function createPayment(Request $request)
    {
        // 1. Lấy thông tin từ giỏ hàng (ví dụ: tổng tiền)
        $cart = auth()->user()->cart;
        $totalAmount = $cart->sum('sub_price') * 100;  // VNPAY yêu cầu nhân 100

       // 2. Lấy cấu hình VNPAY từ config
$vnp_TmnCode = config('vnpay.vnp_TmnCode');
$vnp_HashSecret = config('vnpay.vnp_HashSecret');
$vnp_Url = config('vnpay.vnp_Url');
$vnp_Returnurl = config('vnpay.vnp_ReturnUrl');

        // 3. Tạo mã giao dịch (CodeOrder)
        $vnp_TxnRef = now()->format('YmdHis') . rand(1000, 9999);

        // 4. Chuẩn bị dữ liệu gửi sang VNPAY
        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $totalAmount,
            "vnp_CurrCode" => "VND",
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_OrderInfo" => "Thanh toán đơn hàng #$vnp_TxnRef",
            "vnp_OrderType" => "other",
            "vnp_Locale" => "vn",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_IpAddr" => $request->ip(),
            "vnp_CreateDate" => now()->format('YmdHis'),
        ];

        // 5. Tạo URL thanh toán
        ksort($inputData);
        $query = http_build_query($inputData);
        $vnpSecureHash = hash_hmac('sha512', urldecode($query), $vnp_HashSecret);
        $vnp_Url .= '?' . $query . '&vnp_SecureHash=' . $vnpSecureHash;

        // 6. Chuyển hướng sang VNPAY
        return redirect()->away($vnp_Url);
    }

    public function vnpayReturn(Request $request)
    {
        // 7. Xử lý kết quả trả về từ VNPAY
        if ($request->vnp_ResponseCode == '00') {
            // Thanh toán thành công
            return response()->json(['message' => 'Thanh toán thành công!']);
        } else {
            // Thanh toán thất bại
            return response()->json(['message' => 'Thanh toán thất bại!']);
        }
    }
}