<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;

class ProgramController extends Controller
{
    public function index() { return response()->json(['data' => [], 'message' => 'Programs retrieved']); }
    public function store() { return response()->json(['message' => 'Not implemented'], 501); }
    public function show($id) { return response()->json(['data' => null, 'message' => 'Program not found'], 404); }
    public function update($id) { return response()->json(['message' => 'Not implemented'], 501); }
    public function destroy($id) { return response()->json(['message' => 'Not implemented'], 501); }
}