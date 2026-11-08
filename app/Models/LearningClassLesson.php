<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'learning_class_id',
    'teacher_id',
    'lesson_date',
    'lesson_time',
    'duration_minutes',
    'status',
    'teacher_attendance_at',
    'reason',
])]
class LearningClassLesson extends Model
{
    protected static function booted(): void
    {
        static::created(function (LearningClassLesson $lesson): void {
            $studentIds = $lesson->learningClass
                ->students()
                ->pluck('users.id');

            $lesson->studentRecords()->createMany(
                $studentIds->map(fn (int $studentId): array => ['student_id' => $studentId])->all(),
            );
        });
    }

    public function learningClass(): BelongsTo
    {
        return $this->belongsTo(LearningClass::class);
    }

    public function lessonStatus(): BelongsTo
    {
        return $this->belongsTo(LearningClassLessonStatus::class, 'status', 'code');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function rescheduleHistories(): HasMany
    {
        return $this->hasMany(LearningClassLessonRescheduleHistory::class);
    }

    public function studentRecords(): HasMany
    {
        return $this->hasMany(LearningClassLessonStudent::class, 'learning_class_lesson_id');
    }
}
