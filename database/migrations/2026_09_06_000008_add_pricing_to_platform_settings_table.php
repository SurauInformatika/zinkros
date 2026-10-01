<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->boolean('show_pricing')->default(true)->after('footer_medsos');
            $table->string('pricing_heading', 150)->default(config('platform.pricing_heading', ''))->after('show_pricing');
            $table->string('pricing_subtitle', 300)->default(config('platform.pricing_subtitle', ''))->after('pricing_heading');
            $table->longText('pricing_plans')->nullable()->after('pricing_subtitle');
        });

        $defaults = config('platform.pricing_defaults', []);
        if ($defaults !== []) {
            DB::table('platform_settings')
                ->whereNull('pricing_plans')
                ->update(['pricing_plans' => json_encode($defaults)]);
        }
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn([
                'show_pricing',
                'pricing_heading',
                'pricing_subtitle',
                'pricing_plans',
            ]);
        });
    }
};