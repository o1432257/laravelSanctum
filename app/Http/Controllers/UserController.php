<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
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
        $user = new User();
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

        $user = User::where('email', Arr::get($credentials, 'email'))     ->first();

        if (!$user || ! Hash::check(Arr::get($credentials, 'password'), $user->password)) {
            throw new \Exception('credentials wrong');
        }

        $user->tokens()->delete();

        return response()->json([
            'status_code' => 200,
            'token' => $user->createToken('userToken')->plainTextToken
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
            'auth' => Auth::user()
        ]);
    }
}
