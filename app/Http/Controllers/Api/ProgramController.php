<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        return response()->json(['data' => [], 'message' => 'Programs retrieved successfully']);
    }

    public function show(string $slug)
    {
        return response()->json(['data' => null, 'message' => 'Program not found'], 404);
    }
}