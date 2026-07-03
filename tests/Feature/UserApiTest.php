<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secure-pass',
        'role' => User::ROLE_USER,
    ];

    public function test_list_users_empty(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertOk()->assertExactJson([]);
    }

    public function test_create_user(): void
    {
        $response = $this->postJson('/api/users', $this->payload);

        $response->assertCreated()
            ->assertJsonPath('name', $this->payload['name'])
            ->assertJsonPath('email', $this->payload['email'])
            ->assertJsonPath('role', $this->payload['role'])
            ->assertJsonStructure(['id', 'passwordHash', 'created_at'])
            ->assertJsonMissing(['password']);

        $this->assertStringStartsWith('$2', $response->json('passwordHash'));

        $user = User::query()->find($response->json('id'));
        $this->assertTrue(Hash::check($this->payload['password'], $user->password));
    }

    public function test_create_user_validation_error(): void
    {
        $payload = $this->payload;
        $payload['password'] = 'short';

        $this->postJson('/api/users', $payload)->assertStatus(400);
    }

    public function test_create_user_duplicate_email(): void
    {
        $this->postJson('/api/users', $this->payload)->assertCreated();

        $response = $this->postJson('/api/users', $this->payload);

        $response->assertStatus(409)
            ->assertJsonPath('detail', 'A user with this email already exists.');
    }

    public function test_retrieve_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email)
            ->assertJsonStructure(['passwordHash']);
    }

    public function test_retrieve_user_not_found(): void
    {
        $this->getJson('/api/users/999')->assertNotFound();
    }

    public function test_list_users(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $user->id);
    }

    public function test_update_user(): void
    {
        $user = User::factory()->create();
        $payload = [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'new-secure-pass',
            'role' => User::ROLE_ADMIN,
        ];

        $response = $this->putJson("/api/users/{$user->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('name', $payload['name'])
            ->assertJsonPath('email', $payload['email'])
            ->assertJsonPath('role', User::ROLE_ADMIN);

        $user->refresh();
        $this->assertTrue(Hash::check($payload['password'], $user->password));
    }

    public function test_update_user_without_password(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;

        $payload = [
            'name' => 'Jane Smith',
            'email' => $user->email,
            'role' => User::ROLE_ADMIN,
        ];

        $this->putJson("/api/users/{$user->id}", $payload)->assertOk();

        $user->refresh();
        $this->assertSame($oldHash, $user->password);
        $this->assertSame('Jane Smith', $user->name);
    }

    public function test_update_user_not_found(): void
    {
        $payload = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'role' => User::ROLE_USER,
        ];

        $this->putJson('/api/users/999', $payload)->assertNotFound();
    }

    public function test_delete_user(): void
    {
        $user = User::factory()->create();

        $this->deleteJson("/api/users/{$user->id}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_user_not_found(): void
    {
        $this->deleteJson('/api/users/999')->assertNotFound();
    }

    public function test_openapi_schema_is_available(): void
    {
        $this->get('/api/schema/')->assertOk();
    }

    public function test_openapi_docs_are_available(): void
    {
        $this->get('/api/docs/')->assertOk();
    }
}
