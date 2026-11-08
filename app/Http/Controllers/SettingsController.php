<?php

namespace App\Http\Controllers;

use App\Models\LearningClassLessonStatus;
use App\Models\LookupValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'programs' => LookupValue::query()
                ->where('category', 'program')
                ->withCount('learningClasses')
                ->orderBy('sort_order')
                ->orderBy('value')
                ->paginate(10),
            'classCategories' => LookupValue::query()
                ->where('category', 'class_category')
                ->withCount('categorizedClasses')
                ->orderBy('sort_order')
                ->orderBy('value')
                ->get(),
            'classLevels' => LookupValue::query()
                ->where('category', 'class_level')
                ->withCount('levelProducts')
                ->orderBy('sort_order')
                ->orderBy('value')
                ->get(),
            'classFrequencies' => LookupValue::query()
                ->where('category', 'class_frequency')
                ->withCount('frequencyProducts')
                ->orderBy('sort_order')
                ->orderBy('value')
                ->get(),
            'lessonStatuses' => LearningClassLessonStatus::query()
                ->withCount('lessons')
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('settings.program-form', ['program' => new LookupValue]);
    }

    public function store(Request $request): RedirectResponse
    {
        LookupValue::create([
            ...$this->validatedData($request),
            'category' => 'program',
        ]);

        return redirect()
            ->route('settings.index')
            ->with('status', 'Program berhasil ditambahkan.');
    }

    public function edit(LookupValue $program): View
    {
        abort_unless($program->category === 'program', 404);

        return view('settings.program-form', ['program' => $program]);
    }

    public function update(Request $request, LookupValue $program): RedirectResponse
    {
        abort_unless($program->category === 'program', 404);
        $program->update($this->validatedData($request, $program));

        return redirect()
            ->route('settings.index')
            ->with('status', 'Program berhasil diperbarui.');
    }

    public function destroy(LookupValue $program): RedirectResponse
    {
        abort_unless($program->category === 'program', 404);

        if ($program->learningClasses()->exists() || $program->programProducts()->exists()) {
            return redirect()
                ->route('settings.index')
                ->withErrors(['program' => 'Program tidak dapat dihapus karena masih digunakan oleh kelas atau produk.']);
        }

        $program->delete();

        return redirect()
            ->route('settings.index')
            ->with('status', 'Program berhasil dihapus.');
    }

    public function createCategory(): View
    {
        return view('settings.category-form', ['classCategory' => new LookupValue]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        LookupValue::create([
            ...$this->validatedCategoryData($request),
            'category' => 'class_category',
        ]);

        return redirect()
            ->route('settings.index')
            ->with('status', 'Kategori kelas berhasil ditambahkan.');
    }

    public function editCategory(LookupValue $classCategory): View
    {
        abort_unless($classCategory->category === 'class_category', 404);

        return view('settings.category-form', compact('classCategory'));
    }

    public function updateCategory(Request $request, LookupValue $classCategory): RedirectResponse
    {
        abort_unless($classCategory->category === 'class_category', 404);
        $classCategory->update($this->validatedCategoryData($request, $classCategory));

        return redirect()
            ->route('settings.index')
            ->with('status', 'Kategori kelas berhasil diperbarui.');
    }

    public function destroyCategory(LookupValue $classCategory): RedirectResponse
    {
        abort_unless($classCategory->category === 'class_category', 404);

        if ($classCategory->categorizedClasses()->exists() || $classCategory->categoryProducts()->exists()) {
            return redirect()
                ->route('settings.index')
                ->withErrors(['class_category' => 'Kategori tidak dapat dihapus karena masih digunakan oleh kelas atau produk.']);
        }

        $classCategory->delete();

        return redirect()
            ->route('settings.index')
            ->with('status', 'Kategori kelas berhasil dihapus.');
    }

    public function createLevel(): View
    {
        return view('settings.level-form', ['classLevel' => new LookupValue]);
    }

    public function storeLevel(Request $request): RedirectResponse
    {
        LookupValue::create([
            ...$this->validatedLookupData($request, 'class_level'),
            'category' => 'class_level',
        ]);

        return redirect()
            ->route('settings.index')
            ->with('status', 'Level kelas berhasil ditambahkan.');
    }

    public function editLevel(LookupValue $classLevel): View
    {
        abort_unless($classLevel->category === 'class_level', 404);

        return view('settings.level-form', compact('classLevel'));
    }

    public function updateLevel(Request $request, LookupValue $classLevel): RedirectResponse
    {
        abort_unless($classLevel->category === 'class_level', 404);
        $classLevel->update($this->validatedLookupData($request, 'class_level', $classLevel));

        return redirect()
            ->route('settings.index')
            ->with('status', 'Level kelas berhasil diperbarui.');
    }

    public function destroyLevel(LookupValue $classLevel): RedirectResponse
    {
        abort_unless($classLevel->category === 'class_level', 404);

        if ($classLevel->levelProducts()->exists()) {
            return redirect()
                ->route('settings.index')
                ->withErrors(['class_level' => 'Level tidak dapat dihapus karena masih digunakan oleh produk.']);
        }

        $classLevel->delete();

        return redirect()
            ->route('settings.index')
            ->with('status', 'Level kelas berhasil dihapus.');
    }

    public function createFrequency(): View
    {
        return view('settings.frequency-form', ['classFrequency' => new LookupValue]);
    }

    public function storeFrequency(Request $request): RedirectResponse
    {
        LookupValue::create([
            ...$this->validatedLookupData($request, 'class_frequency'),
            'category' => 'class_frequency',
        ]);

        return redirect()
            ->route('settings.index')
            ->with('status', 'Frekuensi kelas berhasil ditambahkan.');
    }

    public function editFrequency(LookupValue $classFrequency): View
    {
        abort_unless($classFrequency->category === 'class_frequency', 404);

        return view('settings.frequency-form', compact('classFrequency'));
    }

    public function updateFrequency(Request $request, LookupValue $classFrequency): RedirectResponse
    {
        abort_unless($classFrequency->category === 'class_frequency', 404);
        $classFrequency->update($this->validatedLookupData($request, 'class_frequency', $classFrequency));

        return redirect()
            ->route('settings.index')
            ->with('status', 'Frekuensi kelas berhasil diperbarui.');
    }

    public function destroyFrequency(LookupValue $classFrequency): RedirectResponse
    {
        abort_unless($classFrequency->category === 'class_frequency', 404);

        if ($classFrequency->frequencyProducts()->exists()) {
            return redirect()
                ->route('settings.index')
                ->withErrors(['class_frequency' => 'Frekuensi tidak dapat dihapus karena masih digunakan oleh produk.']);
        }

        $classFrequency->delete();

        return redirect()
            ->route('settings.index')
            ->with('status', 'Frekuensi kelas berhasil dihapus.');
    }

    public function createLessonStatus(): View
    {
        return view('settings.lesson-status-form', ['lessonStatus' => new LearningClassLessonStatus]);
    }

    public function storeLessonStatus(Request $request): RedirectResponse
    {
        $data = $this->validatedLessonStatusData($request);

        DB::transaction(function () use ($data): void {
            $lastCode = LearningClassLessonStatus::query()->lockForUpdate()->max('code') ?? -1;

            if ($lastCode >= 65535) {
                abort(422, 'Kode status sudah mencapai batas maksimum.');
            }

            LearningClassLessonStatus::query()->create([
                ...$data,
                'code' => $lastCode + 1,
                'sort_order' => $lastCode + 1,
            ]);
        });

        return redirect()
            ->route('settings.index')
            ->with('status', 'Status jadwal les berhasil ditambahkan.');
    }

    public function editLessonStatus(LearningClassLessonStatus $lessonStatus): View
    {
        return view('settings.lesson-status-form', compact('lessonStatus'));
    }

    public function updateLessonStatus(Request $request, LearningClassLessonStatus $lessonStatus): RedirectResponse
    {
        $data = $this->validatedLessonStatusData($request, $lessonStatus);
        if ($lessonStatus->system_key === 'sedang_berlangsung') {
            $data['color'] = '#198754';
        } elseif (in_array($lessonStatus->system_key, ['belum_terlaksana', 'dibatalkan'], true)) {
            $data['color'] = '#dc3545';
        }
        $lessonStatus->update($data);

        return redirect()
            ->route('settings.index')
            ->with('status', 'Status jadwal les berhasil diperbarui.');
    }

    public function destroyLessonStatus(LearningClassLessonStatus $lessonStatus): RedirectResponse
    {
        if ($lessonStatus->system_key !== null) {
            return redirect()
                ->route('settings.index')
                ->withErrors(['lesson_status' => 'Status bawaan tidak dapat dihapus.']);
        }

        if ($lessonStatus->lessons()->exists()) {
            return redirect()
                ->route('settings.index')
                ->withErrors(['lesson_status' => 'Status tidak dapat dihapus karena masih digunakan oleh jadwal les.']);
        }

        $lessonStatus->delete();

        return redirect()
            ->route('settings.index')
            ->with('status', 'Status jadwal les berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?LookupValue $program = null): array
    {
        return $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lookup_values', 'value')
                    ->where('category', 'program')
                    ->ignore($program?->id),
            ],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCategoryData(Request $request, ?LookupValue $classCategory = null): array
    {
        return $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lookup_values', 'value')
                    ->where('category', 'class_category')
                    ->ignore($classCategory?->id),
            ],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedLookupData(Request $request, string $category, ?LookupValue $lookup = null): array
    {
        $data = $request->validate([
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lookup_values', 'value')
                    ->where('category', $category)
                    ->ignore($lookup?->id),
            ],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);

        if ($category === 'class_frequency') {
            $data['is_monthly'] = $request->boolean('is_monthly');
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedLessonStatusData(Request $request, ?LearningClassLessonStatus $lessonStatus = null): array
    {
        return $request->validate([
            'label' => [
                'required',
                'string',
                'max:100',
                Rule::unique('learning_class_lesson_statuses', 'label')
                    ->ignore($lessonStatus?->code, 'code'),
            ],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}
