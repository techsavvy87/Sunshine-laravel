<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Models\VerifyEmail;
use App\Models\VerifyPassword;
use App\Models\RoleUser;
use App\Models\Service;
use App\Mail\VerifyRegister;
use App\Mail\VerifyResetPassword;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => "This account no longer exists. Please contact support.",
                'result' => NULL
            ], 200);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'The provided credentials are incorrect.',
                'results' => NULL
            ], 200);
        }

        if (empty($user->email_verified_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Email has not been verified yet',
                'results' => NULL
            ], 200);
        }

        // delete the record of VerifyEmail, VerifyPassword
        $verifyEmail = VerifyEmail::where('user_id', $user->id)->delete();
        $verifyPassword = VerifyPassword::where('user_id', $user->id)->delete();

        $token = $user->createToken('Personal Access Token')->plainTextToken;

        // services for menu configuration
        $services = Service::where('status', 'active')
                    ->where('level', 'primary')
                    // ->whereHas('category', function ($query) {
                    //     $query->whereRaw('LOWER(name) not like ?', ['%package%']);
                    // })
                    ->get();

        $result = [
            'user'  => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'services'     => $services
        ];

        return response()->json([
            'status' => true,
            'message' => 'Login Successfully',
            'result' => $result
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logout Successfully',
            'result' => NULL
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'firstName' => 'required_without:first_name',
            'first_name' => 'required_without:firstName',
            'lastName' => 'required_without:last_name',
            'last_name' => 'required_without:lastName',
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        $firstName = trim((string) $request->input('firstName', $request->input('first_name', '')));
        $lastName = trim((string) $request->input('lastName', $request->input('last_name', '')));
        $email = $request->email;
        $password = $request->password;
        $phoneNumber1 = trim((string) $request->input('phoneNumber1', $request->input('phone_number_1', '')));

        $role = Role::whereRaw('LOWER(title) = ?', ['customer'])->first();
        if (!isset($role)) {
            return response()->json([
                'status' => false,
                'message' => 'Customer signup is not allowed at this time.',
                'results' => NULL
            ], 200);
        }

        $existUser = User::where('email', $email)->first();
        if (!empty($existUser)) {
            return response()->json([
                'status' => false,
                'message' => 'This email already exists',
                'results' => NULL
            ], 200);
        }

        DB::transaction(function () use ($firstName, $lastName, $email, $password, $phoneNumber1, $role, &$user, &$token, &$number) {
            $user = new User;
            $user->name = trim($firstName . ' ' . $lastName);
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->save();

            $profile = new Profile;
            $profile->user_id = $user->id;
            $profile->first_name = $firstName;
            $profile->last_name = $lastName;
            $profile->phone_number_1 = $phoneNumber1;
            $profile->save();

            $roleUser = new RoleUser;
            $roleUser->user_id = $user->id;
            $roleUser->role_id = $role->id;
            $roleUser->save();

            $token = \Illuminate\Support\Str::random(40);
            $number = mt_rand(100000, 999999);
            $verifyEmail = new VerifyEmail;
            $verifyEmail->user_id = $user->id;
            $verifyEmail->token = $token;
            $verifyEmail->number = $number;
            $verifyEmail->save();
        });

        if (app()->environment('local')) {
            $baseUrl = 'http://localhost:5173';
        } else {
            $baseUrl = 'https://app.petmanage.com';
        }
        $mailData = [
            'verification_link' => $baseUrl . "/verify-account?token=$token",
            'verification_number' => $number
        ];
        Mail::to($email)->send(new VerifyRegister($mailData));

        return response()->json([
            'status' => true,
            'message' => 'The verification link has been sent to your email address.',
            'result' => ['userId' => $user->id ]
        ], 200);
    }

    public function verifyRegister(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->token;
        if (empty($token)) {
            return response()->json([
                'status' => false,
                'message' => 'The verification link is invalid.',
                'results' => NULL
            ], 200);
        }

        $verifyEmail = VerifyEmail::where('token', $token)->first();
        if (!isset($verifyEmail))
        {
            return response()->json([
                'status' => false,
                'message' => 'The verification link is invalid.',
                'results' => NULL
            ], 200);
        }

        $userId = $verifyEmail->user_id;
        $user = User::find($userId);
        if (!isset($user)) {
            return response()->json([
                'status' => false,
                'message' => "The user's account has not been created.",
                'results' => NULL
            ], 200);
        }
        $user->email_verified_at = Carbon::now();
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Your email address is verified and PawPrints account is activated now.',
            'results' => NULL
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!isset($user)) {
            return response()->json([
                'status' => false,
                'message' => "We can't find your account with this email.",
                'result' => null
            ], 200);
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
            'reset_link' => $baseUrl . "/reset-password?token=$token",
            'reset_number' => $number
        ];
        Mail::to($email)->send(new VerifyResetPassword($mailData));

        return response()->json([
            'status' => true,
            'message' => 'The password reset link has been sent to your email address.',
            'result' => null
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'new_password' => 'required|string',
        ]);

        $token = $request->token;
        if (empty($token)) {
            return response()->json([
                'status' => false,
                'message' => 'The reset password link is invalid.',
                'result' => null
            ], 200);
        }

        $verifyPassword = VerifyPassword::where('token', $token)->first();
        if (!isset($verifyPassword)) {
            return response()->json([
                'status' => false,
                'message' => 'The reset password link is invalid or expired.',
                'result' => null
            ], 200);
        }

        $userId = $verifyPassword->user_id;
        $user = User::find($userId);
        if (!isset($user)) {
            return response()->json([
                'status' => false,
                'message' => "The user's account doesn't exist.",
                'result' => null
            ], 200);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Your password has been reset successfully.',
            'result' => null
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string',
        ]);

        $currentPassword = $request->currentPassword;
        $newPassword = $request->newPassword;

        $user = User::find(Auth::id());
        if (Hash::check($currentPassword, $user->password))
        {
            $newPass = Hash::make($newPassword);
            $user->password = $newPass;
            $user->save();
        }
        else
        {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect.',
                'result' => NULL
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'User password is updated successfully.',
            'result' => NULL
        ], 200);

    }
}
