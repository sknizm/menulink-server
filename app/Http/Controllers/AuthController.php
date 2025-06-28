<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;
use App\Models\Session;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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


     /**
     * Generate and store an OTP for a given email, and send it directly via email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $userEmail = $validated['email'];

            $otpCode = (string)mt_rand(100000, 999999);
            $expiresAt = Carbon::now()->addMinutes(5);

            // Delete any existing unexpired OTPs for this email to prevent clutter
            Otp::where('email', $userEmail)
                ->where('expires_at', '>', Carbon::now())
                ->delete();

            // Create new OTP record
            $otp = Otp::create([
                'id' => Str::uuid(),
                'email' => $userEmail,
                'otp_code' => $otpCode,
                'expires_at' => $expiresAt,
            ]);

            // --- Directly send the OTP email without Mailable or Blade ---
            $subject = 'Your One-Time Password (OTP) for MenuLink';
            $emailBody = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>{$subject}</title>
                    <style>
                        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
                        .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eeeeee; }
                        .header h1 { color: #333333; font-size: 24px; margin: 0; }
                        .content { padding: 20px 0; text-align: center; }
                        .content p { font-size: 16px; line-height: 1.6; color: #555555; margin-bottom: 15px; }
                        .otp-code { display: inline-block; background-color: #f0f8ff; color: #007bff; font-size: 28px; font-weight: bold; padding: 15px 30px; border-radius: 5px; letter-spacing: 3px; margin: 20px 0; border: 1px dashed #cccccc; }
                        .footer { text-align: center; padding-top: 20px; border-top: 1px solid #eeeeee; font-size: 12px; color: #888888; }
                        .warning { color: #dc3545; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>MenuLink</h1>
                        </div>
                        <div class='content'>
                            <p>Hello,</p>
                            <p>You have requested a One-Time Password (OTP) for your account. Please use the following code to proceed:</p>
                            <div class='otp-code'>{$otpCode}</div>
                            <p>This OTP is valid for 5 minutes. Please do not share this code with anyone.</p>
                            <p class='warning'>If you did not request this, please ignore this email.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; " . date('Y') . " MenuLink. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Correct way to send raw HTML email in older Laravel versions (and still works in newer)
            Mail::send([], [], function ($message) use ($userEmail, $subject, $emailBody) {
                $message->to($userEmail)
                        ->subject($subject)
                        ->html($emailBody); // The html() method is available on the $message object
            });
            // --- End direct email sending ---

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email. Please check your inbox.',
                'expires_at' => $expiresAt->toDateTimeString(),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("OTP generation failed for {$request->email}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate OTP. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Verify the provided OTP for a given email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp_code' => 'required|string|size:6', // Assuming 6-digit OTP
            ]);

            $email = $validated['email'];
            $otpCode = $validated['otp_code'];

            // Find the most recent unexpired OTP for the email
            $otp = Otp::where('email', $email)
                      ->where('otp_code', $otpCode)
                      ->where('expires_at', '>', Carbon::now())
                      ->latest() // Get the most recently created OTP
                      ->first();

            if (!$otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP.',
                ], 400); // Bad Request
            }

            // Optional: Invalidate the OTP after successful verification to prevent reuse
            // You might choose to do this only AFTER password reset is complete.
            // $otp->delete(); // Or $otp->update(['expires_at' => Carbon::now()]);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("OTP verification failed for {$request->email}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset the user's password after successful OTP verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp_code' => 'required|string|size:6', // Re-verify OTP for security
                'password' => 'required|string|min:8|confirmed', // 'confirmed' checks against password_confirmation
            ]);

            $email = $validated['email'];
            $otpCode = $validated['otp_code'];
            $newPassword = $validated['password'];

            // Re-verify the OTP before allowing password change
            $otp = Otp::where('email', $email)
                      ->where('otp_code', $otpCode)
                      ->where('expires_at', '>', Carbon::now())
                      ->latest()
                      ->first();

            if (!$otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP for password reset.',
                ], 400);
            }

            // Find the user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                // This should theoretically not happen if 'exists:users,email' validation passes
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Update the user's password
            $user->password = Hash::make($newPassword);
            $user->save();

            // Invalidate the used OTP to prevent replay attacks
            $otp->delete(); // Mark as used by deleting or updating its status/expiration

            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset successfully.',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Password reset failed for {$request->email}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
