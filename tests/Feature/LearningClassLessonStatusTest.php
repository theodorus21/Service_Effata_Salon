<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LearningClassLessonStatus;
use App\Models\LookupValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class LearningClassLessonStatusTest extends TestCase
{
    use RefreshDatabase;
    use AuthenticatesAsMasterAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_default_status_is_zero_and_shown_in_red(): void
    {
        $status = LearningClassLessonStatus::query()->findOrFail(0);

        $this->assertSame('Belum terlaksana', $status->label);
        $this->assertSame('#dc3545', $status->color);

        $class = $this->createClass();
        $class->lessons()->create([
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
        ]);

        $this->assertDatabaseHas('learning_class_lessons', [
            'learning_class_id' => $class->id,
            'status' => 0,
        ]);

        $this->get(route('class-schedules.show', [
            'learningClass' => $class,
            'month' => 10,
            'year' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Belum terlaksana')
            ->assertSee('--lesson-status-color: #dc3545', false);
    }

    public function test_status_can_be_added_and_removed_from_settings_when_unused(): void
    {
        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Status Jadwal Les')
            ->assertSee('Belum terlaksana');

        $this->post(route('settings.lesson-statuses.store'), [
            'label' => 'Terlaksana',
            'color' => '#198754',
            'sort_order' => 1,
        ])->assertRedirect(route('settings.index'));

        $status = LearningClassLessonStatus::query()->where('label', 'Terlaksana')->firstOrFail();
        $this->assertSame('Terlaksana', $status->label);

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Terlaksana')
            ->assertSee('#198754');

        $this->delete(route('settings.lesson-statuses.destroy', $status))
            ->assertRedirect(route('settings.index'));

        $this->assertDatabaseMissing('learning_class_lesson_statuses', ['code' => $status->code]);
    }

    public function test_default_status_cannot_be_deleted_and_used_status_is_protected(): void
    {
        $this->delete(route('settings.lesson-statuses.destroy', 0))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors('lesson_status');

        $nextCode = (int) LearningClassLessonStatus::query()->max('code') + 1;
        $status = LearningClassLessonStatus::query()->create([
            'code' => $nextCode,
            'label' => 'Terlaksana',
            'color' => '#198754',
            'sort_order' => 1,
        ]);
        $class = $this->createClass();
        $class->lessons()->create([
            'lesson_date' => '2026-10-05',
            'lesson_time' => '09:30',
            'status' => $status->code,
        ]);

        $this->delete(route('settings.lesson-statuses.destroy', $status))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors('lesson_status');

        $this->assertDatabaseHas('learning_class_lesson_statuses', ['code' => $nextCode]);
    }

    private function createClass(): LearningClass
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'murid']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $category = LookupValue::query()->where('category', 'class_category')->firstOrFail();

        return LearningClass::query()->create([
            'name' => 'Kelas Status',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'product_id' => \Tests\Support\ProductFixture::make($program->id, $category->id)->id,
        ]);
    }
}
