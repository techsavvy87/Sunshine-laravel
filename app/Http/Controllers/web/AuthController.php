<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\VerifyPassword;
use App\Mail\VerifyResetPassword;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function handleLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $rememberMe = $request->remember_me;
        $remember = $rememberMe === 'on' ? TRUE : FALSE;

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (Auth::user()->roles->contains('title', 'customer'))
                return back()->with([
                    'status' => 'fail',
                    'message' => 'Required administrator access.'
                ]);

            return redirect()->route('dashboard');
        }

        return back()->with([
            'status' => 'fail',
            'message' => 'Incorrect credentials.'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function forgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function verifyForgotPassword(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!isset($user))
        {
            return back()->with([
                'status' => 'fail',
                'message' => "Invalid account email."
            ]);
        }

        $token = \Illuminate\Support\Str::random(40);
        $number = mt_rand(100000, 999999);
        $verifyPassword = new VerifyPassword;
        $verifyPassword->user_id = $user->id;
        $verifyPassword->token = $token;
        $verifyPassword->number = $number;
        $verifyPassword->save();

        if (app()->environment('local')) {
            $baseUrl = 'http://localhost:5173';
        } else {
            $baseUrl = 'https://app.petmanage.com';
        }
        $mailData = [
            'reset_link' => $request->schemeAndHttpHost() . "/reset-password/verify?token=$token",
            'reset_number' => $number
        ];
        Mail::to($email)->send(new VerifyResetPassword($mailData));

        return back()->with([
            'status' => 'success',
            'message' => 'Reset link sent to your email.'
        ]);
    }

    public function verifyResetPassword(Request $request)
    {
        $token = $request->query('token');
        if (!isset($token))
        {
            return redirect()->route('forgot-password')->with([
                'status' => 'fail',
                'message' => 'Reset password verification failed.'
            ]);
        }

        $verifyPassword = VerifyPassword::where('token', $token)->first();
        if (!isset($verifyPassword))
        {
            return redirect()->route('forgot-password')->with([
                'status' => 'fail',
                'message' => 'Invalid or expired link.'
            ]);
        }

        return view('auth.reset_password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'new_password' => ['required'],
            'confirm_password' => ['required'],
            'token' => ['required'],
        ]);

        $newPassword = $request->new_password;
        $confirmPassword = $request->confirm_password;
        $token = $request->token;

        if ($newPassword !== $confirmPassword)
        {
            return back()->with([
                'status' => 'fail',
                'message' => 'Passwords do not match.'
            ]);
        }

        $verifyPassword = VerifyPassword::where('token', $token)->first();
        $userId = $verifyPassword->user_id;

        $user = User::find($userId);
        $newPass = Hash::make($newPassword);
        $user->password = $newPass;
        $user->save();

        $verifyPassword->delete();

        return redirect()->route('login')->with([
            'message' => 'Password reset successful.',
            'status' => 'success'
        ]);
    }
}
