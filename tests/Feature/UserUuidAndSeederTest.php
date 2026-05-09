<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\UserSeeder;

class UserUuidAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_primary_key_is_uuid()
    {
        $user = User::factory()->create();

        $this->assertIsString($user->id);
        $this->assertMatchesRegularExpression('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $user->id);
    }

    public function test_user_seeder_creates_super_admin_with_expected_email()
    {
        $this->seed(UserSeeder::class);

        $email = env('SEED_ADMIN_EMAIL') ?: 'contact@Orientationtech.ma';
        $this->assertDatabaseHas('users', ['email' => $email]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertMatchesRegularExpression('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $user->id);
    }
}
