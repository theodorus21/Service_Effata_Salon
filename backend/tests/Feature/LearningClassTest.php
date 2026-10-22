<?php

namespace Tests\Feature;

use App\Models\LearningClass;
use App\Models\LookupValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class LearningClassTest extends TestCase
{
    use RefreshDatabase;
    use AuthenticatesAsMasterAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_dashboard_and_sidebar_link_to_classes_and_settings(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('learning-classes.index'), false)
            ->assertSee(route('settings.index'), false);

        $this->get(route('learning-classes.index'))
            ->assertOk()
            ->assertSee('Daftar Kelas')
            ->assertSee(route('learning-classes.create'), false);
    }

    public function test_class_form_lists_categories_from_lookup_values(): void
    {
        $this->get(route('learning-classes.create'))
            ->assertOk()
            ->assertSee('Pilih kategori')
            ->assertSee('Pelajaran')
            ->assertSee('Musik');
    }

    public function test_class_can_be_created_updated_and_deleted(): void
    {
        $student = User::factory()->create(['name' => 'Murid Kelas', 'role' => 'murid']);
        $teacher = User::factory()->create(['name' => 'Guru Kelas', 'role' => 'guru']);
        $program = LookupValue::query()->where('value', 'Home Programme')->firstOrFail();
        $category = LookupValue::query()
            ->where('category', 'class_category')
            ->where('value', 'Pelajaran')
            ->firstOrFail();

        $this->post(route('learning-classes.store'), [
            'name' => 'Kelas Ceria',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
            'schedules' => [
                ['day' => 'senin', 'lesson_time' => '09:30', 'duration_minutes' => 60],
                ['day' => 'rabu', 'lesson_time' => '10:30', 'duration_minutes' => 45],
            ],
        ])->assertRedirect(route('learning-classes.index'));

        $learningClass = LearningClass::query()->firstOrFail();
        $this->assertCount(2, $learningClass->schedules);

        $this->get(route('learning-classes.index'))
            ->assertOk()
            ->assertSee('Kelas Ceria')
            ->assertSee('Murid Kelas')
            ->assertSee('Guru Kelas')
            ->assertSee('Home Programme')
            ->assertSee('Pelajaran')
            ->assertSee('Senin')
            ->assertSee('Rabu')
            ->assertSee(route('learning-classes.show', $learningClass), false);

        $this->get(route('learning-classes.show', $learningClass))
            ->assertOk()
            ->assertSee('Detail Kelas')
            ->assertSee('Kelas Ceria')
            ->assertSee('Murid Kelas')
            ->assertSee('Guru Kelas')
            ->assertSee('Home Programme')
            ->assertSee('Pelajaran')
            ->assertSee('Senin')
            ->assertSee('Rabu')
            ->assertSee('09:30')
            ->assertSee('60 menit')
            ->assertSee('10:30')
            ->assertSee('45 menit')
            ->assertSee(route('learning-classes.edit', $learningClass), false);

        $this->put(route('learning-classes.update', $learningClass), [
            'name' => 'Kelas Ceria Baru',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => LookupValue::query()
                ->where('category', 'class_category')
                ->where('value', 'Musik')
                ->value('id'),
            'schedules' => [
                ['day' => 'sabtu', 'lesson_time' => '13:15', 'duration_minutes' => 45],
            ],
        ])->assertRedirect(route('learning-classes.index'));

        $learningClass->refresh();
        $this->assertSame('Kelas Ceria Baru', $learningClass->name);
        $this->assertSame('Musik', $learningClass->category->value);
        $this->assertCount(1, $learningClass->schedules);
        $this->assertSame('sabtu', $learningClass->schedules->first()->day);
        $this->assertSame('13:15', substr($learningClass->schedules->first()->lesson_time, 0, 5));
        $this->assertSame(45, $learningClass->schedules->first()->duration_minutes);

        $this->delete(route('learning-classes.destroy', $learningClass))
            ->assertRedirect(route('learning-classes.index'));
        $this->assertDatabaseMissing('learning_classes', ['id' => $learningClass->id]);
    }

    public function test_class_rejects_users_with_incorrect_roles_and_invalid_day(): void
    {
        $student = User::factory()->create(['role' => 'murid']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $program = LookupValue::query()->where('value', 'Home Programme')->firstOrFail();
        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), [
                'name' => 'Kelas Tidak Valid',
                'student_id' => $teacher->id,
                'teacher_id' => $student->id,
                'program_id' => $program->id,
                'category_id' => $program->id,
                'schedules' => [
                    ['day' => 'minggu', 'lesson_time' => '09:30', 'duration_minutes' => 60],
                ],
            ])
            ->assertRedirect(route('learning-classes.create'))
            ->assertSessionHasErrors(['student_id', 'teacher_id', 'category_id', 'schedules.0.day']);

        $this->assertDatabaseCount('learning_classes', 0);
    }

    public function test_class_rejects_overlapping_schedules_in_the_same_submission(): void
    {
        $student = User::factory()->create(['role' => 'murid']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $category = LookupValue::query()->where('category', 'class_category')->firstOrFail();

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), [
                'name' => 'Kelas Jadwal Bentrok',
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'program_id' => $program->id,
                'category_id' => $category->id,
                'schedules' => [
                    ['day' => 'senin', 'lesson_time' => '09:00', 'duration_minutes' => 60],
                    ['day' => 'senin', 'lesson_time' => '09:59', 'duration_minutes' => 30],
                ],
            ])
            ->assertRedirect(route('learning-classes.create'))
            ->assertSessionHasErrors('schedules.1.lesson_time');

        $this->assertDatabaseCount('learning_classes', 0);
    }

    public function test_class_rejects_overlap_with_another_class_sharing_student_or_teacher(): void
    {
        $student = User::factory()->create(['role' => 'murid']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $otherStudent = User::factory()->create(['role' => 'murid']);
        $otherTeacher = User::factory()->create(['role' => 'guru']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $category = LookupValue::query()->where('category', 'class_category')->firstOrFail();

        $existingClass = LearningClass::query()->create([
            'name' => 'Kelas Sudah Ada',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
        ]);
        $existingClass->schedules()->create([
            'day' => 'senin',
            'lesson_time' => '09:00',
            'duration_minutes' => 60,
        ]);

        $payload = [
            'name' => 'Kelas Baru',
            'student_id' => $student->id,
            'teacher_id' => $otherTeacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
            'schedules' => [
                ['day' => 'senin', 'lesson_time' => '09:30', 'duration_minutes' => 30],
            ],
        ];

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $payload)
            ->assertRedirect(route('learning-classes.create'))
            ->assertSessionHasErrors('schedules.0.lesson_time');

        $payload['student_id'] = $otherStudent->id;
        $payload['teacher_id'] = $teacher->id;

        $this->from(route('learning-classes.create'))
            ->post(route('learning-classes.store'), $payload)
            ->assertRedirect(route('learning-classes.create'))
            ->assertSessionHasErrors('schedules.0.lesson_time');

        $this->assertDatabaseCount('learning_classes', 1);
    }

    public function test_adjacent_schedules_do_not_overlap_and_updates_check_conflicts(): void
    {
        $student = User::factory()->create(['role' => 'murid']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $category = LookupValue::query()->where('category', 'class_category')->firstOrFail();
        $learningClass = LearningClass::query()->create([
            'name' => 'Kelas Jadwal',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
        ]);
        $learningClass->schedules()->create([
            'day' => 'selasa',
            'lesson_time' => '09:00',
            'duration_minutes' => 60,
        ]);

        $this->post(route('learning-classes.store'), [
            'name' => 'Kelas Berurutan',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
            'schedules' => [
                ['day' => 'selasa', 'lesson_time' => '10:00', 'duration_minutes' => 30],
            ],
        ])->assertRedirect(route('learning-classes.index'));

        $this->from(route('learning-classes.edit', $learningClass))
            ->put(route('learning-classes.update', $learningClass), [
                'name' => 'Kelas Jadwal',
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'program_id' => $program->id,
                'category_id' => $category->id,
                'schedules' => [
                    ['day' => 'selasa', 'lesson_time' => '10:15', 'duration_minutes' => 30],
                ],
            ])
            ->assertRedirect(route('learning-classes.edit', $learningClass))
            ->assertSessionHasErrors('schedules.0.lesson_time');

        $learningClass->refresh();
        $this->assertSame('09:00', substr($learningClass->schedules->first()->lesson_time, 0, 5));
    }

    public function test_program_choices_can_be_managed_and_not_deleted_while_in_use(): void
    {
        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Home Programme')
            ->assertSee('Center Meruya')
            ->assertSee('Center Interkota');

        $this->post(route('settings.programs.store'), [
            'value' => 'Program Baru',
            'sort_order' => 4,
        ])->assertRedirect(route('settings.index'));

        $program = LookupValue::query()->where('value', 'Program Baru')->firstOrFail();
        $student = User::factory()->create(['role' => 'murid']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $category = LookupValue::query()
            ->where('category', 'class_category')
            ->where('value', 'Pelajaran')
            ->firstOrFail();
        $learningClass = LearningClass::query()->create([
            'name' => 'Kelas Program Baru',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
        ]);
        $learningClass->schedules()->create([
            'day' => 'rabu',
            'lesson_time' => '10:00',
            'duration_minutes' => 30,
        ]);

        $this->delete(route('settings.programs.destroy', $program))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors('program');
        $this->assertDatabaseHas('lookup_values', ['id' => $program->id]);

        $this->put(route('settings.programs.update', $program), [
            'value' => 'Program Diperbarui',
            'sort_order' => 5,
        ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('learning_classes', [
            'id' => $learningClass->id,
            'program_id' => $program->id,
        ]);

        $learningClass->delete();
        $this->delete(route('settings.programs.destroy', $program))
            ->assertRedirect(route('settings.index'));
        $this->assertDatabaseMissing('lookup_values', ['id' => $program->id]);
    }

    public function test_class_categories_can_be_managed_and_not_deleted_while_in_use(): void
    {
        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Kategori Kelas')
            ->assertSee('Pelajaran')
            ->assertSee('Musik')
            ->assertSee(route('settings.class-categories.create'), false);

        $this->post(route('settings.class-categories.store'), [
            'value' => 'Seni',
            'sort_order' => 3,
        ])->assertRedirect(route('settings.index'));

        $newCategory = LookupValue::query()
            ->where('category', 'class_category')
            ->where('value', 'Seni')
            ->firstOrFail();

        $student = User::factory()->create(['role' => 'murid']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $program = LookupValue::query()->where('category', 'program')->firstOrFail();
        $pelajaran = LookupValue::query()
            ->where('category', 'class_category')
            ->where('value', 'Pelajaran')
            ->firstOrFail();
        $learningClass = LearningClass::query()->create([
            'name' => 'Kelas Kategori',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $pelajaran->id,
        ]);
        $learningClass->schedules()->create([
            'day' => 'senin',
            'lesson_time' => '09:00',
            'duration_minutes' => 45,
        ]);

        $this->delete(route('settings.class-categories.destroy', $pelajaran))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors('class_category');
        $this->assertDatabaseHas('lookup_values', ['id' => $pelajaran->id]);

        $this->put(route('settings.class-categories.update', $newCategory), [
            'value' => 'Seni Rupa',
            'sort_order' => 4,
        ])->assertRedirect(route('settings.index'));
        $this->assertDatabaseHas('lookup_values', [
            'id' => $newCategory->id,
            'category' => 'class_category',
            'value' => 'Seni Rupa',
            'sort_order' => 4,
        ]);

        $this->delete(route('settings.class-categories.destroy', $newCategory))
            ->assertRedirect(route('settings.index'));
        $this->assertDatabaseMissing('lookup_values', ['id' => $newCategory->id]);
        $this->assertDatabaseHas('learning_classes', ['id' => $learningClass->id]);
    }

    public function test_student_or_teacher_in_use_cannot_be_deleted(): void
    {
        $student = User::factory()->create(['role' => 'murid']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $program = LookupValue::query()->where('value', 'Home Programme')->firstOrFail();
        $category = LookupValue::query()
            ->where('category', 'class_category')
            ->where('value', 'Pelajaran')
            ->firstOrFail();
        LearningClass::query()->create([
            'name' => 'Kelas Terpakai',
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'program_id' => $program->id,
            'category_id' => $category->id,
        ]);
        $learningClass = LearningClass::query()->latest('id')->firstOrFail();
        $learningClass->schedules()->create([
            'day' => 'kamis',
            'lesson_time' => '14:00',
            'duration_minutes' => 30,
        ]);

        $this->delete(route('master-data.destroy', ['role' => 'guru', 'user' => $teacher->id]))
            ->assertRedirect(route('master-data.index', ['role' => 'guru']))
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $teacher->id]);

        $this->delete(route('master-data.destroy', ['role' => 'murid', 'user' => $student->id]))
            ->assertRedirect(route('master-data.index', ['role' => 'murid']))
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }
}
