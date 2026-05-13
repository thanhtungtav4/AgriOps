<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->authError(
                'AUTH_INVALID_CREDENTIALS',
                'Invalid email or password.'
            );
        }

        $token = $user->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        $user->update(['last_login_at' => now()]);

        $user->load('farm');

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(7)->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'farm_id' => $user->farm_id,
                'farm' => $user->farm ? [
                    'id' => $user->farm->id,
                    'name' => $user->farm->name,
                    'code' => $user->farm->code,
                ] : null,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if (!$token) {
            return $this->success(['message' => 'No active token to revoke.']);
        }

        $token->delete();

        return $this->success(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('farm');

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_admin' => $user->isAdmin(),
            'can_approve' => $user->canApprove(),
            'farm_id' => $user->farm_id,
            'farm' => $user->farm ? [
                'id' => $user->farm->id,
                'name' => $user->farm->name,
                'code' => $user->farm->code,
            ] : null,
            'scope' => $user->isAdmin() ? 'global' : 'farm',
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ]);
    }
}
