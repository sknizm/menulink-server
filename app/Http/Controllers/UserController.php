<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
  public function getUser(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated',
        ], 401);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $user->id,
            'email' => $user->email,
        ]
    ]);
}
}
