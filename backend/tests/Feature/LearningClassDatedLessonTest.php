<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LearningClassLesson;
use App\Models\LearningClassLessonStatus;
use App\Models\LookupValue;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\Support\ProductFixture;
use Tests\TestCase;

class LearningClassDatedLessonTest extends TestCase
{
    use AuthenticatesAsMasterAdmin;
    use RefreshDatabase;

    private User $student;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();

        $this->student = User::factory()->create(['role' => 'murid']);
        $this->teacher = User::factory()->create(['role' => 'guru']);
    }

    private function product(bool $monthly, string $category = 'Pelajaran'): Product
    {
        $program = LookupValue::query()->where('value', 'Home Programme')->firstOrFail();
        $categoryId = LookupValue::query()->where('category', 'class_category')->where('value', $category)->value('id');

        return ProductFixture::make($program->id, $categoryId, $monthly);
    }

    private function payload(int $productId, array $extra = [], ?User $teacher = null, ?User $student = null): array
    {
        return [
            'student_id' => ($student ?? $this->student)->id,
            'teacher_id' => ($teacher ?? $this->teacher)->id,
            'product_id' => $productId,
            ...$extra,
        ];
    }

    private function futureDate(int $days = 7): string
    {
        return CarbonImmutable::today()->addDays($days)->toDateString();
    }

    public function test_dated_class_saves_lessons_with_product_duration_and_no_weekly_schedule(): void
    {
        $product = $this->product(monthly: false);

        $this->post(route('learning-classes.store'), $this->payload($product->id, [
            'lessons' => [
                ['lesson_date' => $this->futureDate(7), 'lesson_time' => '09:00'],
                ['lesson_date' => $this->futureDate(14), 'lesson_time' => '10:00'],
            ],
        ]))->assertRedirect(route('learning-classes.index'));

        $learningClass = LearningClass::query()->firstOrFail();
        $this->assertCount(0, $learningClass->schedules);
        $this->assertCount(2, $learningClass->lessons);
        $this->assertSame(60, (int) $learningClass->lessons->first()->duration_minutes);
        $this->assertSame($this->teacher->id, $learningClass->lessons->first()->teacher_id);
    }

    public function test_dated_class_rejects_past_dates(): void
    {
        $product = $this->product(monthly: false);

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $this->payload($product->id, [
                'lessons' => [
                    ['lesson_date' => CarbonImmutable::yesterday()->toDateString(), 'lesson_time' => '09:00'],
                ],
            ]))
            ->assertSessionHasErrors(['lessons.0.lesson_date']);

        $this->assertDatabaseCount('learning_classes', 0);
    }

    public function test_monthly_class_requires_weekly_schedules_and_ignores_dated_lessons(): void
    {
        $product = $this->product(monthly: true);

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $this->payload($product->id, [
                'lessons' => [
                    ['lesson_date' => $this->futureDate(), 'lesson_time' => '09:00'],
                ],
            ]))
            ->assertSessionHasErrors(['schedules']);

        $this->assertDatabaseCount('learning_classes', 0);
    }

    public function test_dated_class_rejects_duplicate_dates_within_the_same_submission(): void
    {
        $product = $this->product(monthly: false);
        $date = $this->futureDate();

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $this->payload($product->id, [
                'lessons' => [
                    ['lesson_date' => $date, 'lesson_time' => '09:00'],
                    ['lesson_date' => $date, 'lesson_time' => '09:30'],
                ],
            ]))
            ->assertSessionHasErrors(['lessons.1.lesson_time']);

        $this->assertDatabaseCount('learning_classes', 0);
    }

    public function test_dated_lesson_conflicts_with_another_class_sharing_the_student(): void
    {
        $product = $this->product(monthly: false);
        $date = $this->futureDate();

        $this->post(route('learning-classes.store'), $this->payload($product->id, [
            'lessons' => [['lesson_date' => $date, 'lesson_time' => '09:00']],
        ]))->assertRedirect();

        $otherTeacher = User::factory()->create(['role' => 'guru']);

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $this->payload($product->id, [
                'lessons' => [['lesson_date' => $date, 'lesson_time' => '09:30']],
            ], teacher: $otherTeacher))
            ->assertSessionHasErrors(['lessons.0.lesson_time']);

        $this->assertDatabaseCount('learning_classes', 1);
    }

    public function test_cancelled_lesson_does_not_block_a_new_class_at_the_same_time(): void
    {
        $product = $this->product(monthly: false);
        $date = $this->futureDate();

        $this->post(route('learning-classes.store'), $this->payload($product->id, [
            'lessons' => [['lesson_date' => $date, 'lesson_time' => '09:00']],
        ]))->assertRedirect();

        $cancelledCode = LearningClassLessonStatus::query()->where('system_key', 'dibatalkan')->value('code');
        LearningClassLesson::query()->update(['status' => $cancelledCode]);

        $otherTeacher = User::factory()->create(['role' => 'guru']);

        $this->post(route('learning-classes.store'), $this->payload($product->id, [
            'lessons' => [['lesson_date' => $date, 'lesson_time' => '09:00']],
        ], teacher: $otherTeacher))->assertRedirect();

        $this->assertDatabaseCount('learning_classes', 2);
    }

    public function test_adjacent_dated_lessons_are_allowed(): void
    {
        $product = $this->product(monthly: false);
        $date = $this->futureDate();

        $this->post(route('learning-classes.store'), $this->payload($product->id, [
            'lessons' => [['lesson_date' => $date, 'lesson_time' => '09:00']],
        ]))->assertRedirect();

        $otherTeacher = User::factory()->create(['role' => 'guru']);

        $this->post(route('learning-classes.store'), $this->payload($product->id, [
            'lessons' => [['lesson_date' => $date, 'lesson_time' => '10:00']],
        ], teacher: $otherTeacher))->assertRedirect();

        $this->assertDatabaseCount('learning_classes', 2);
    }

    public function test_dated_lesson_conflicts_with_weekly_schedule_of_a_shared_teacher(): void
    {
        $monthly = $this->product(monthly: true);
        $dated = $this->product(monthly: false);
        $monday = CarbonImmutable::today()->addWeek()->next(CarbonImmutable::MONDAY)->toDateString();

        $this->post(route('learning-classes.store'), $this->payload($monthly->id, [
            'schedules' => [['day' => 'senin', 'lesson_time' => '09:00']],
        ]))->assertRedirect();

        $otherStudent = User::factory()->create(['role' => 'murid']);

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $this->payload($dated->id, [
                'lessons' => [['lesson_date' => $monday, 'lesson_time' => '09:30']],
            ], student: $otherStudent))
            ->assertSessionHasErrors(['lessons.0.lesson_time']);

        $this->assertDatabaseCount('learning_classes', 1);
    }

    public function test_weekly_schedule_conflicts_with_existing_dated_lesson(): void
    {
        $monthly = $this->product(monthly: true);
        $dated = $this->product(monthly: false);
        $monday = CarbonImmutable::today()->addWeek()->next(CarbonImmutable::MONDAY)->toDateString();

        $this->post(route('learning-classes.store'), $this->payload($dated->id, [
            'lessons' => [['lesson_date' => $monday, 'lesson_time' => '09:00']],
        ]))->assertRedirect();

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $this->payload($monthly->id, [
                'schedules' => [['day' => 'senin', 'lesson_time' => '09:15']],
            ]))
            ->assertSessionHasErrors(['schedules.0.lesson_time']);

        $this->assertDatabaseCount('learning_classes', 1);
    }

    public function test_generate_is_blocked_for_dated_classes(): void
    {
        $product = $this->product(monthly: false);

        $this->post(route('learning-classes.store'), $this->payload($product->id, [
            'lessons' => [['lesson_date' => $this->futureDate(), 'lesson_time' => '09:00']],
        ]))->assertRedirect();

        $learningClass = LearningClass::query()->firstOrFail();

        $this->post(route('learning-classes.lessons.generate', $learningClass), [
            'month' => now()->month,
            'year' => now()->year,
        ])->assertStatus(422);

        $this->assertCount(1, LearningClassLesson::query()->where('learning_class_id', $learningClass->id)->get());
    }
}
