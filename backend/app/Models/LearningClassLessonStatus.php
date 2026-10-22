<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'label', 'color', 'sort_order', 'system_key'])]
class LearningClassLessonStatus extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'int';

    public function lessons(): HasMany
    {
        return $this->hasMany(LearningClassLesson::class, 'status', 'code');
    }
}
