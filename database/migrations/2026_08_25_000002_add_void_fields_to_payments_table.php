<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', ['active', 'void'])->default('active')->after('note');
            $table->text('void_reason')->nullable()->after('status');
            $table->timestamp('voided_at')->nullable()->after('void_reason');
            $table->uuid('voided_by')->nullable()->after('voided_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'void_reason', 'voided_at', 'voided_by']);
        });
    }
};
