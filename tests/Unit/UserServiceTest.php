<?php

namespace Tests\Unit;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function test_create_user_hashes_password(): void
    {
        $user = $this->service->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secure-pass',
            'role' => User::ROLE_USER,
        ]);

        $this->assertNotNull($user->id);
        $this->assertTrue(Hash::check('secure-pass', $user->password));
    }

    public function test_create_user_raises_on_duplicate_email(): void
    {
        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secure-pass',
            'role' => User::ROLE_USER,
        ];

        $this->service->create($payload);

        $this->expectException(DuplicateEmailException::class);
        $this->service->create($payload);
    }

    public function test_update_user_keeps_password_when_not_provided(): void
    {
        $user = $this->service->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'secure-pass',
            'role' => User::ROLE_USER,
        ]);

        $oldHash = $user->password;

        $updated = $this->service->update($user->id, [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertSame($oldHash, $updated->password);
        $this->assertSame(User::ROLE_ADMIN, $updated->role);
    }

    public function test_delete_user_raises_when_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->service->delete(999);
    }
}
