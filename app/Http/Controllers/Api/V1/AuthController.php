<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
      $validateData = $request->validate([
        // 'email' => 'required|string|email|unique:users',
        'password' => 'required|string',
        'rol' => 'required',
        'personId' => 'required',
      ]);

      $user = User::create([
        // 'email' => $validateData['email'],
        'password' => Hash::make($validateData['password']),
        'rol' => $validateData['rol'],
        'person_id' => $validateData['personId'],
      ]);

      $token = $user->createToken('auth_token')->plainTextToken;

      return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer'
      ]);
    }

    public function login(Request $request) {
      if (!Auth::attempt($request->only('id', 'password'))) {
        return response()->json([
          'message' => 'Invalid login details'
        ], 403);
      }

      $user = User::where('id', '=', $request['id'])->with('person')->firstOrFail();

      $token = $user->createToken('auth_token')->plainTextToken;

      return response()->json([
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer'
      ]);
    }

    public function logout() {
      auth()->user()->tokens()->delete();
      return response()->json([
        'message' => 'You have been succefully logged out'
      ], 200);
    }
}
