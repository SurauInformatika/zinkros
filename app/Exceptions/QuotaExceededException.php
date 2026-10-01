<?php

namespace App\Exceptions;

use App\Models\School;
use RuntimeException;

class QuotaExceededException extends RuntimeException
{
    private const LABELS = [
        'siswa' => 'Siswa',
        'guru' => 'Guru',
    ];

    public function __construct(
        public readonly School $school,
        public readonly string $key,
        public readonly int $increment,
        public readonly int $quota,
    ) {
        $label = self::LABELS[$key] ?? ucfirst($key);
        $used = $school->quotaUsed($key);

        parent::__construct(
            sprintf(
                'Kuota %s untuk paket %s sudah penuh (%d/%d). Hapus data lama atau hubungi pengelola platform untuk upgrade paket.',
                $label,
                $school->planLabel(),
                $used,
                $quota,
            )
        );
    }
}