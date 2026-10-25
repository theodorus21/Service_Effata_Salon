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
    'lesson_photo',
    'lesson_description',
    'reason',
    'notes',
])]
class LearningClassLesson extends Model
{
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
}
