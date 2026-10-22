@extends('layouts.dashboard')

@php($isEditing = $user->exists)
@section('title', ($isEditing ? 'Ubah' : 'Tambah').' '.$roleLabel)
@section('breadcrumb', 'Master Data / '.$roleLabel)

@section('content')
    <section class="master-data-heading">
        <div>
            <p class="dashboard-eyebrow">PENGELOLAAN AKUN</p>
            <h1>{{ $isEditing ? 'Ubah' : 'Tambah' }} {{ $roleLabel }}</h1>
            <p>Lengkapi informasi akun {{ strtolower($roleLabel) }}.</p>
        </div>
        @if (auth()->user()->hasPermission("master_data.{$role}.view"))
            <a class="secondary-action" href="{{ route('master-data.index', ['role' => $role]) }}">Kembali</a>
        @endif
    </section>

    <section class="panel master-data-form-panel">
        <form method="POST" enctype="multipart/form-data" action="{{ $isEditing ? route('master-data.update', ['role' => $role, 'user' => $user->id]) : route('master-data.store', ['role' => $role]) }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="master-data-field">
                <label for="name">{{ $role === 'murid' ? 'Nama lengkap anak' : 'Nama lengkap' }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                @error('name')<p class="master-data-error">{{ $message }}</p>@enderror
            </div>
            @if ($role !== 'murid')
                <div class="master-data-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                    @error('email')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
            @endif
            @if (in_array($role, ['guru', 'admin'], true))
                <div class="master-data-field">
                    <label for="nickname">Nama panggilan</label>
                    <input id="nickname" name="nickname" type="text" value="{{ old('nickname', $user->nickname) }}" required maxlength="255">
                    @error('nickname')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                <div class="master-data-field">
                    <label for="phone">No. telepon</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required maxlength="30" autocomplete="tel">
                    @error('phone')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                <div class="master-data-field">
                    <label for="bank_account_number">Nomor rekening</label>
                    <input id="bank_account_number" name="bank_account_number" type="text" value="{{ old('bank_account_number', $user->bank_account_number) }}" required maxlength="100">
                    @error('bank_account_number')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                <div class="master-data-field">
                    <label for="bank_name">Bank rekening</label>
                    <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $user->bank_name) }}" required maxlength="255">
                    @error('bank_name')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                @if ($role === 'guru')
                    <div class="master-data-field">
                        <label for="teaching_quote">Quotes of Teaching</label>
                        <textarea id="teaching_quote" name="teaching_quote" required maxlength="2000">{{ old('teaching_quote', $user->teaching_quote) }}</textarea>
                        @error('teaching_quote')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div class="master-data-field">
                    <label for="birth_date">Tanggal lahir</label>
                    <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}" required>
                    <small>Kata sandi awal dibuat otomatis dari tanggal lahir (YYYYMMDD).</small>
                    @error('birth_date')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                <div class="master-data-field">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" required maxlength="2000">{{ old('address', $user->address) }}</textarea>
                    @error('address')<p class="master-data-error">{{ $message }}</p>@enderror
                </div>
                @foreach (['selfie_image' => 'Gambar selfie', 'ktp_image' => 'Gambar KTP'] as $imageField => $label)
                    <div class="master-data-field">
                        <label for="{{ $imageField }}">{{ $label }} {{ $isEditing ? '(opsional jika tidak diganti)' : '' }}</label>
                        <input id="{{ $imageField }}" name="{{ $imageField }}" type="file" accept="image/jpeg,image/png,image/webp" {{ $isEditing ? '' : 'required' }}>
                        <small>Format JPG, PNG, atau WEBP; maksimal 5 MB.</small>
                        @if ($isEditing && $user->{$imageField})
                            <small>File tersimpan: {{ basename($user->{$imageField}) }}</small>
                        @endif
                        @error($imageField)<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            @else
                @if ($role === 'murid')
                    <div class="master-data-field">
                        <label for="nickname">Nama panggilan anak</label>
                        <input id="nickname" name="nickname" type="text" value="{{ old('nickname', $user->nickname) }}" required maxlength="255">
                        @error('nickname')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="parent_name">Nama orang tua</label>
                        <input id="parent_name" name="parent_name" type="text" value="{{ old('parent_name', $user->parent_name) }}" required maxlength="255">
                        @error('parent_name')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="parent_phone">No. telepon orang tua</label>
                        <input id="parent_phone" name="parent_phone" type="tel" value="{{ old('parent_phone', $user->parent_phone) }}" required maxlength="30">
                        @error('parent_phone')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="child_pickup_person">Penjemput anak</label>
                        <input id="child_pickup_person" name="child_pickup_person" type="text" value="{{ old('child_pickup_person', $user->child_pickup_person) }}" required maxlength="255">
                        @error('child_pickup_person')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="email">Email orang tua</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                        @error('email')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="emergency_contact_name">Nama kontak darurat</label>
                        <input id="emergency_contact_name" name="emergency_contact_name" type="text" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" required maxlength="255">
                        @error('emergency_contact_name')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="emergency_contact_phone">No. telepon kontak darurat</label>
                        <input id="emergency_contact_phone" name="emergency_contact_phone" type="tel" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}" required maxlength="30">
                        @error('emergency_contact_phone')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="birth_date">Tanggal lahir anak</label>
                        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}" required>
                        <small>Kata sandi awal akun orang tua dibuat otomatis dari tanggal lahir (YYYYMMDD).</small>
                        @error('birth_date')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="address">Alamat</label>
                        <textarea id="address" name="address" required maxlength="2000">{{ old('address', $user->address) }}</textarea>
                        @error('address')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="child_characteristics">Karakteristik anak</label>
                        <textarea id="child_characteristics" name="child_characteristics" required maxlength="2000">{{ old('child_characteristics', $user->child_characteristics) }}</textarea>
                        @error('child_characteristics')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="child_hobbies">Hobi anak</label>
                        <textarea id="child_hobbies" name="child_hobbies" required maxlength="2000">{{ old('child_hobbies', $user->child_hobbies) }}</textarea>
                        @error('child_hobbies')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="reference">Referensi</label>
                        <input id="reference" name="reference" type="text" value="{{ old('reference', $user->reference) }}" required maxlength="255">
                        @error('reference')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="child_photo">Gambar foto anak {{ $isEditing ? '(opsional jika tidak diganti)' : '' }}</label>
                        <input id="child_photo" name="child_photo" type="file" accept="image/jpeg,image/png,image/webp" {{ $isEditing ? '' : 'required' }}>
                        <small>Format JPG, PNG, atau WEBP; maksimal 5 MB.</small>
                        @if ($isEditing && $user->child_photo)
                            <small>File tersimpan: {{ basename($user->child_photo) }}</small>
                        @endif
                        @error('child_photo')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                @else
                    <div class="master-data-field">
                        <label for="birth_date">Tanggal lahir <span>(opsional)</span></label>
                        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                        @error('birth_date')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="master-data-field">
                        <label for="password">Kata sandi {{ $isEditing ? '(kosongkan jika tidak diubah)' : '' }}</label>
                        <input id="password" name="password" type="password" {{ $isEditing ? '' : 'required' }} minlength="8" autocomplete="new-password">
                        @error('password')<p class="master-data-error">{{ $message }}</p>@enderror
                    </div>
                @endif
            @endif

            <div class="master-data-form-actions">
                @if (auth()->user()->hasPermission("master_data.{$role}.view"))
                    <a class="secondary-action" href="{{ route('master-data.index', ['role' => $role]) }}">Batal</a>
                @endif
                <button class="primary-action" type="submit">{{ $isEditing ? 'Simpan perubahan' : 'Simpan data' }}</button>
            </div>
        </form>
    </section>
@endsection
