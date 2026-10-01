<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Template KALDIK (Superadmin)
        Schema::create('kaldik_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('source', ['dindik', 'kemenag', 'custom'])->default('custom');
            $table->json('default_day_structure')->nullable();
            $table->json('default_level_structure')->nullable();
            $table->json('default_holidays')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. KALDIK Tenant
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignUuid('template_id')->nullable()->constrained('kaldik_templates')->nullOnDelete();
            $table->string('name');
            $table->tinyInteger('semester');
            $table->enum('source', ['dindik', 'kemenag', 'custom'])->default('custom');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'pending', 'final', 'archived'])->default('draft');
            $table->integer('version')->default(1);
            $table->uuid('parent_version_id')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'academic_year_id', 'semester', 'version'], 'cal_uniq');
        });

        // 3. Struktur JP per hari
        Schema::create('calendar_day_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_calendar_id')->constrained('academic_calendars')->cascadeOnDelete();
            $table->enum('day_name', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->integer('jp_count')->default(0);
            $table->integer('jp_duration_minutes')->default(35);
            $table->timestamps();

            $table->unique(['academic_calendar_id', 'day_name'], 'cds_uniq');
        });

        // 4. Durasi JP per level
        Schema::create('calendar_level_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_calendar_id')->constrained('academic_calendars')->cascadeOnDelete();
            $table->integer('grade_level_start');
            $table->integer('grade_level_end');
            $table->integer('jp_duration_minutes')->default(35);
            $table->timestamps();

            $table->unique(['academic_calendar_id', 'grade_level_start', 'grade_level_end'], 'cls_uniq');
        });

        // 5. Libur
        Schema::create('calendar_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_calendar_id')->constrained('academic_calendars')->cascadeOnDelete();
            $table->date('date');
            $table->string('name');
            $table->enum('type', ['nasional', 'daerah', 'sekolah', 'rutin'])->default('sekolah');
            $table->timestamps();
        });

        // 6. Override kelas akhir / kegiatan khusus
        Schema::create('calendar_class_overrides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_calendar_id')->constrained('academic_calendars')->cascadeOnDelete();
            $table->integer('grade_level')->nullable();
            $table->enum('override_type', ['orientation', 'exam', 'graduation', 'digital_class', 'assessment', 'teacher_training', 'other']);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Libur Rutin (Global, Superadmin kelola)
        Schema::create('recurring_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->integer('month');
            $table->integer('day')->nullable();
            $table->enum('calendar_type', ['masehi', 'hijriah'])->default('masehi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. Notifikasi
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at'], 'notif_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('recurring_holidays');
        Schema::dropIfExists('calendar_class_overrides');
        Schema::dropIfExists('calendar_holidays');
        Schema::dropIfExists('calendar_level_structures');
        Schema::dropIfExists('calendar_day_structures');
        Schema::dropIfExists('academic_calendars');
        Schema::dropIfExists('kaldik_templates');
    }
};
