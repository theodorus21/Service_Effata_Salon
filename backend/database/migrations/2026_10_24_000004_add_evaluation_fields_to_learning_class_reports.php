<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_class_reports', function (Blueprint $table) {
            $table->longText('development_evaluation')->nullable();
            $table->longText('additional_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('learning_class_reports', function (Blueprint $table) {
            $table->dropColumn(['development_evaluation', 'additional_notes']);
        });
    }
};
