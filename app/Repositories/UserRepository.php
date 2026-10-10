<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    /**
     * Find a user by id.
     *
     * @param  int  $id  User id
     */
    public function findUser(int $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * Find a user by email or code.
     */
    public function findUserByEmailOrCode(string $identifier): ?User
    {
        return User::query()
            ->where(function ($query) use ($identifier): void {
                $query->where('email', $identifier)
                    ->orWhere('code', $identifier);
            })
            ->first();
    }

    /**
     * Search users by role with pagination.
     *
     * @param  array{q?: string, limit?: int, page?: int}  $params
     */
    public function searchUsersByRole(UserRole $role, array $params = []): LengthAwarePaginator
    {
        $limit = max(1, min((int) ($params['limit'] ?? 10), 100));
        $page = max(1, (int) ($params['page'] ?? 1));
        $q = trim((string) ($params['q'] ?? ''));

        return User::query()
            ->where('role', $role)
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Insert a user row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function insertUser(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /**
     * Update a user row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateUser(User $user, array $attributes): User
    {
        $user->fill($attributes);
        $user->save();

        return $user->refresh();
    }

    /**
     * Delete a user row.
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}
