<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Session;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    public function getSession(Request $request)
    {
        try {
            $token = $request->cookie(config('session.cookie'));

            if (!$token) {
                return response()->json([
                    'success' => true,
                    'data' => ['user' => null]
                ]);
            }

            $session = Session::with('user')
                ->where('session_token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$session || !$session->user) {
                return response()->json([
                    'success' => true,
                    'data' => ['user' => null]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $session->user->id,
                        'email' => $session->user->email,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Session check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => 'Unable to check session.'
            ], 500);
        }
    }
}
