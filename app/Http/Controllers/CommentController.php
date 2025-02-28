<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Lấy tất cả comment của tất cả users
    public function index()
    {
        $comments = Comment::with('user', 'product')->get();

        return response()->json([
            'status' => 'success',
            'data' => $comments
        ]);
    }

}