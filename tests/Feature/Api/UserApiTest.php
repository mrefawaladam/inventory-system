<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic roles
        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type',
                ],
            ]);
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_users()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        User::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'pagination',
            ]);
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    /** @test */
    public function it_can_show_a_user()
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;
        $targetUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/' . $targetUser->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_can_update_a_user()
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;
        $targetUser = User::factory()->create(['name' => 'Old Name']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/users/' . $targetUser->id, [
                'name' => 'Updated Name',
                'email' => $targetUser->email,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        $admin = User::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;
        $targetUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/users/' . $targetUser->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }
}
