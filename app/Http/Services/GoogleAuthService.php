<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class GoogleAuthService
{
    public function __construct(protected User $user){}
    public function loginWithGoogle(string $idToken): array
    {

        $googleUser = Http::get("https://www.googleapis.com/oauth2/v3/tokeninfo?id_token={$idToken}")->json();

        if (!isset($googleUser['email'])) {
            return ['success' => false, 'error' => 'Invalid token'];
        }
        $user = $this->user->firstOrCreate(
            ['email' => $googleUser['email']],
            [
                'name'     => $googleUser['name'] ?? 'No Name',
                'password' => Hash::make(uniqid()),
            ]
        );
        $authToken = $user->createToken('auth_token')->plainTextToken;
        return [
            'success' => true,
            'user'    => $user,
            'token'   => $authToken,
        ];
    }
    public function getAuthUrl(): string
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        $redirectUri = env('GOOGLE_REDIRECT_URI');

        return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
                'client_id'     => $clientId,
                'redirect_uri'  => $redirectUri,
                'response_type' => 'code',
                'scope'         => 'https://www.googleapis.com/auth/calendar',
                'access_type'   => 'offline',
                'prompt'        => 'consent',
            ]);
    }
    public function handleCallback(Request $request): array
    {
        $code = $request->input('code');

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            'grant_type'    => 'authorization_code',
        ]);

        $googleResponse = $response->json();

        if (isset($googleResponse['error'])) {
            return ['success' => false, 'error' => $googleResponse];
        }

        $expiresIn = $googleResponse['expires_in'];
        $expireTime = Carbon::now()->addSeconds($expiresIn);
        $googleResponse['expires_in'] = $expireTime->toDateTimeString();

        Cache::put('google_tokens_' . Auth::id(), $googleResponse);

        return ['success' => true];
    }
}
