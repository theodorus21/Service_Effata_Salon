<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use App\Models\LearningClassReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class LearningClassReportController extends Controller
{
    public function index(Request $request, LearningClass $learningClass): View
    {
        $this->authorizeClassAccess($request, $learningClass);
        $period = $this->validatedPeriod($request);

        return view('learning-class-reports.index', [
            'learningClass' => $learningClass->load(['teacher', 'student', 'program']),
            'reports' => $learningClass->reports()
                ->with('teacher')
                ->where('period_start', '<=', $period['period_end'])
                ->where('period_end', '>=', $period['period_start'])
                ->orderByDesc('period_start')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString(),
            'period' => $period,
        ]);
    }

    public function create(Request $request, LearningClass $learningClass): View
    {
        $this->authorizeClassAccess($request, $learningClass);
        $period = $this->validatedPeriod($request);

        return view('learning-class-reports.create', [
            'learningClass' => $learningClass->load(['teacher', 'student', 'program']),
            'period' => $period,
        ]);
    }

    public function download(Request $request, LearningClass $learningClass, LearningClassReport $report): Response
    {
        $this->authorizeClassAccess($request, $learningClass);
        abort_unless($report->learning_class_id === $learningClass->id, 404);

        $report->load(['teacher', 'student', 'program']);
        $startDate = Carbon::createFromFormat('!Y-m', $report->period_start)->startOfMonth();
        $endDate = Carbon::createFromFormat('!Y-m', $report->period_end)->endOfMonth();
        $lessons = $learningClass->lessons()
            ->with(['lessonStatus', 'teacher'])
            ->whereBetween('lesson_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('lesson_date')
            ->orderBy('lesson_time')
            ->get();

        $logoPath = public_path('images/effata-logo.jpg');
        if (! is_file($logoPath) || ! is_readable($logoPath)) {
            throw new RuntimeException('Logo Effata tidak ditemukan atau tidak dapat dibaca.');
        }
        $logoContents = file_get_contents($logoPath);
        if ($logoContents === false) {
            throw new RuntimeException('Gagal membaca logo Effata untuk report PDF.');
        }

        $pdf = Pdf::loadView('learning-class-reports.pdf', [
            'learningClass' => $learningClass->load(['category']),
            'report' => $report,
            'lessons' => $lessons,
            'logoData' => 'data:image/jpeg;base64,'.base64_encode($logoContents),
        ])->setPaper('a4', 'portrait');

        $filename = Str::slug('report-'.$learningClass->name.'-'.$report->period_start.'-'.$report->period_end).'.pdf';

        return $pdf->download($filename);
    }

    public function store(Request $request, LearningClass $learningClass): RedirectResponse
    {
        $this->authorizeClassAccess($request, $learningClass);

        $period = $request->validate([
            'period_start' => ['required', 'date_format:Y-m'],
            'period_end' => ['required', 'date_format:Y-m', 'after_or_equal:period_start'],
        ]);
        $data = $request->validate([
            'development_evaluation' => ['nullable', 'string', 'max:10000'],
            'additional_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $learningClass->reports()->create([
            'teacher_id' => $learningClass->teacher_id,
            'student_id' => $learningClass->student_id,
            'program_id' => $learningClass->program_id,
            'period_start' => $period['period_start'],
            'period_end' => $period['period_end'],
            'development_evaluation' => $data['development_evaluation'] ?? null,
            'additional_notes' => $data['additional_notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('learning-classes.reports.index', [
                'learningClass' => $learningClass,
                ...$period,
            ])
            ->with('status', 'Report kelas berhasil ditambahkan.');
    }

    /**
     * @return array{period_start: string, period_end: string}
     */
    private function validatedPeriod(Request $request): array
    {
        $period = $request->validate([
            'period_start' => ['nullable', 'date_format:Y-m'],
            'period_end' => ['nullable', 'date_format:Y-m', 'after_or_equal:period_start'],
        ]);

        $currentMonth = now()->format('Y-m');

        return [
            'period_start' => $period['period_start'] ?? $currentMonth,
            'period_end' => $period['period_end'] ?? $period['period_start'] ?? $currentMonth,
        ];
    }

    private function authorizeClassAccess(Request $request, LearningClass $learningClass): void
    {
        $user = $request->user();

        if ($user->role === 'murid') {
            abort_unless($learningClass->student_id === $user->id, 404);
        } elseif ($user->role === 'guru') {
            abort_unless($learningClass->teacher_id === $user->id, 404);
        }
    }
}
