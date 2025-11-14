<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function show($token)
    {
        return response()->json([
            'message' => 'Vista del formulario de restablecimiento (frontend debe manejarla)',
            'token' => $token
        ]);
    }
}
