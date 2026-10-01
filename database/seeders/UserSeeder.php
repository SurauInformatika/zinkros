<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::firstOrCreate(
            ['slug' => 'sit-default'],
            [
                'name' => 'Sekolah SIT (Default)',
                'plan' => School::PLAN_PRO,
                'status' => School::STATUS_ACTIVE,
            ],
        );

        $users = [
            [
                'name' => 'Administrator',
                'role' => User::ROLE_ADMIN,
                'phone' => '081200000001',
                'email' => 'admin@sit.sch.id',
            ],
            [
                'name' => 'Kepala Sekolah',
                'role' => User::ROLE_KEPSEK,
                'phone' => '081200000002',
                'email' => 'kepsek@sit.sch.id',
            ],
            [
                'name' => 'Bendahara',
                'role' => User::ROLE_KEUANGAN,
                'phone' => '081200000003',
                'email' => 'keuangan@sit.sch.id',
            ],
            [
                'name' => 'Ustadz Rizky',
                'role' => User::ROLE_GURU,
                'phone' => '081200000004',
                'email' => 'guru@sit.sch.id',
            ],
            [
                'name' => 'Orang Tua Sampel',
                'role' => User::ROLE_ORTU,
                'phone' => '081200000005',
                'email' => 'ortu@sit.sch.id',
            ],
            [
                'name' => 'Pak Satpam',
                'role' => User::ROLE_STAFF,
                'position' => 'Sekuriti',
                'phone' => '081200000006',
                'email' => 'staff@sit.sch.id',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [...$user, 'school_id' => $school->id, 'password' => 'password'],
            );
        }
    }
}
