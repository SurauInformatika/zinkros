<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('remedial_score', 5, 2)->nullable()->after('score');
            $table->decimal('remedial_capped', 5, 2)->nullable()->after('remedial_score');
            $table->text('remedial_notes')->nullable()->after('remedial_capped');
            $table->foreignUuid('remedial_by')->nullable()->after('remedial_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('remedial_at')->nullable()->after('remedial_by');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['remedial_by']);
            $table->dropColumn(['remedial_score', 'remedial_capped', 'remedial_notes', 'remedial_by', 'remedial_at']);
        });
    }
};