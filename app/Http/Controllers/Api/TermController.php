<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index()
    {
        return response()->json(['data' => [], 'message' => 'Terms retrieved successfully']);
    }

    public function byType(string $type)
    {
        return response()->json(['data' => [], 'message' => "Terms of type {$type} retrieved successfully"]);
    }
}