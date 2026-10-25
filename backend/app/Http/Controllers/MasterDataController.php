<?php

namespace App\Http\Controllers;

use App\Models\LearningClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MasterDataController extends Controller
{
    private const ROLES = [
        'guru' => 'Guru',
        'admin' => 'Admin',
        'murid' => 'Murid',
    ];

    public function index(string $role): View
    {
        $users = User::query()->where('role', $role);
        if ($role === 'murid' && request()->user()->role === 'guru') {
            $this->limitStudentsToTeacherClasses($users, request()->user());
        }

        return view('master-data.index', [
            'role' => $role,
            'roleLabel' => self::ROLES[$role],
            'users' => $users->orderBy('name')->paginate(10),
        ]);
    }

    public function create(string $role): View
    {
        return view('master-data.form', [
            'role' => $role,
            'roleLabel' => self::ROLES[$role],
            'user' => new User,
        ]);
    }

    public function show(string $role, int $user): View
    {
        abort_unless(in_array($role, ['guru', 'admin', 'murid'], true), 404);

        return view("master-data.{$role}-show", [
            'role' => $role,
            'roleLabel' => self::ROLES[$role],
            'user' => $this->findUser($role, $user),
        ]);
    }

    public function image(string $role, int $user, string $image): BinaryFileResponse
    {
        $allowedImages = $role === 'murid' ? ['child_photo'] : ['selfie_image', 'ktp_image'];
        abort_unless(in_array($role, ['guru', 'admin', 'murid'], true) && in_array($image, $allowedImages, true), 404);

        $path = $this->findUser($role, $user)->{$image};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function store(Request $request, string $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                ...($role === 'guru' ? [Rule::unique('users', 'name')->where('role', 'guru')] : []),
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        if (in_array($role, ['guru', 'admin'], true)) {
            $data = [...$data, ...$request->validate($this->profileRules($role, true))];
            $data['password'] = Carbon::parse($data['birth_date'])->format('Ymd');
            $data['selfie_image'] = $this->storeProfileImage($request->file('selfie_image'), "{$role}-documents/selfie");
            $data['ktp_image'] = $this->storeProfileImage($request->file('ktp_image'), "{$role}-documents/ktp");
        } elseif ($role === 'murid') {
            $data = [...$data, ...$request->validate($this->studentRules(true))];
            $data['password'] = Carbon::parse($data['birth_date'])->format('Ymd');
            $data['child_photo'] = $this->storeProfileImage($request->file('child_photo'), 'murid-documents/photos');
        } else {
            $data = [...$data, ...$request->validate([
                'birth_date' => ['nullable', 'date'],
                'password' => ['required', 'string', 'min:8'],
            ])];
        }

        User::create([
            ...$data,
            'role' => $role,
            'status' => 'active',
            'created_by' => $request->user()?->getAuthIdentifier(),
            'created_date' => now(),
        ]);

        return redirect()
            ->route('master-data.index', ['role' => $role])
            ->with('status', self::ROLES[$role].' berhasil ditambahkan.');
    }

    public function edit(string $role, int $user): View
    {
        return view('master-data.form', [
            'role' => $role,
            'roleLabel' => self::ROLES[$role],
            'user' => $this->findUser($role, $user),
        ]);
    }

    public function update(Request $request, string $role, int $user): RedirectResponse
    {
        $record = $this->findUser($role, $user);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                ...($role === 'guru'
                    ? [Rule::unique('users', 'name')->where('role', 'guru')->ignore($record->id)]
                    : []),
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$record->id],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if (in_array($role, ['guru', 'admin'], true)) {
            $data = [...$data, ...$request->validate($this->profileRules($role, false))];
            $oldFilesToDelete = [];

            foreach (['selfie_image', 'ktp_image'] as $imageField) {
                if ($request->hasFile($imageField)) {
                    $documentType = $imageField === 'selfie_image' ? 'selfie' : 'ktp';
                    $newPath = $this->storeProfileImage(
                        $request->file($imageField),
                        "{$role}-documents/{$documentType}",
                    );
                    $oldPath = $record->{$imageField};
                    $data[$imageField] = $newPath;

                    if ($oldPath) {
                        $oldFilesToDelete[] = $oldPath;
                    }
                }
            }
        } elseif ($role === 'murid') {
            $data = [...$data, ...$request->validate($this->studentRules(false))];

            if ($request->hasFile('child_photo')) {
                $newPath = $this->storeProfileImage($request->file('child_photo'), 'murid-documents/photos');
                $oldPath = $record->child_photo;
                $data['child_photo'] = $newPath;

                if ($oldPath) {
                    $oldFilesToDelete[] = $oldPath;
                }
            }
        } else {
            $data = [...$data, ...$request->validate([
                'birth_date' => ['nullable', 'date'],
                'password' => ['nullable', 'string', 'min:8'],
            ])];

            if (blank($data['password'])) {
                unset($data['password']);
            }
        }

        $record->update([
            ...$data,
            'updated_by' => $request->user()?->getAuthIdentifier(),
            'updated_date' => now(),
        ]);

        foreach ($oldFilesToDelete ?? [] as $oldPath) {
            $this->deleteProfileImage($oldPath);
        }

        return redirect()
            ->route('master-data.index', ['role' => $role])
            ->with('status', self::ROLES[$role].' berhasil diperbarui.');
    }

    public function destroy(string $role, int $user): RedirectResponse
    {
        $record = $this->findUser($role, $user);

        if (in_array($role, ['guru', 'murid'], true) && LearningClass::query()
            ->where($role === 'guru' ? 'teacher_id' : 'student_id', $record->id)
            ->exists()) {
            return redirect()
                ->route('master-data.index', ['role' => $role])
                ->withErrors(['user' => 'Data tidak dapat dihapus karena masih digunakan oleh kelas.']);
        }

        if (in_array($role, ['guru', 'admin', 'murid'], true)) {
            $imageFields = $role === 'murid' ? ['child_photo'] : ['selfie_image', 'ktp_image'];
            foreach ($imageFields as $imageField) {
                if ($record->{$imageField}) {
                    $this->deleteProfileImage($record->{$imageField});
                }
            }
        }

        $record->delete();

        return redirect()
            ->route('master-data.index', ['role' => $role])
            ->with('status', self::ROLES[$role].' berhasil dihapus.');
    }

    private function findUser(string $role, int $user): User
    {
        $query = User::query()->where('role', $role);
        if ($role === 'murid' && request()->user()?->role === 'guru') {
            $this->limitStudentsToTeacherClasses($query, request()->user());
        }

        return $query->findOrFail($user);
    }

    private function limitStudentsToTeacherClasses(Builder $query, User $teacher): void
    {
        $query->where(function ($query) use ($teacher): void {
            $query->whereExists(function ($classQuery) use ($teacher): void {
                $classQuery->selectRaw('1')
                    ->from('learning_classes')
                    ->whereColumn('learning_classes.student_id', 'users.id')
                    ->where('learning_classes.teacher_id', $teacher->id);
            })->orWhereExists(function ($lessonQuery) use ($teacher): void {
                $lessonQuery->selectRaw('1')
                    ->from('learning_class_lessons')
                    ->join('learning_classes', 'learning_classes.id', '=', 'learning_class_lessons.learning_class_id')
                    ->whereColumn('learning_classes.student_id', 'users.id')
                    ->where('learning_class_lessons.teacher_id', $teacher->id);
            });
        });
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function profileRules(string $role, bool $creating): array
    {
        $rules = [
            'nickname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'birth_date' => ['required', 'date', 'before:today'],
            'selfie_image' => [$creating ? 'required' : 'sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ktp_image' => [$creating ? 'required' : 'sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        if ($role === 'guru') {
            $rules['teaching_quote'] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function studentRules(bool $creating): array
    {
        return [
            'nickname' => ['required', 'string', 'max:255'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:30'],
            'child_pickup_person' => ['required', 'string', 'max:255'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
            'child_characteristics' => ['required', 'string', 'max:2000'],
            'child_hobbies' => ['required', 'string', 'max:2000'],
            'reference' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'child_photo' => [$creating ? 'required' : 'sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    private function storeProfileImage(UploadedFile $image, string $directory): string
    {
        $path = $image->store($directory, 'local');

        if ($path === false) {
            throw new RuntimeException('Gagal menyimpan gambar data pengguna.');
        }

        return $path;
    }

    private function deleteProfileImage(string $path): void
    {
        $disk = Storage::disk('local');

        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new RuntimeException('Gagal menghapus gambar data pengguna.');
        }
    }
}
