<?php

namespace App\Http\Controllers\Staff\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class AuthController extends Controller
{
    /**
     *  Login Function
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $errorString = implode("\n", $validator->errors()->all());


            return response()->json([
                'message' => $errorString
            ], 403);
        }

        $user = Staff::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Invalid email account'
            ], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Credentials do not match'
            ], 403);
        }

        return response()->json([
            'token' => $user->createToken('User token', ['user'])->plainTextToken,
            'user' => $user,
        ], 200);
    }

    /**
     * 
     *  Reset Password Function
     * 
     */
    public function resetPass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
        ]);

        if ($validator->fails()) {
            $errorString = implode("\n", $validator->errors()->all());

            return response()->json([
                'message' => $errorString
            ], 403);
        }

        $user = Staff::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Invalid email account'
            ], 403);
        }


        $user->email_time = Carbon::now();
        $user->verification_code = mt_rand(1000, 9999);
        $user->save();

        $data = [
            'title' => 'One Time Password',
            'message' => "Your Verification Code Is: " . $user->verification_code,
        ];

        // Mail::to($user->email)->send(new SampleMail($data));

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ], 200);
    }

    /**
     * 
     *  Resend OTP Function
     * 
     */
    public function resendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
        ]);

        if ($validator->fails()) {
            $errorString = implode("\n", $validator->errors()->all());


            return response()->json([
                'message' => $errorString
            ], 403);
        }

        $user = Staff::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Invalid email account'
            ], 403);
        }

        if (Carbon::parse($user->email_time)->addMinutes(1) > Carbon::now()) {
            $delayInSeconds = Carbon::now()->diffInSeconds(Carbon::parse($user->email_time)->addMinutes(1));
            $delay = intval($delayInSeconds);

            return response()->json([
                'message' => 'You can resend Verification Code after ' . $delay . ' seconds'
            ], 403);
        } else {
            $user->email_time = Carbon::now();
            $user->verification_code = mt_rand(1000, 9999);
            $user->save();

            $data = [
                'title' => 'One Time Password',
                'message' => "Your Verification Code Is: " . $user->verification_code,
            ];

            // Mail::to($user->email)->send(new SampleMail($data));

            return response()->json([
                'message' => 'Verification code has been sent to your email successfully',
                'data' => $data
            ], 200);
        }
    }

    /**
     * 
     *  Verify OTP Function
     * 
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'string',],
        ]);

        if ($validator->fails()) {
            $errorString = implode("\n", $validator->errors()->all());


            return response()->json([
                'message' => $errorString
            ], 403);
        }

        $user = Staff::where('email', $request->email)->first();

        if (Carbon::now() > Carbon::parse($user->email_time)->addMinutes(20)) {
            return response()->json([
                'message' => 'OTP has expired, please resend OTP and try again'
            ], 403);
        }

        if (!$user) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Invalid email account'
            ], 403);
        }

        if ($user->verification_code == $request->otp) {
            $user->email_verified_at = Carbon::now();
            $user->save();

            $user->tokens()->delete();

            return response()->json([
                'message' => 'Verification successful',
                'token' => $user->createToken('User token', ['user'])->plainTextToken,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 403);
        }
    }

    /**
     * 
     *  Set Password Function
     * 
     */
    public function setPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'min:8',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x]).*$/',
                'confirmed'
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'New password must contain at least one uppercase letter, one lowercase letter, a number and must be at least 8 characters'
            ], 403);
        }

        $user = Auth::user();

        if (Carbon::now() > Carbon::parse($user->email_time)->addMinutes(20)) {

            return response()->json([
                'message' => 'OTP has expired, please resend OTP and try again'
            ], 403);
        }

        if ($user->email_verified_at) {
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'message' => 'Password set successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Complete KYC'
            ], 403);
        }
    }

    /**
     * 
     *  Update Password Function
     * 
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => ['required', 'string',],
            'password' => [
                'required',
                'min:8',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x]).*$/',
                'confirmed'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'New password must contain at least one uppercase letter, one lowercase letter, a number and must be at least 8 characters'
            ], 403);
        }

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => 'Request failed',
                'message' => 'Incorrect old Password'
            ], 403);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }

    /**
     * 
     *  Logout Function
     * 
     */
    public function logout()
    {
        $user = Auth::user();

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
