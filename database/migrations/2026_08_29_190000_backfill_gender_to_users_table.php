<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const FEMININE_MARKERS = [
        'putri', 'siti', 'salsa', 'aima', 'astin', 'astilah', 'sahan', 'daniati',
        'dayanti', 'fadilah', 'nurfadilah', 'wati', 'sari', 'astuti', 'aini',
        'nisa', 'aisyah', 'khadijah', 'maryam', 'fatimah', 'fauziyyah',
        'nurhayati', 'rizqia', 'aulia', 'nabila', 'zahra', 'laila', 'nadira',
    ];

    private const MALE_EXCEPTIONS_ENDING_A = [
        'saputra', 'putra', 'kusuma', 'dewantara', 'sendana', 'yudha', 'dwi',
    ];

    public function up(): void
    {
        $teachers = DB::table('users')
            ->where('role', 'guru')
            ->whereNull('gender')
            ->get(['id', 'name']);

        foreach ($teachers as $teacher) {
            DB::table('users')
                ->where('id', $teacher->id)
                ->update(['gender' => $this->guessGender($teacher->name)]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'guru')->update(['gender' => null]);
    }

    private function guessGender(string $name): string
    {
        $tokens = preg_split('/[^a-z]+/', strtolower($name));
        $tokens = array_values(array_filter(array_map('trim', $tokens ?: []), fn ($t) => $t !== ''));

        $nameTokens = array_values(array_filter($tokens, fn ($t) => ! in_array($t, [
            's', 'pd', 'pd.i', 'hum', 'ag', 'th', 'th.i', 'sos', 'm.ag', 'm.pd', 'ma', 'spd', 'spdi', 'i', 'ii',
        ], true)));

        $last = end($nameTokens) ?: '';

        foreach (self::FEMININE_MARKERS as $marker) {
            if (in_array($marker, $nameTokens, true) || str_ends_with(implode('', $nameTokens), $marker)) {
                return 'P';
            }
        }

        if (str_ends_with($last, 'a') && ! in_array($last, self::MALE_EXCEPTIONS_ENDING_A, true)) {
            return 'P';
        }

        return 'L';
    }
};