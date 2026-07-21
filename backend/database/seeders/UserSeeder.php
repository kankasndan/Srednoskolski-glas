<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Manually defined test accounts. Password for every account is "password".
     *
     * @var list<array{username: string, email: string, onboarded: bool, role: string}>
     */
    private const USERS = [
        ['username' => 'test_user', 'email' => 'test@example.com', 'onboarded' => true, 'role' => 'user'],
        ['username' => 'demo_student', 'email' => 'demo@example.com', 'onboarded' => true, 'role' => 'user'],
        ['username' => 'ana_k', 'email' => 'ana@example.com', 'onboarded' => true, 'role' => 'user'],
        ['username' => 'marko_p', 'email' => 'marko@example.com', 'onboarded' => true, 'role' => 'user'],
        ['username' => 'elena_s', 'email' => 'elena@example.com', 'onboarded' => true, 'role' => 'user'],
        ['username' => 'stefan_t', 'email' => 'stefan@example.com', 'onboarded' => true, 'role' => 'user'],
        ['username' => 'ivana_m', 'email' => 'ivana@example.com', 'onboarded' => true, 'role' => 'user'],
        ['username' => 'nikola_d', 'email' => 'nikola@example.com', 'onboarded' => false, 'role' => 'user'],
        ['username' => 'profesor_x', 'email' => 'profesor@example.com', 'onboarded' => false, 'role' => 'user'],
    ];

    public function run(): void
    {
        foreach (self::USERS as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'username' => $user['username'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'email_verified_at' => now(),
                    'onboarding_completed_at' => $user['onboarded'] ? now() : null,
                ],
            );
        }
    }
}
