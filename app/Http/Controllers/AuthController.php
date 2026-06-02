<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ]);
    }

    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user()->load('clinic');

        return response()->json([
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
            'clinic' => $user->clinic ? [
                'id' => $user->clinic->id,
                'name' => $user->clinic->name,
            ] : null,
            'examination_fee' => $user->examination_fee,
        ]);
    }
}
