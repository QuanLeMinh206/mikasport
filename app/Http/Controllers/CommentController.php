<?php

namespace App\Http\Controllers; // Đặt controller vào namespace mặc định

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Lấy danh sách bình luận cho một sản phẩm (API).
     *
     * @param   int   $proId
     * @return \Illuminate\Http\JsonResponse
     */


    public function index($proId)
{
    $comments = Comment::where('product_id', $proId)
        ->with('user:user_id,full_name')  // Verify these fields
        ->orderBy('timestamp', 'desc')
        ->get();



    return response()->json([
        'status' => 'success',
        'data' => $comments,
    ]);
}
    /**
     * Tạo một bình luận mới cho một sản phẩm (API).
     *
     * @param   \Illuminate\Http\Request   $request
     * @param   int   $proId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $proId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:jpeg,png,mp4,webm,ogg|max:20480', // Tối đa 20MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $commentData = [
            'product_id' => $proId,
            'user_id' => Auth::id(),
            'message' => $request->input('message'),
            'rating' => $request->input('rating'),
            'title' => $request->input('title'),
            'timestamp' => now(), // Thêm timestamp tự động ở backend
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('comments', $filename, 'public');
            $commentData['file_url'] = asset('storage/' . $path); // Tạo URL đầy đủ
        }

        $comment = Comment::create($commentData);

        return response()->json([
            'status' => 'success',
            'message' => 'Bình luận thành công!',
            'data' => $comment->load('user:user_id,full_name'), // Load thông tin user
        ], 201);
    }





    public function update(Request $request, Comment $comment)
    {
        if (Auth::id() !== $comment->user_id) {
            return response()->json(['error' => 'Bạn không có quyền sửa bình luận này.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'img' => 'nullable|file|mimes:jpeg,png|max:20480', // Đảm bảo tên trường là 'img'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $commentData = $request->only(['message', 'rating', 'title']);


        if ($request->hasFile('img')) { // Đảm bảo tên trường là 'img'
            $file = $request->file('img'); // Đảm bảo tên trường là 'img'
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('comments', $filename, 'public');
            $commentData['file_url'] = asset('storage/' . $path);
        }
        $comment->update($commentData);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật bình luận thành công!',
            'data' => $comment->load('user:user_id,full_name'),
        ]);
    }

    /**
     * Xoá một bình luận (API).
     *
     * @param   int   $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        if (Auth::id() !== $comment->user_id) {
            return response()->json(['error' => 'Bạn không có quyền xoá bình luận này!'], 403);
        }



        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xoá bình luận thành công!',
        ]);
    }
}
