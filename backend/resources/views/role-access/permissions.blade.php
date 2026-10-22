@extends('layouts.dashboard')

@section('title', 'Akses Role')
@section('breadcrumb', 'Role Akses / Akses')

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">KONFIGURASI HAK AKSES</p>
            <h1>Akses {{ $role->name }}</h1>
            <p>Pilih menu, submenu, dan tombol yang dapat digunakan role ini.</p>
        </div>
        @if (auth()->user()->hasPermission('role_access.view'))
            <a class="secondary-action" href="{{ route('role-access.index') }}">Kembali ke daftar role</a>
        @endif
    </section>

    @if (session('status'))
        <div class="master-data-alert" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="master-data-alert master-data-alert-error" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($role->code === \App\Models\Role::MASTER_ADMIN_CODE)
        <div class="master-data-alert" role="status">Master Admin memiliki seluruh akses secara permanen.</div>
    @endif

    <form method="POST" action="{{ route('role-access.permissions.update', $role) }}">
        @csrf
        @method('PUT')
        @foreach ($permissionGroups as $groupLabel => $permissions)
            <section class="panel master-data-panel role-permission-panel" aria-label="Akses {{ $groupLabel }}">
                <div class="panel-heading">
                    <div>
                        <h2>{{ $groupLabel }}</h2>
                        <p>Pilih akses menu dan aksi untuk bagian ini.</p>
                    </div>
                </div>
                <div class="role-permission-list">
                    @foreach ($permissions as $permission)
                        <label class="role-permission-item" for="permission-{{ \Illuminate\Support\Str::slug($permission->key) }}">
                            <span>
                                <strong>{{ $permission->label }}</strong>
                                <small>{{ $permission->key }}</small>
                            </span>
                            <input
                                id="permission-{{ \Illuminate\Support\Str::slug($permission->key) }}"
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->key }}"
                                @checked(in_array($permission->key, $selectedPermissions, true) || $role->code === \App\Models\Role::MASTER_ADMIN_CODE)
                                @disabled($role->code === \App\Models\Role::MASTER_ADMIN_CODE)
                            >
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
        <div class="master-data-form-actions role-permission-actions">
            @if (auth()->user()->hasPermission('role_access.view'))
                <a class="secondary-action" href="{{ route('role-access.index') }}">Batal</a>
            @endif
            <button class="primary-action" type="submit" @disabled($role->code === \App\Models\Role::MASTER_ADMIN_CODE)>Simpan Akses</button>
        </div>
    </form>
@endsection
