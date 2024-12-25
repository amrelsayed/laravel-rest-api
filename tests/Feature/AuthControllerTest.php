<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Amr Elsayed',
            'email' => 'test@unit.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertExactJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
            ]);
    }

    public function test_user_cannot_login_with_wrong_credentials(): void
    {
        $response = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'passwordsdf',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('email');
    }

    public function test_login_fails_with_missing_email(): void
    {
        $response = $this->postJson(route('login'), [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('email');
    }

    public function test_login_fails_with_missing_password(): void
    {
        $response = $this->postJson(route('login'), [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('password');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $token = $this->user->createToken($this->user->name)->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(route('logout'));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'user logged out',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable' => $this->user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->post(route('logout'));

        $response->assertStatus(401);
    }
}
