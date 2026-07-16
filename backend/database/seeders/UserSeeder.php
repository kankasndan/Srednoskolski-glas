<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Manually defined test accounts. Password for every account is "password".
     *
     * @var list<array{username: string, email: string, onboarded: bool}>
     */
    private const USERS = [
        ['username' => 'test_user', 'email' => 'test@example.com', 'onboarded' => true],
        ['username' => 'demo_student', 'email' => 'demo@example.com', 'onboarded' => true],
        ['username' => 'ana_k', 'email' => 'ana@example.com', 'onboarded' => true],
        ['username' => 'marko_p', 'email' => 'marko@example.com', 'onboarded' => true],
        ['username' => 'elena_s', 'email' => 'elena@example.com', 'onboarded' => true],
        ['username' => 'stefan_t', 'email' => 'stefan@example.com', 'onboarded' => true],
        ['username' => 'ivana_m', 'email' => 'ivana@example.com', 'onboarded' => true],
        ['username' => 'nikola_d', 'email' => 'nikola@example.com', 'onboarded' => false],
        ['username' => 'profesor_x', 'email' => 'profesor@example.com', 'onboarded' => false],
    ];

    public function run(): void
    {
        $now = now();
        $password = Hash::make('password');

        foreach (self::USERS as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'username' => $user['username'],
                    'password' => $password,
                    'email_verified_at' => $now,
                    'type' => 'user',
                    'onboarding_completed_at' => $user['onboarded'] ? $now : null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }
}
