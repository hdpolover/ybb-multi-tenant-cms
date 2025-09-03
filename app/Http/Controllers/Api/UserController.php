<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function posts()
    {
        return response()->json(['data' => [], 'message' => 'User posts retrieved successfully']);
    }
}