<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('blog_posts', 'school_id')) {
            return;
        }

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->uuid('school_id')->nullable()->index()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('blog_posts', 'school_id')) {
            return;
        }

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('school_id');
        });
    }
};
