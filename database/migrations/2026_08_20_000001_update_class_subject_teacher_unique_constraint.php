<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK constraints that depend on the unique index
        DB::statement('ALTER TABLE class_subject_teacher DROP FOREIGN KEY class_subject_teacher_class_id_foreign');
        DB::statement('ALTER TABLE class_subject_teacher DROP FOREIGN KEY class_subject_teacher_subject_id_foreign');
        DB::statement('ALTER TABLE class_subject_teacher DROP FOREIGN KEY class_subject_teacher_teacher_id_foreign');

        // Drop old unique index
        DB::statement('ALTER TABLE class_subject_teacher DROP INDEX class_subject_teacher_class_id_subject_id_teacher_id_unique');

        // Create new unique index including academic_year_id
        DB::statement('ALTER TABLE class_subject_teacher ADD UNIQUE INDEX cst_class_subject_teacher_year_unique (class_id, subject_id, teacher_id, academic_year_id)');

        // Recreate FK constraints
        DB::statement('ALTER TABLE class_subject_teacher ADD CONSTRAINT class_subject_teacher_class_id_foreign FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE class_subject_teacher ADD CONSTRAINT class_subject_teacher_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE class_subject_teacher ADD CONSTRAINT class_subject_teacher_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE class_subject_teacher DROP FOREIGN KEY class_subject_teacher_class_id_foreign');
        DB::statement('ALTER TABLE class_subject_teacher DROP FOREIGN KEY class_subject_teacher_subject_id_foreign');
        DB::statement('ALTER TABLE class_subject_teacher DROP FOREIGN KEY class_subject_teacher_teacher_id_foreign');

        DB::statement('ALTER TABLE class_subject_teacher DROP INDEX cst_class_subject_teacher_year_unique');

        DB::statement('ALTER TABLE class_subject_teacher ADD UNIQUE INDEX class_subject_teacher_class_id_subject_id_teacher_id_unique (class_id, subject_id, teacher_id)');

        DB::statement('ALTER TABLE class_subject_teacher ADD CONSTRAINT class_subject_teacher_class_id_foreign FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE class_subject_teacher ADD CONSTRAINT class_subject_teacher_subject_id_foreign FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE class_subject_teacher ADD CONSTRAINT class_subject_teacher_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users (id) ON DELETE CASCADE');
    }
};
