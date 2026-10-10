<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class MasterUserService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * Fetch paginated users for a role.
     *
     * @param  array{q?: string, limit?: int, page?: int}  $params
     */
    public function fetchUserList(UserRole $role, array $params = []): LengthAwarePaginator
    {
        return $this->userRepository->searchUsersByRole($role, $params);
    }

    /**
     * Create a student or lecturer.
     *
     * @param  array{name: string, email: string, code: string, password?: string}  $params
     */
    public function createUser(UserRole $role, array $params): User
    {
        return $this->userRepository->insertUser([
            'name' => $params['name'],
            'email' => $params['email'],
            'code' => $params['code'],
            'password' => $params['password'] ?? 'password',
            'role' => $role,
        ]);
    }

    /**
     * Update a student or lecturer.
     *
     * @param  array{name: string, email: string, code: string, password?: string|null}  $params
     */
    public function updateUser(User $user, UserRole $role, array $params): User
    {
        if ($user->role !== $role) {
            throw ValidationException::withMessages([
                'role' => 'User role mismatch.',
            ]);
        }

        $attributes = [
            'name' => $params['name'],
            'email' => $params['email'],
            'code' => $params['code'],
        ];

        if (! empty($params['password'])) {
            $attributes['password'] = $params['password'];
        }

        return $this->userRepository->updateUser($user, $attributes);
    }

    /**
     * Delete a student or lecturer.
     */
    public function deleteUser(User $user, UserRole $role): void
    {
        if ($user->role !== $role) {
            throw ValidationException::withMessages([
                'role' => 'User role mismatch.',
            ]);
        }

        $this->userRepository->deleteUser($user);
    }
}
