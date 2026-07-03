<?php

namespace App\Services;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function list(): array
    {
        return User::query()->orderByDesc('created_at')->get()->all();
    }

    public function find(int $id): User
    {
        $user = User::query()->find($id);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function create(array $data): User
    {
        try {
            return User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateEmail($exception)) {
                throw new DuplicateEmailException();
            }

            throw $exception;
        }
    }

    public function update(int $id, array $data): User
    {
        $user = $this->find($id);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if (! empty($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        try {
            $user->save();
        } catch (QueryException $exception) {
            if ($this->isDuplicateEmail($exception)) {
                throw new DuplicateEmailException();
            }

            throw $exception;
        }

        return $user;
    }

    public function delete(int $id): void
    {
        $user = $this->find($id);
        $user->delete();
    }

    private function isDuplicateEmail(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return in_array($sqlState, ['23505', '23000'], true);
    }
}
