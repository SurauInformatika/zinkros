<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LaravelWebauthn\Models\WebauthnKey;

#[Fillable(['school_id', 'student_id', 'name', 'gender', 'role', 'created_by', 'phone', 'position', 'email', 'password', 'google_id', 'google_avatar', 'password_changed_at', 'is_wali_kelas', 'is_pj_tahfidz'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToSchool, HasFactory, HasUuids, Notifiable;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (! $user->school_id || ! in_array($user->role, School::STAFF_ROLES, true)) {
                return;
            }

            School::find($user->school_id)?->assertWithinQuota('guru');
        });
    }

    public const ROLE_ADMIN = 'admin';

    public const ROLE_GURU = 'guru';

    public const ROLE_KEUANGAN = 'keuangan';

    public const ROLE_ORTU = 'ortu';

    public const ROLE_STAFF = 'staff';

    public const ROLE_SUPERADMIN = 'superadmin';

    public const ROLE_WAKASEK = 'wakasek';

    public const ROLE_KEPSEK = 'kepsek';

    public const ROLE_MURID = 'murid';

    public const POSITION_KURIKULUM = 'kurikulum';

    public const POSITION_KESISWAAN = 'kesiswaan';

    public const POSITION_KEISLAMAN = 'keislaman';

    public const POSITION_SARPRAS = 'sarpras';

    public const POSITION_HUMAS = 'humas';

    /**
     * Default position keys offered for the wakasek role. Custom labels can be
     * typed as a free-form value when creating a wakasek account.
     */
    public const WAKASEK_POSITIONS = [
        self::POSITION_KURIKULUM => 'Wakil Kepala Kurikulum',
        self::POSITION_KESISWAAN => 'Wakil Kepala Kesiswaan',
        self::POSITION_KEISLAMAN => 'Wakil Kepala Keislaman',
        self::POSITION_SARPRAS => 'Wakil Kepala Sarana Prasarana',
        self::POSITION_HUMAS => 'Wakil Kepala Hubungan Masyarakat',
    ];

    /**
     * Whether the user is a wakasek (any jabatan).
     */
    public function isWakasek(): bool
    {
        return $this->role === self::ROLE_WAKASEK;
    }

    /**
     * Display label for the user's wakasek jabatan (falls back to a readable
     * form of the raw position, or a generic term).
     */
    public function wakasekPositionLabel(): ?string
    {
        if (! $this->isWakasek() || empty($this->position)) {
            return null;
        }

        return self::WAKASEK_POSITIONS[$this->position] ?? ucwords(str_replace('_', ' ', $this->position));
    }

    /**
     * Default temporary password for newly created parent (ortu) accounts.
     * Parent must change it on first login.
     */
    public const DEFAULT_FIRST_PASSWORD = 'katasandi123';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'password' => 'hashed',
            'is_wali_kelas' => 'boolean',
            'is_pj_tahfidz' => 'boolean',
        ];
    }

    /**
     * Whether this account still uses the default temporary password and
     * must be forced to change it (ortu accounts created via the combobox).
     */
    public function mustChangePassword(): bool
    {
        return $this->isOrtu() && $this->password_changed_at === null;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isKeuangan(): bool
    {
        return $this->role === self::ROLE_KEUANGAN;
    }

    public function isOrtu(): bool
    {
        return $this->role === self::ROLE_ORTU;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isWakakur(): bool
    {
        return $this->role === self::ROLE_WAKAKUR;
    }

    public function isKepsek(): bool
    {
        return $this->role === self::ROLE_KEPSEK;
    }

    public function isWakamur(): bool
    {
        return $this->role === self::ROLE_WAKAMUR;
    }

    public function isMurid(): bool
    {
        return $this->role === self::ROLE_MURID;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function webauthnKeys(): HasMany
    {
        return $this->hasMany(WebauthnKey::class, 'user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_parent', 'user_id', 'student_id')
            ->withPivot('relation', 'is_primary', 'school_id')
            ->withTimestamps();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function taughtClasses(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_subject_teacher', 'teacher_id', 'class_id')
            ->withPivot('subject_id');
    }

    public function taughtSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'teacher_id', 'subject_id')
            ->withPivot('class_id');
    }

    public function waliClasses(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_homerooms', 'user_id', 'class_id')
            ->withPivot('label', 'sort')
            ->orderBy('class_homerooms.sort');
    }

    public function homerooms(): HasMany
    {
        return $this->hasMany(ClassHomeroom::class, 'user_id');
    }

    public function isWaliKelasFor(ClassRoom $class): bool
    {
        return $this->homerooms()->where('class_id', $class->id)->exists();
    }

    public function isWaliKelasActive(): bool
    {
        return $this->homerooms()->exists();
    }

    public function classSubjectTeachers(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'teacher_id');
    }

    public function quranTeachingAssignments(): HasMany
    {
        return $this->hasMany(QuranTeachingAssignment::class, 'teacher_id');
    }

    public function teacherRoles(): HasMany
    {
        return $this->hasMany(TeacherRole::class, 'teacher_id');
    }

    public function studentRelatedRoles(): HasMany
    {
        return $this->hasMany(TeacherRole::class, 'teacher_id')->where('is_student_related', true);
    }

    public function hasTeacherRole(string $roleName): bool
    {
        return $this->teacherRoles()->where('role_name', $roleName)->exists();
    }

    public function hasStudentRelatedRole(): bool
    {
        return $this->teacherRoles()->studentRelated()->exists();
    }

    public function hasSubjectMapping(): bool
    {
        return $this->classSubjectTeachers()->exists();
    }

    public function hasQuranAssignment(): bool
    {
        return $this->quranTeachingAssignments()->exists();
    }

    public function isQuranTeacher(): bool
    {
        return $this->subjects()->where('subjects.type', Subject::TYPE_QURAN)->exists()
            || $this->taughtSubjects()->where('subjects.type', Subject::TYPE_QURAN)->exists();
    }
}
