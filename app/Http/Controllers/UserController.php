<?php

namespace App\Http\Controllers;

use App\Http\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(protected UserRepositoryInterface $userRepository){}
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse {
        $users = $this->userRepository->all();
//        $users = User::query()
//            ->select(['users.id', 'users.name', 'users.email', 'specifications.title'])
//            ->leftJoin('specification_user', 'specification_user.user_id', '=', 'users.id')
//            ->leftJoin('specifications', 'specification_user.specification_id', '=', 'specifications.id')
//            ->get();
        return response()->json($users);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse {
        try {
            DB::beginTransaction();
            $user = $this->userRepository->create([
                'name'      => $request->name,
                'email'     => $request->email,
                'last_name' => $request->last_name,
                'password'  => Hash::make('password'),
            ]);

//            $user->specifications()->attach([
//                'specification_id' => 1
//            ]);
//            $user->companies()->attach([
//                'company_id' => 1
//            ]);

            DB::commit();
            return response()->json($user);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
            ]);
        }
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('companies','posts.comments.likes')->loadCount(['comments' , 'likes']));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return int
     */
    public function update(int $id, Request $request): int {
        return $this->userRepository->update(['name' => $request->name], $id);
    }

    public function delete(int $id): int {
//        $user = User::find($id);
//        if ($user) {
//            $user->delete();
//        }
        return $this->userRepository->delete($id);
    }

    public function getUserCompanies($userId): JsonResponse
    {
        $companies = $this->userRepository->getUserCompanies($userId);
        return response()->json($companies);
    }

    public function getUserPosts($userId): JsonResponse
    {
        $posts = $this->userRepository->getUserPosts($userId);
        return response()->json($posts);
    }

    public function getUserComments($userId): JsonResponse
    {
        $comments = $this->userRepository->getUserComments($userId);
        return response()->json($comments);
    }
    public function getUserBookings($userId): JsonResponse
    {
        $booking = $this->userRepository->getUserBookings($userId);
        return response()->json($booking);
    }
}
