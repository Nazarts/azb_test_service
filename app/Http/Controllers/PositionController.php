<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index() {
        return response()->json([
            'success' => true,
            'positions' => Position::all()
        ]);
    }
}
