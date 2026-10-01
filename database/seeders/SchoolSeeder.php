<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@platform.id'],
            [
                'name' => 'Super Admin Platform',
                'role' => User::ROLE_SUPERADMIN,
                'school_id' => null,
                'password' => 'password',
            ],
        );

        $alizzah = School::updateOrCreate(
            ['slug' => 'sit-al-izzah'],
            [
                'name' => 'SIT Al-Izzah',
                'address' => 'Jl. Pendidikan No. 1, Bogor',
                'phone' => '0251-123456',
                'email' => 'info@alizzah.sch.id',
                'plan' => School::PLAN_PRO,
                'status' => School::STATUS_ACTIVE,
                'subscribed_at' => now(),
                'next_billing_at' => now()->addDays(30),
            ],
        );

        User::firstOrCreate(
            ['email' => 'admin@alizzah.sch.id'],
            [
                'school_id' => $alizzah->id,
                'name' => 'Admin Al-Izzah',
                'role' => User::ROLE_ADMIN,
                'phone' => '081200000101',
                'password' => 'password',
            ],
        );

        $alhikmah = School::updateOrCreate(
            ['slug' => 'sit-al-hikmah'],
            [
                'name' => 'SIT Al-Hikmah',
                'address' => 'Jl. Hikmah No. 7, Depok',
                'phone' => '021-7654321',
                'email' => 'info@alhikmah.sch.id',
                'plan' => School::PLAN_PRO,
                'status' => School::STATUS_TRIAL,
                'trial_ends_at' => Carbon::now()->addDays(14),
            ],
        );

        User::firstOrCreate(
            ['email' => 'admin@alhikmah.sch.id'],
            [
                'school_id' => $alhikmah->id,
                'name' => 'Admin Al-Hikmah',
                'role' => User::ROLE_ADMIN,
                'phone' => '081200000102',
                'password' => 'password',
            ],
        );
    }
}
