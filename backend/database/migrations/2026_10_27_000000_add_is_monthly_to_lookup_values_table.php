<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lookup_values', function (Blueprint $table) {
            $table->boolean('is_monthly')->default(false)->after('sort_order');
        });

        DB::table('lookup_values')
            ->where('category', 'class_frequency')
            ->whereRaw('LOWER(TRIM(value)) = ?', ['bulanan'])
            ->update(['is_monthly' => true]);
    }

    public function down(): void
    {
        Schema::table('lookup_values', function (Blueprint $table) {
            $table->dropColumn('is_monthly');
        });
    }
};
