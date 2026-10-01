<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('status');
            $table->text('suspended_reason')->nullable()->after('suspended_at');
            $table->uuid('suspended_by')->nullable()->after('suspended_reason');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['suspended_at', 'suspended_reason', 'suspended_by']);
        });
    }
};
