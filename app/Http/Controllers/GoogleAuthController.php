<?php

namespace App\Http\Controllers;

use App\Http\Services\GoogleAuthService;
use App\Http\Services\GoogleCalendarService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleAuthController extends Controller {

    public function __construct(
        protected User $user ,
        protected GoogleCalendarService $googleService,
        protected GoogleAuthService $googleAuthService
    ) {}
    public function loginGoogle(Request $request): JsonResponse
    {
        $idToken = $request->input('credential');
        $result = $this->googleAuthService->loginWithGoogle($idToken);

        return response()->json($result, $result['success'] ? 200 : 401);
    }
    public function redirect(): RedirectResponse
    {
        return redirect($this->googleAuthService->getAuthUrl());
    }

    public function callback(Request $request): JsonResponse
    {
        $result = $this->googleAuthService->handleCallback($request);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

}
