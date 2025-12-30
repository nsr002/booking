<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LineService;
use App\Models\User;

class VerifyLiffToken
{
    protected $lineService;

    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json([
                'error' => 'Access token is required'
            ], 401);
        }

        // Verify token with LINE
        $tokenInfo = $this->lineService->verifyAccessToken($accessToken);

        if (!$tokenInfo) {
            return response()->json([
                'error' => 'Invalid access token'
            ], 401);
        }

        // Get user profile from LINE
        $profile = $this->lineService->getUserProfile($accessToken);

        if (!$profile) {
            return response()->json([
                'error' => 'Failed to get user profile'
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

        // Attach user to request
        $request->merge(['user' => $user]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
