<?php

namespace App\Http\Controllers;

use App\Jobs\SendUserNotificationJob;
use App\Models\User;
use App\Notifications\UserRegisteredNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use App\Http\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $usersRepository,
        protected UserRegisteredNotification $userRegisteredNotification,
        protected SendUserNotificationJob $sendUserNotificationJob
    ){}

    public function register(Request $request) :JsonResponse
    {
        try {
            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            $user = $this->usersRepository->create($data);
            $sendUserNotificationJob = new SendUserNotificationJob($user, $this->userRegisteredNotification);
            dispatch($sendUserNotificationJob);

            return response()->json([
                "success" => true
            ]);

        } catch(Exception $exception) {
            Log::info($exception);
            return response()->json([
                "success" => false
            ]);
        }
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
    public function logout(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user) {
            $user->tokens()->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 401);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            $user = $this->usersRepository->getUser($data);
            if(!$user){
                return response()->json([
                    "success" => false
                ]);
            }
            $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;
            return response()->json([
                "token" => $token,
                "user"  => $user
            ]);
        } catch (Exception $ex) {
            return response()->json($ex, 500);
        }
    }
}
