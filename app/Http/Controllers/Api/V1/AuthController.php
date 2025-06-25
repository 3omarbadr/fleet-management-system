<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\AuthResource;
use App\Http\Resources\Api\V1\AuthLoginResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => false,
            ]);

            $token = $user->createToken('API Token')->plainTextToken;

            return $this->createdResponse(
                new AuthLoginResource($user, $token),
                'User registered successfully.'
            );
        });
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->unauthorizedResponse('Invalid credentials.');
            }

            $token = $user->createToken('API Token')->plainTextToken;

            return $this->successResponse(
                new AuthLoginResource($user, $token),
                'Login successful.'
            );
        });
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logged out successfully.');
        });
    }

    /**
     * Get current user.
     */
    public function user(Request $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            return $this->successResponse(
                ['user' => new AuthResource($request->user())],
                'User retrieved successfully.'
            );
        });
    }
} 