<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/** Sign in as an admin for the rest of the test. */
function actingAsAdmin(array $attributes = []): User
{
    $user = User::factory()->admin()->create($attributes);
    test()->actingAs($user);

    return $user;
}

/** The real site content (seeders), with uploads going to a fake disk. */
function seedSite(): void
{
    Storage::fake('public');
    config(['site.admin.email' => 'owner@example.com', 'site.admin.password' => 'a-strong-password-123']);
    test()->seed(DatabaseSeeder::class);
}
