<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;
use App\Models\Session;

class AuthController extends Controller
{

public function signup(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        // Create User
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create token for user
        $token = $user->createToken($request->email);

        // Return response with token
        return response()->json([
            'success' => true,
            'message' => 'SignUp successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
                'token' => $token->plainTextToken
            ]
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Registration error: ' . $e->getMessage());  // log the error message

        return response()->json([
            'success' => false,
            'message' => 'Registration failed. Please try again later.',
            'error' => $e->getMessage(),  // return the exception message
        ], 500);
    }
}


    public function signin(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'error' => 'The provided credentials are incorrect.',
                ], 401);
            }

            $token = $user->createToken($user->email)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'SignIn successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                    'token' => $token
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('SignIn error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'SignIn failed. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function signout(Request $request)
    {
        try {
            // Revoke all tokens for the authenticated user
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('SignOut error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
