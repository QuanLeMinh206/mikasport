<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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



   public function login(Request $request)
   {
       try {
           // Kiểm tra dữ liệu đầu vào
           $request->validate([
               'email' => 'required|email',
               'password' => 'required|min:6',
           ]);

           // Kiểm tra thông tin đăng nhập
           $user = User::where('email', $request->email)->first();

           if (!$user || !Hash::check($request->password, $user->password)) {
               return response()->json(['error' => 'Email hoặc mật khẩu không đúng'], 401);
           }

           // Kiểm tra người dùng đã đăng nhập trước đó
    //     if (method_exists($user, 'tokens') && $user->tokens()->count() > 0) {
      //   return response()->json(['error' => 'Người dùng đã đăng nhập trước đó'], 401);
       //  }

            //Tạo token đăng nhập
         $token = $user->createToken('auth_token')->plainTextToken;

           // Kiểm tra quyền truy cập cho người dùng
    //       if ($user->role != 1) { // Nếu role là số (tinyint)
      //         return response()->json(['error' => 'Người dùng không có quyền truy cập'], 401);
       //   }

           return response()->json([
               'message' => 'Đăng nhập thành công!',
               'user' => $user,
               'token' => $token,
           ], 200);
       } catch (\Exception $e) {
           return response()->json([
               'error' => 'Lỗi đăng nhập',
               'message' => $e->getMessage(), // In lỗi cụ thể để debug
           ], 500);
       }
   }
   public function register(Request $request)
   {
       try {
           $validator = Validator::make($request->all(), [
               'full_name' => 'nullable|string|max:255',
               'user_name' => 'required|string|max:255|unique:users',
               'email' => 'required|string|email|max:255|unique:users',
               'password' => 'required|string|min:6|confirmed'
           ]);

           if ($validator->fails()) {
               return response()->json(['errors' => $validator->errors()], 422);
           }

           $user = User::create([
               'full_name' => $request->full_name,
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
   public function update(Request $request, $id)
   {
       try {
           // Lấy thông tin người dùng hiện tại từ token
           $currentUser = auth()->user();

           // Kiểm tra nếu không đăng nhập hoặc không lấy được user
           if (!$currentUser) {
               return response()->json(['message' => 'Chưa đăng nhập'], 401);
           }

           // Ép kiểu `$id` sang số nguyên để đảm bảo so sánh chính xác
           $userId = (int) $id;

           // Kiểm tra nếu người dùng hiện tại không phải là người cần cập nhật
           if ($currentUser->user_id !== $userId) {
               return response()->json(['message' => 'Không thể cập nhật thông tin người dùng khác'], 403);
           }

           // Lấy thông tin người dùng cần cập nhật
           $user = User::where('user_id', $userId)->first();

           // Kiểm tra nếu người dùng không tồn tại
           if (!$user) {
               return response()->json(['message' => 'Người dùng không tồn tại'], 404);
           }

           // Kiểm tra nếu user là admin thì không cho phép chỉnh sửa
           if ((int) $user->role === 1) {
               return response()->json(['message' => 'Không thể chỉnh sửa Admin'], 403);
           }

           // Validate dữ liệu đầu vào
           $validatedData = $request->validate([
               'full_name' => 'nullable|string|max:255',
               'email' => 'sometimes|required|email|unique:users,email,' . $userId . ',user_id',
               'phone' => 'nullable|string|max:15',
               'address' => 'nullable|string|max:255',
           ]);

           // Cập nhật thông tin người dùng
           $user->update($validatedData);

           return response()->json(['message' => 'Cập nhật thành công', 'user' => $user]);

       } catch (\Exception $e) {
           return response()->json(['error' => $e->getMessage()], 500);
       }
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




}