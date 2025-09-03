<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        return response()->json(['data' => [], 'message' => 'Jobs retrieved successfully']);
    }

    public function show(string $slug)
    {
        return response()->json(['data' => null, 'message' => 'Job not found'], 404);
    }
}