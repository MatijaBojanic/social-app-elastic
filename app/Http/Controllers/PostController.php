<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function search(Request $request)
    {
//        $response = Post::search($request->value);
        $response = true;
        return response()->json($response);
    }
}
