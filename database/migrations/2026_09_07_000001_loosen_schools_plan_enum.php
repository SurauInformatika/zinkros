<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('plan', 20)->default('pro')->change();
        });

        DB::table('schools')->whereNull('plan')->orWhere('plan', '')->update(['plan' => 'pro']);
    }

    public function down(): void
    {
        DB::table('schools')->whereNotIn('plan', ['basic', 'pro'])->update(['plan' => 'pro']);
    }
};