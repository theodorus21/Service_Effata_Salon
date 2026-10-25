<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            if (! Schema::hasColumn('learning_class_lessons', 'reason')) {
                $table->text('reason')->nullable();
            }
            if (! Schema::hasColumn('learning_class_lessons', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('learning_class_id');
            }
        });

        Schema::table('learning_class_lesson_reschedule_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('learning_class_lesson_reschedule_histories', 'reason')) {
                $table->text('reason')->nullable();
            }
            if (! Schema::hasColumn('learning_class_lesson_reschedule_histories', 'old_teacher_id')) {
                $table->foreignId('old_teacher_id')->nullable();
            }
            if (! Schema::hasColumn('learning_class_lesson_reschedule_histories', 'new_teacher_id')) {
                $table->foreignId('new_teacher_id')->nullable();
            }
        });

        $this->addForeignKeyIfMissing('learning_class_lessons', 'teacher_id', 'lcll_teacher_fk');
        $this->addForeignKeyIfMissing('learning_class_lesson_reschedule_histories', 'old_teacher_id', 'lclrh_old_teacher_fk');
        $this->addForeignKeyIfMissing('learning_class_lesson_reschedule_histories', 'new_teacher_id', 'lclrh_new_teacher_fk');

        $cancelledStatus = DB::table('learning_class_lesson_statuses')
            ->where('system_key', 'dibatalkan')
            ->value('code');

        if ($cancelledStatus !== null) {
            DB::table('learning_class_lessons')
                ->where('status', $cancelledStatus)
                ->whereNull('reason')
                ->update(['reason' => DB::raw('lesson_description')]);
        }
    }

    public function down(): void
    {
        $this->dropForeignKeyIfPresent('learning_class_lesson_reschedule_histories', 'old_teacher_id');
        $this->dropForeignKeyIfPresent('learning_class_lesson_reschedule_histories', 'new_teacher_id');
        $this->dropForeignKeyIfPresent('learning_class_lessons', 'teacher_id');

        Schema::table('learning_class_lesson_reschedule_histories', function (Blueprint $table): void {
            $columns = array_filter(
                ['reason', 'old_teacher_id', 'new_teacher_id'],
                fn (string $column): bool => Schema::hasColumn('learning_class_lesson_reschedule_histories', $column),
            );
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('learning_class_lessons', function (Blueprint $table): void {
            $columns = array_filter(
                ['reason', 'teacher_id'],
                fn (string $column): bool => Schema::hasColumn('learning_class_lessons', $column),
            );
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    private function addForeignKeyIfMissing(string $tableName, string $column, string $foreignKeyName): void
    {
        $exists = collect(Schema::getForeignKeys($tableName))
            ->contains(fn (array $foreignKey): bool => in_array($column, $foreignKey['columns'], true));

        if ($exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $foreignKeyName): void {
            $table->foreign($column, $foreignKeyName)
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    private function dropForeignKeyIfPresent(string $tableName, string $column): void
    {
        $foreignKey = collect(Schema::getForeignKeys($tableName))
            ->first(fn (array $foreignKey): bool => in_array($column, $foreignKey['columns'], true));

        if ($foreignKey !== null) {
            Schema::table($tableName, function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['name']);
            });
        }
    }
};
