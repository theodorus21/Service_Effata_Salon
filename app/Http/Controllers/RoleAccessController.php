<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleAccessController extends Controller
{
    public function index(): View
    {
        return view('role-access.index', [
            'roles' => Role::query()
                ->withCount('users')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('role-access.form', ['role' => new Role]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
        ]);

        $code = Str::slug($data['name'], '_');
        if ($code === '') {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Nama role harus menghasilkan kode role yang valid.']);
        }

        if (Role::query()->where('code', $code)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Nama tersebut menghasilkan kode role yang sudah digunakan.']);
        }

        Role::create([
            'code' => $code,
            'name' => $data['name'],
        ]);

        return redirect()
            ->route('role-access.index')
            ->with('status', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role): View
    {
        return view('role-access.form', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);

        $role->update(['name' => $data['name']]);

        return redirect()
            ->route('role-access.index')
            ->with('status', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()
                ->route('role-access.index')
                ->withErrors(['role' => 'Role sistem tidak dapat dihapus.']);
        }

        if ($role->users()->exists()) {
            return redirect()
                ->route('role-access.index')
                ->withErrors(['role' => 'Role tidak dapat dihapus karena masih digunakan oleh pengguna.']);
        }

        $role->delete();

        return redirect()
            ->route('role-access.index')
            ->with('status', 'Role berhasil dihapus.');
    }

    public function editPermissions(Role $role): View
    {
        return view('role-access.permissions', [
            'role' => $role,
            'permissionGroups' => Permission::query()
                ->orderBy('group_key')
                ->orderBy('key')
                ->get()
                ->groupBy('group_label'),
            'selectedPermissions' => $role->permissions()->pluck('permissions.key')->all(),
        ]);
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['required', 'string', Rule::exists('permissions', 'key')],
        ]);

        $permissions = $role->code === Role::MASTER_ADMIN_CODE
            ? Permission::query()->pluck('key')->all()
            : array_values(array_unique($data['permissions'] ?? []));

        $role->permissions()->sync($permissions);

        return redirect()
            ->route('role-access.permissions.edit', $role)
            ->with('status', 'Akses role berhasil diperbarui.');
    }
}
