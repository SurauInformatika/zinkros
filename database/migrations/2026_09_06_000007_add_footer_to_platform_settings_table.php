<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->boolean('show_footer')->default(true)->after('testimonials');
            $table->string('footer_tagline', 300)->default(config('platform.footer_tagline', ''))->after('show_footer');
            $table->string('footer_address', 300)->nullable()->after('footer_tagline');
            $table->string('footer_phone', 50)->nullable()->after('footer_address');
            $table->string('footer_email', 150)->nullable()->after('footer_phone');
            $table->string('footer_copyright', 200)->nullable()->after('footer_email');
            $table->longText('footer_medsos')->nullable()->after('footer_copyright');
        });

        $defaults = config('platform.footer_medsos_defaults', []);
        if ($defaults !== []) {
            \Illuminate\Support\Facades\DB::table('platform_settings')
                ->whereNull('footer_medsos')
                ->update(['footer_medsos' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_footer',
                'footer_tagline',
                'footer_address',
                'footer_phone',
                'footer_email',
                'footer_copyright',
                'footer_medsos',
            ]);
        });
    }
};