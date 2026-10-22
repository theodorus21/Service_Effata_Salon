<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('learning_class_lesson_statuses', 'system_key')) {
            Schema::table('learning_class_lesson_statuses', function (Blueprint $table): void {
                $table->string('system_key', 40)->nullable()->unique();
            });
        }

        DB::table('learning_class_lesson_statuses')
            ->where('code', 0)
            ->update(['system_key' => 'belum_terlaksana']);

        $nextCode = (int) DB::table('learning_class_lesson_statuses')->max('code') + 1;
        $now = now();

        foreach ([
            ['system_key' => 'sedang_berlangsung', 'label' => 'Sedang Berlangsung', 'color' => '#198754'],
            ['system_key' => 'dibatalkan', 'label' => 'Dibatalkan', 'color' => '#dc3545'],
        ] as $index => $status) {
            $existingStatus = DB::table('learning_class_lesson_statuses')
                ->where('label', $status['label'])
                ->first();

            if ($existingStatus) {
                DB::table('learning_class_lesson_statuses')
                    ->where('code', $existingStatus->code)
                    ->update([
                        'system_key' => $status['system_key'],
                        'color' => $status['color'],
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('learning_class_lesson_statuses')->insert([
                'code' => $nextCode + $index,
                ...$status,
                'sort_order' => $nextCode + $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! Schema::hasTable('learning_class_lesson_reschedule_histories')) {
            Schema::create('learning_class_lesson_reschedule_histories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('learning_class_lesson_id');
                $table->date('old_lesson_date');
                $table->time('old_lesson_time');
                $table->date('new_lesson_date');
                $table->time('new_lesson_time');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->index(['learning_class_lesson_id', 'created_at'], 'lclrh_lesson_created_idx');
            });
        }

        if (! Schema::hasIndex('learning_class_lesson_reschedule_histories', 'lclrh_lesson_created_idx')) {
            Schema::table('learning_class_lesson_reschedule_histories', function (Blueprint $table): void {
                $table->index(['learning_class_lesson_id', 'created_at'], 'lclrh_lesson_created_idx');
            });
        }

        Schema::table('learning_class_lesson_reschedule_histories', function (Blueprint $table): void {
            $table->foreign('learning_class_lesson_id', 'lclrh_lesson_fk')
                ->references('id')
                ->on('learning_class_lessons')
                ->cascadeOnDelete();
            $table->foreign('updated_by', 'lclrh_updated_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_class_lesson_reschedule_histories');

        $defaultCode = DB::table('learning_class_lesson_statuses')
            ->where('system_key', 'belum_terlaksana')
            ->value('code');
        $actionStatusCodes = DB::table('learning_class_lesson_statuses')
            ->whereIn('system_key', ['sedang_berlangsung', 'dibatalkan'])
            ->pluck('code');

        if ($defaultCode !== null && $actionStatusCodes->isNotEmpty()) {
            DB::table('learning_class_lessons')
                ->whereIn('status', $actionStatusCodes)
                ->update(['status' => $defaultCode]);
        }

        DB::table('learning_class_lesson_statuses')
            ->whereIn('code', $actionStatusCodes)
            ->delete();

        DB::table('learning_class_lesson_statuses')
            ->whereIn('system_key', ['belum_terlaksana', 'sedang_berlangsung', 'dibatalkan'])
            ->update(['system_key' => null]);

        Schema::table('learning_class_lesson_statuses', function (Blueprint $table): void {
            $table->dropUnique(['system_key']);
            $table->dropColumn('system_key');
        });
    }
};
