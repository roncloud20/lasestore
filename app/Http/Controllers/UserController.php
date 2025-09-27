<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|same:confirm_password|string|min:8',
            'confirm_password' => 'required|string|same:password',
            'phone_number' => 'required|string|min:11|max:14|unique:users,phone_number',
            'role' => 'required|in:user,admin,vendor'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Registration Failed',
            ], 400);
        }
        try {
            $verification_code = rand(100000, 999999);
            $user = new User;
            $user->firstname = $request->input('firstname');
            $user->lastname = $request->input('lastname');
            $user->email = $request->input('email');
            $user->password = $request->input('password');
            $user->phone_number = $request->input('phone_number');
            $user->verification_code = $verification_code;
            $user->role = $request->input('role');
            $user->save();

            Mail::to($user->email)->send(new \App\Mail\UserEmailVerification($user));

            return response()->json([
                'user' => $user,
                'message' => "Register Successfully: Mail Sent",
            ], 201);
            // return "Register Here";
        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
                'message' => 'Server Error'
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user || $user->verification_code !== $request->code) {
                return response()->json([
                    'message' => 'Code is invalid',
                ], 400);
            }
            if ($user->email && $user->email === $request->email) {
                User::where('email', $user->email)->update([
                    'email_verified_at' => now(),
                    'verification_code' => null,
                ]);
            }
            $user->save();

            return response()->json([
                'message' => 'Verified Successfully.',
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
            ], 500);
        }
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->email_verified_at === null) {
                return response()->json([
                    'message' => "Please verify account before login"
                ], 400);
            }
            // $token = $user->createToken('lase-token')->plainTextToken;
            $token = $user->createToken('lase-token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'message' => "Login Successfully",
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid Login Credentials'
        ], 400);
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'The email does not exist in our records.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }
        try {
            $user = User::where('email', $request->email)->first();
            // if(!$user) {
            //     return response()->json([
            //         'message' => 'User not found',
            //     ], 404);
            // }
            Mail::send('emails.forget-password', [
                'user' => $user,
                'url' => env('FRONTEND_URL') . '/api/changepassword?email=' . $user->email,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Account Password');
            });

            return response()->json([
                'message' => "Please check your mail to reset password",
            ], 200);

        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error->getMessage(),
                'message' => 'Server Error'
            ], 500);
        }
    }

    public function getUser($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }
            return response()->json([
                'user' => $user,
            ], 200);

        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
            ], 500);
        }
    }

    public function getUsers()
    {
        $users = User::all();
        return $users;
    }

    public function adminUpdateUserRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:user,admin,vendor'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }
            $user->role = $request->input('role');
            $user->save();

        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
            ], 500);
        }

        return response()->json([
            'user' => $user,
            'message' => 'User role updated successfully',
        ], 200);
    }

    public function editUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:50',
            'lastname' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|string|email|unique:users,email',
            'password' => 'sometimes|required|same:confirm_password|string|min:8',
            'confirm_password' => 'sometimes|required|string|same:password',
            'phone_number' => 'sometimes|required|string|min:11|max:14|unique:users,phone_number,',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Update Failed',
            ], 400);
        }
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            if ($request->has('firstname')) {
                $user->firstname = $request->input('firstname');
            }
            if ($request->has('lastname')) {
                $user->lastname = $request->input('lastname');
            }
            if ($request->has('email')) {
                $user->email = $request->input('email');
                $user->email_verified_at = null; // Reset email verification
                $verification_code = rand(100000, 999999);
                $user->verification_code = $verification_code;
                Mail::to($user->email)->send(new \App\Mail\UserEmailVerification($user));
            }
            if ($request->has('password')) {
                $user->password = $request->input('password');
            }
            if ($request->has('phone_number')) {
                $user->phone_number = $request->input('phone_number');
            }

            $user->save();

            return response()->json([
                'user' => $user,
                'message' => "User updated successfully",
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
                'message' => 'Server Error'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
