<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_class_lesson_statuses', function (Blueprint $table): void {
            $table->unsignedSmallInteger('code')->primary();
            $table->string('label', 100)->unique();
            $table->string('color', 7)->default('#dc3545');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('learning_class_lesson_statuses')->insert([
            'code' => 0,
            'label' => 'Belum terlaksana',
            'color' => '#dc3545',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->unsignedSmallInteger('status_code')->default(0)->after('lesson_time');
        });

        DB::table('learning_class_lessons')->update(['status_code' => 0]);

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->dropColumn('status');
        });

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->renameColumn('status_code', 'status');
        });

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->foreign('status', 'learning_class_lessons_status_foreign')
                ->references('code')
                ->on('learning_class_lesson_statuses')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->dropForeign('learning_class_lessons_status_foreign');
            $table->dropColumn('status');
        });

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $table->string('status', 30)->default('belum_terlaksana')->after('lesson_time');
        });

        Schema::dropIfExists('learning_class_lesson_statuses');
    }
};
