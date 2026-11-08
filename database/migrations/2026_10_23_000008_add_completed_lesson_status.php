<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $completedStatus = DB::table('learning_class_lesson_statuses')
            ->where('system_key', 'terlaksana')
            ->first();

        if ($completedStatus) {
            return;
        }

        $existingLabel = DB::table('learning_class_lesson_statuses')
            ->where('label', 'Terlaksana')
            ->first();

        if (! $existingLabel) {
            return;
        }

        DB::table('learning_class_lesson_statuses')
            ->where('code', $existingLabel->code)
            ->update([
                'system_key' => 'terlaksana',
                'color' => '#198754',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('learning_class_lesson_statuses')
            ->where('system_key', 'terlaksana')
            ->update(['system_key' => null]);
    }
};
