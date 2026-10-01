<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleFixSeeder extends Seeder
{
    public function run(): void
    {
        // Restore admin
        User::where('email', 'admin@sit.sch.id')->update(['role' => 'admin']);

        // Create wakasek (Kurikulum) user
        User::updateOrCreate(
            ['email' => 'wakakur@sit.sch.id'],
            [
                'name' => 'Wakil Kurikulum',
                'password' => Hash::make('password'),
                'role' => 'wakasek',
                'position' => User::POSITION_KURIKULUM,
                'school_id' => 'bd49e6f0-2fb3-4932-a711-9f4c84400a70',
                'email_verified_at' => now(),
            ]
        );

        // Create kepsek user
        User::updateOrCreate(
            ['email' => 'kepsek@sit.sch.id'],
            [
                'name' => 'Kepala Sekolah',
                'password' => Hash::make('password'),
                'role' => 'kepsek',
                'school_id' => 'bd49e6f0-2fb3-4932-a711-9f4c84400a70',
                'email_verified_at' => now(),
            ]
        );

        // Create wakasek (Kesiswaan) user
        User::updateOrCreate(
            ['email' => 'wakamur@sit.sch.id'],
            [
                'name' => 'Wakil Kepala Kesiswaan',
                'password' => Hash::make('password'),
                'role' => 'wakasek',
                'position' => User::POSITION_KESISWAAN,
                'school_id' => 'bd49e6f0-2fb3-4932-a711-9f4c84400a70',
                'email_verified_at' => now(),
            ]
        );

        // Create murid user
        User::updateOrCreate(
            ['email' => 'murid@sit.sch.id'],
            [
                'name' => 'Murid SIT',
                'password' => Hash::make('password'),
                'role' => 'murid',
                'school_id' => 'bd49e6f0-2fb3-4932-a711-9f4c84400a70',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Roles fixed: admin + wakasek (kurikulum) + kepsek + wakasek (kesiswaan) + murid');
    }
}
