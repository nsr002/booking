<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LineService;
use App\Models\User;

class LiffAuthController extends Controller
{
    protected $lineService;

    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    /**
     * Authenticate user via LINE LIFF.
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        $accessToken = $request->access_token;

        // Verify token with LINE
        $tokenInfo = $this->lineService->verifyAccessToken($accessToken);

        if (!$tokenInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid access token',
            ], 401);
        }

        // Get user profile from LINE
        $profile = $this->lineService->getUserProfile($accessToken);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user profile',
            ], 401);
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['line_user_id' => $profile['userId']],
            [
                'name' => $profile['displayName'],
                'avatar' => $profile['pictureUrl'] ?? null,
                'role' => 'user',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful',
            'data' => [
                'user' => $user,
                'access_token' => $accessToken,
            ],
        ]);
    }

    /**
     * Get authenticated user info.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }
}
