<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassswordMail;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 400,
                'message'     => 'Bad Request'
            ]);
        }
        $user = new Admin();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status_code' => 200,
            'message'     => 'User created successFully'
        ]);

    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $admin = Admin::where('email', Arr::get($credentials, 'email'))->first();

        if (!$admin || ! Hash::check(Arr::get($credentials, 'password'), $admin->password)) {
            throw new \Exception('credentials wrong');
        }

        $admin->tokens()->delete();
        return response()->json([
            'status_code' => 200,
            'token' => $admin->createToken('adminToken')->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status_code' => 200,
            'message' => 'Token deleted successfully'
        ]);
    }

    public function memberInfo()
    {
        return response()->json([
            'status_code' => '200',
            'auth' => Auth::guard('admin')->user()
        ]);
    }

    public function forgotPassword($email)
    {
        if(Admin::where('email', $email)->first())
        {
            $admin = Admin::where('email', $email)->first();
            Mail::to($email)->send(new ResetPassswordMail($admin));

            return response()->json([
                'status_code' => 200,
                'message' => 'Please Check Your Email!!!'
            ]);
        }else{
            return response()->json([
                'status_code' => 400,
                'message' => 'Wrong Email!!!'
            ]);
        }
    }
}
