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
     return  response()->json(User::all(),200);
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
           if (method_exists($user, 'tokens') && $user->tokens()->count() > 0) {
               return response()->json(['error' => 'Người dùng đã đăng nhập trước đó'], 401);
           }

           // Tạo token đăng nhập
           $token = $user->createToken('auth_token')->plainTextToken;

           // Kiểm tra quyền truy cập cho người dùng
           if ($user->role != 1) { // Nếu role là số (tinyint)
               return response()->json(['error' => 'Người dùng không có quyền truy cập'], 401);
           }

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



    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }
}