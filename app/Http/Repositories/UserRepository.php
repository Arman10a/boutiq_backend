<?php

namespace App\Http\Repositories;

use App\Http\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**'
     * @param User $user
     */
    public function __construct(protected User $user) {}

    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->user->all();
    }

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return $this->user->create($data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->user->destroy($id);
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): User|null
    {
        return $this->user->where('id', $id)->first();
    }

    /**
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update(array $data, int $id): bool
    {
        return $this->user->where('id', $id)->update($data);
    }

    public function getUserComments(int $userId): mixed
    {
        $user = $this->user->with('comments')->find($userId);
        return $user?->comments ?? null;
    }

    public function getUserPosts(int $userId): mixed
    {
        $user = $this->user->with('posts')->find($userId);
        return $user?->posts ?? null;
    }

    public function getUserCompanies(int $userId): mixed
    {
        $user = $this->user->with('companies')->find($userId);
        return $user?->companies ?? null;
    }
    public function getUserBookings(int $userId): mixed
    {
        $user = $this->user->with('bookings')->find($userId);
        return $user?->bookings ?? null;
    }
    public function getUser(array $data)
    {
        $user = $this->user->where('email', $data['email'])->first();
        if ($user && Hash::check($data['password'], $user->password)) {
            return $user;
        }
        return null;

    }
}
