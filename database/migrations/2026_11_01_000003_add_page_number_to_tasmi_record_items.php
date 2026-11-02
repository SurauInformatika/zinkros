<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('tasmi_record_items'))->pluck('name');

        if (! Schema::hasColumn('tasmi_record_items', 'page_number')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->unsignedSmallInteger('page_number')->nullable()->after('tasmi_record_id');
            });
        }

        if (! $indexes->contains('tasmi_record_items_record_fk_idx')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->index('tasmi_record_id', 'tasmi_record_items_record_fk_idx');
            });
        }

        if ($indexes->contains('tasmi_record_items_tasmi_record_id_quran_master_id_unique')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->dropUnique('tasmi_record_items_tasmi_record_id_quran_master_id_unique');
            });
        }

        if (! $indexes->contains('tasmi_item_record_page_surah_unique')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->unique(['tasmi_record_id', 'page_number', 'quran_master_id'], 'tasmi_item_record_page_surah_unique');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('tasmi_record_items'))->pluck('name');

        if ($indexes->contains('tasmi_item_record_page_surah_unique')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->dropUnique('tasmi_item_record_page_surah_unique');
            });
        }

        if (Schema::hasColumn('tasmi_record_items', 'page_number')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->dropColumn('page_number');
            });
        }

        if (! $indexes->contains('tasmi_record_items_tasmi_record_id_quran_master_id_unique')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->unique(['tasmi_record_id', 'quran_master_id']);
            });
        }

        if ($indexes->contains('tasmi_record_items_record_fk_idx')) {
            Schema::table('tasmi_record_items', function (Blueprint $table) {
                $table->dropIndex('tasmi_record_items_record_fk_idx');
            });
        }
    }
};
