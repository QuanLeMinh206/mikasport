<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Password; // ✅ Thêm dòng này
class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    //    try {
      //      if (auth('admin')->check()) {
        //        return response()->json(User::all(), 200);
         //   }
           // return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        //} catch (\InvalidArgumentException $e) {
        //    return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        //}
        return response()->json(User::all(), 200);
    }

    // AuthController.php


public function login(Request $request)
{
    // Validate dữ liệu đầu vào
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Kiểm tra thông tin người dùng
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // Nếu đăng nhập thành công, tạo token mới
    $token = $user->createToken('YourAppName')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'data' => [
            'user' => $user,
            'token' => $token
        ]
    ]);
}





   public function register(Request $request)
   {
       try {
           $validator = Validator::make($request->all(), [
               'user_name' => 'required|string|max:255|unique:users',
               'email' => 'required|string|email|max:255|unique:users',
               'password' => 'required|string|min:6|confirmed'
           ]);

           if ($validator->fails()) {
               return response()->json(['errors' => $validator->errors()], 422);
           }

           $user = User::create([

               'user_name' => $request->user_name,
               'email' => $request->email,

               'password' => Hash::make($request->password),
           ]);

           $token = $user->createToken('auth_token')->plainTextToken;

           return response()->json([
               'message' => 'Đăng ký thành công',
               'user' => $user,
               'token' => $token
           ], 201);
       } catch (\Exception $e) {
           return response()->json(['message' => 'Lỗi: ' . $e->getMessage()], 500);
       }
   }

   public function show($id)
   {
       $user = User::find($id);

       if (!$user) {
           return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
       }

       return response()->json($user);
   }


   public function updateProfileApi(Request $request)
   {
       $auth = auth()->user();

       if (!$auth) {
           return response()->json(['message' => 'Chưa xác thực'], 401);
       }

       // Validate cơ bản
       $validator = Validator::make($request->all(), [
           'full_name' => 'nullable|string|max:255',
           'user_name' => 'nullable|string|max:255|unique:users,user_name,' . $auth->user_id . ',user_id',
           'email' => 'nullable|email|max:255|unique:users,email,' . $auth->user_id . ',user_id',
           'phone' => 'nullable|string|max:20',
           'address' => 'nullable|string|max:255',

           // Không validate kiểu image ở đây để cho phép cả URL và file
           'img' => 'nullable',
       ]);

       if ($validator->fails()) {
           return response()->json(['errors' => $validator->errors()], 400);
       }

       $data = $request->only('full_name', 'user_name', 'email', 'phone', 'address');

       // Đảm bảo thư mục tồn tại
       if (!Storage::exists('public/avatars')) {
           Storage::makeDirectory('public/avatars');
       }

       // Xử lý avatar:
       if ($request->hasFile('img')) {
           // Nếu là file upload
           $file = $request->file('img');

           // Validate kiểu file ảnh
           $fileValidator = Validator::make(['img' => $file], [
               'img' => 'image|mimes:jpeg,png,jpg|max:2048',
           ]);

           if ($fileValidator->fails()) {
               return response()->json(['errors' => $fileValidator->errors()], 400);
           }

           // Xoá ảnh cũ
           if ($auth->img && Storage::exists('public/' . $auth->img)) {
               Storage::delete('public/' . $auth->img);
           }

           // Lưu ảnh
           $avatarPath = $file->store('avatars', 'public');
           $data['img'] = $avatarPath;
       } elseif ($request->filled('img') && filter_var($request->img, FILTER_VALIDATE_URL)) {
           // Nếu là URL hợp lệ → chỉ lưu URL vào DB
           $data['img'] = $request->img;
       }
    // dd($data);
       \Log::info('DATA:', $data);
       // Cập nhật user
       $check = $auth->update($data);

       if ($check) {
           return response()->json([
               'message' => 'Hồ sơ đã được cập nhật thành công!',
               'user' => $auth->fresh()
           ], 200);
       }

       return response()->json(['message' => 'Cập nhật hồ sơ thất bại, vui lòng thử lại.'], 500);
   }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $user->tokens()->delete(); // Xóa tất cả token của user
                session()->invalidate();
        session()->regenerateToken();
                return response()->json(['message' => 'Bạn đã đăng xuất thành công'], 200);
            }

            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
             $currentUser=auth()->user();
             if($currentUser->user_id !=$id)
             {
                return response()->json(['message'=>"Bạn ko thể xóa người dùng khác"]);

             }
             $currentUser->delete();
             return response()->json(['message' => 'Tài khoản đã được xóa thành công']);
    }

    public function getUser(Request $request)
    {
        return response()->json([
            'user'=>$request->user()
        ]);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Chúng tôi đã gửi email đặt lại mật khẩu!'])
            : response()->json(['message' => 'Email không tồn tại!'], 400);
    }



    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Mật khẩu đã được đặt lại!'])
            : response()->json(['message' => 'Token không hợp lệ!'], 400);
    }

    public function search(Request $request)
    {
        $name = $request->query('name'); // Get the 'name' parameter from URL
        return response()->json(['searching_for' => $name]);
    }



    public function changePassword(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Chưa xác thực'], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Kiểm tra xem mật khẩu hiện tại có đúng không
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng'], 401);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Mật khẩu đã được thay đổi thành công!'], 200);
    }
}
