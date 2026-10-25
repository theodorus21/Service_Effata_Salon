<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'learning_class_lesson_id',
    'old_lesson_date',
    'old_lesson_time',
    'new_lesson_date',
    'new_lesson_time',
    'reason',
    'old_teacher_id',
    'new_teacher_id',
    'updated_by',
])]
class LearningClassLessonRescheduleHistory extends Model
{
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(LearningClassLesson::class, 'learning_class_lesson_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function oldTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_teacher_id');
    }

    public function newTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_teacher_id');
    }
}
