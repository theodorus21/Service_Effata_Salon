<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;
    use AuthenticatesAsMasterAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_dashboard_shows_master_data_children(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="dashboard-user-submenu" hidden', false)
            ->assertSee(route('master-data.index', ['role' => 'guru']), false)
            ->assertSee(route('master-data.index', ['role' => 'admin']), false)
            ->assertSee(route('master-data.index', ['role' => 'murid']), false)
            ->assertSee('Role Akses')
            ->assertSee(route('role-access.index'), false);
    }

    public function test_user_menu_is_expanded_on_master_data_pages(): void
    {
        $this->get(route('master-data.index', ['role' => 'guru']))
            ->assertOk()
            ->assertSee('>User Management</span>', false)
            ->assertSee('aria-expanded="true"', false)
            ->assertDontSee('id="user-submenu" hidden', false);
    }

    public function test_user_fillable_fields_exist_in_users_table(): void
    {
        $user = new User;
        $databaseColumns = Schema::getColumnListing($user->getTable());

        $this->assertSame([], array_values(array_diff($user->getFillable(), $databaseColumns)));
    }

    public function test_master_admin_and_owner_roles_are_supported_without_master_data_crud(): void
    {
        $masterAdmin = User::factory()->create([
            'role' => User::ROLE_MASTER_ADMIN,
        ]);
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
        ]);

        $this->assertSame('master_admin', $masterAdmin->role);
        $this->assertSame('owner', $owner->role);

        foreach ([User::ROLE_MASTER_ADMIN, User::ROLE_OWNER] as $role) {
            $this->get('/master-data/'.$role)->assertNotFound();
            $this->get('/master-data/'.$role.'/create')->assertNotFound();
        }
    }

    public function test_student_reference_is_a_255_character_string_field(): void
    {
        $this->assertSame('varchar', Schema::getColumnType('users', 'reference'));

        $this->get(route('master-data.create', ['role' => 'murid']))
            ->assertOk()
            ->assertSee('id="reference" name="reference" type="text"', false)
            ->assertSee('maxlength="255"', false);
    }

    public function test_master_data_lists_only_selected_role(): void
    {
        $guru = User::factory()->create(['name' => 'Guru A', 'role' => 'guru']);
        $admin = User::factory()->create(['name' => 'Admin A', 'role' => 'admin']);

        $this->get(route('master-data.index', ['role' => 'guru']))
            ->assertOk()
            ->assertSee('Guru A')
            ->assertSee(route('master-data.show', ['role' => 'guru', 'user' => $guru->id]), false)
            ->assertSee('Detail')
            ->assertDontSee('Admin A');

        $this->get(route('master-data.index', ['role' => 'admin']))
            ->assertOk()
            ->assertSee('Admin A')
            ->assertSee(route('master-data.show', ['role' => 'admin', 'user' => $admin->id]), false)
            ->assertSee('Detail')
            ->assertDontSee('Tanggal lahir');

        $murid = User::factory()->create([
            'name' => 'Murid A',
            'nickname' => 'A',
            'parent_name' => 'Orang Tua A',
            'parent_phone' => '08123456789',
            'role' => 'murid',
        ]);
        $this->get(route('master-data.index', ['role' => 'murid']))
            ->assertOk()
            ->assertSee(route('master-data.show', ['role' => 'murid', 'user' => $murid->id]), false)
            ->assertSee('Detail')
            ->assertSee('Orang Tua A')
            ->assertDontSee('Tanggal lahir');
    }

    public function test_guru_detail_page_shows_profile_and_private_images(): void
    {
        Storage::fake('local');
        $selfie = UploadedFile::fake()->image('selfie.jpg')->store('guru-documents/selfie', 'local');
        $ktp = UploadedFile::fake()->image('ktp.jpg')->store('guru-documents/ktp', 'local');
        $guru = User::factory()->create([
            'name' => 'Guru Detail',
            'nickname' => 'Bu Detail',
            'role' => 'guru',
            'birth_date' => '1990-04-09',
            'phone' => '08123456789',
            'bank_name' => 'Bank Effata',
            'bank_account_number' => '123456789',
            'teaching_quote' => 'Mengajar dengan gembira.',
            'address' => 'Jl. Pendidikan',
            'selfie_image' => $selfie,
            'ktp_image' => $ktp,
        ]);

        $this->get(route('master-data.show', ['role' => 'guru', 'user' => $guru->id]))
            ->assertOk()
            ->assertSee('Guru Detail')
            ->assertSee('Bu Detail')
            ->assertSee('Mengajar dengan gembira.')
            ->assertSee('Jl. Pendidikan')
            ->assertSee(route('master-data.image', ['role' => 'guru', 'user' => $guru->id, 'image' => 'selfie_image']), false)
            ->assertSee(route('master-data.image', ['role' => 'guru', 'user' => $guru->id, 'image' => 'ktp_image']), false);

        $this->get(route('master-data.image', ['role' => 'guru', 'user' => $guru->id, 'image' => 'selfie_image']))
            ->assertOk();
    }

    public function test_detail_route_rejects_other_roles_and_missing_images(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $this->get(route('master-data.show', ['role' => 'admin', 'user' => $guru->id]))
            ->assertNotFound();

        $this->get(route('master-data.image', ['role' => 'guru', 'user' => $guru->id, 'image' => 'child_photo']))
            ->assertNotFound();
        $this->get(route('master-data.image', ['role' => 'guru', 'user' => $guru->id, 'image' => 'ktp_image']))
            ->assertNotFound();
    }

    public function test_admin_detail_page_shows_profile_and_private_images_without_birth_date(): void
    {
        Storage::fake('local');
        $selfie = UploadedFile::fake()->image('admin-selfie.jpg')->store('admin-documents/selfie', 'local');
        $ktp = UploadedFile::fake()->image('admin-ktp.jpg')->store('admin-documents/ktp', 'local');
        $admin = User::factory()->create([
            'name' => 'Admin Detail',
            'nickname' => 'Admin',
            'role' => 'admin',
            'birth_date' => '1990-04-09',
            'phone' => '08123456789',
            'bank_name' => 'Bank Effata',
            'bank_account_number' => '123456789',
            'address' => 'Jl. Admin',
            'selfie_image' => $selfie,
            'ktp_image' => $ktp,
        ]);

        $this->get(route('master-data.show', ['role' => 'admin', 'user' => $admin->id]))
            ->assertOk()
            ->assertSee('Detail Admin')
            ->assertSee('Admin Detail')
            ->assertSee('Jl. Admin')
            ->assertDontSee('Tanggal lahir')
            ->assertSee(route('master-data.image', ['role' => 'admin', 'user' => $admin->id, 'image' => 'selfie_image']), false)
            ->assertSee(route('master-data.image', ['role' => 'admin', 'user' => $admin->id, 'image' => 'ktp_image']), false);

        $this->get(route('master-data.image', ['role' => 'admin', 'user' => $admin->id, 'image' => 'ktp_image']))
            ->assertOk();
    }

    public function test_murid_detail_page_shows_all_student_information_and_private_photo(): void
    {
        Storage::fake('local');
        $photo = UploadedFile::fake()->image('anak.jpg')->store('murid-documents/photos', 'local');
        $murid = User::factory()->create([
            'name' => 'Anak Detail',
            'nickname' => 'Dede',
            'parent_name' => 'Bapak Detail',
            'parent_phone' => '08111111111',
            'child_pickup_person' => 'Bapak',
            'email' => 'orangtua@example.test',
            'emergency_contact_name' => 'Ibu Darurat',
            'emergency_contact_phone' => '08222222222',
            'birth_date' => '2018-06-12',
            'address' => 'Jl. Anak',
            'child_characteristics' => 'Ceria dan aktif',
            'child_hobbies' => 'Menggambar',
            'child_photo' => $photo,
            'reference' => 'Teman sekolah',
            'role' => 'murid',
        ]);

        $this->get(route('master-data.show', ['role' => 'murid', 'user' => $murid->id]))
            ->assertOk()
            ->assertSee('Anak Detail')
            ->assertSee('Dede')
            ->assertSee('Bapak Detail')
            ->assertSee('Penjemput anak')
            ->assertSee('Bapak')
            ->assertSee('orangtua@example.test')
            ->assertSee('Ibu Darurat')
            ->assertSee('08222222222')
            ->assertSee('Ceria dan aktif')
            ->assertSee('Menggambar')
            ->assertSee('Teman sekolah')
            ->assertDontSee('Tanggal lahir')
            ->assertSee(route('master-data.image', ['role' => 'murid', 'user' => $murid->id, 'image' => 'child_photo']), false);

        $this->get(route('master-data.image', ['role' => 'murid', 'user' => $murid->id, 'image' => 'child_photo']))
            ->assertOk();
    }

    public function test_guru_create_form_displays_requested_fields_and_supports_file_uploads(): void
    {
        $this->get(route('master-data.create', ['role' => 'guru']))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="nickname"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="bank_account_number"', false)
            ->assertSee('name="bank_name"', false)
            ->assertSee('name="teaching_quote"', false)
            ->assertSee('name="birth_date"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="selfie_image"', false)
            ->assertSee('name="ktp_image"', false)
            ->assertDontSee('name="password"', false);
    }

    public function test_admin_create_form_displays_requested_fields_and_supports_file_uploads(): void
    {
        $this->get(route('master-data.create', ['role' => 'admin']))
            ->assertOk()
            ->assertSee('name="nickname"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="birth_date"', false)
            ->assertSee('name="bank_account_number"', false)
            ->assertSee('name="bank_name"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="selfie_image"', false)
            ->assertSee('name="ktp_image"', false)
            ->assertSee('Kata sandi awal dibuat otomatis dari tanggal lahir (YYYYMMDD).')
            ->assertDontSee('name="teaching_quote"', false)
            ->assertDontSee('name="password"', false);
    }

    public function test_murid_create_form_displays_requested_fields_and_photo_upload(): void
    {
        $this->get(route('master-data.create', ['role' => 'murid']))
            ->assertOk()
            ->assertSee('name="name"', false)
            ->assertSee('name="nickname"', false)
            ->assertSee('name="parent_name"', false)
            ->assertSee('name="parent_phone"', false)
            ->assertSee('name="child_pickup_person"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="emergency_contact_name"', false)
            ->assertSee('name="emergency_contact_phone"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="child_characteristics"', false)
            ->assertSee('name="child_hobbies"', false)
            ->assertSee('name="child_photo"', false)
            ->assertSee('name="reference"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertDontSee('name="password"', false);
    }

    public function test_master_data_can_create_update_and_delete_each_role(): void
    {
        Storage::fake('local');
        $email = 'orangtua@example.test';

        $this->post(route('master-data.store', ['role' => 'murid']), [
            'name' => 'Anak Baru',
            'nickname' => 'Adek',
            'parent_name' => 'Orang Tua',
            'parent_phone' => '08123456789',
            'child_pickup_person' => 'Ibu',
            'email' => $email,
            'emergency_contact_name' => 'Kontak Darurat',
            'emergency_contact_phone' => '08234567890',
            'birth_date' => '2018-05-12',
            'address' => 'Jl. Anak',
            'child_characteristics' => 'Aktif',
            'child_hobbies' => 'Membaca',
            'reference' => 'Teman',
            'child_photo' => UploadedFile::fake()->image('anak.jpg'),
        ])->assertRedirect(route('master-data.index', ['role' => 'murid']));

        $user = User::query()->where('email', $email)->firstOrFail();
        $this->assertSame('murid', $user->role);
        $this->assertSame('Orang Tua', $user->parent_name);
        $this->assertSame('Ibu', $user->child_pickup_person);
        $this->assertSame('2018-05-12', $user->birth_date->toDateString());
        $this->assertTrue(Hash::check('20180512', $user->password));
        $this->assertTrue($user->created_date !== null);
        Storage::disk('local')->assertExists($user->child_photo);

        $this->put(route('master-data.update', ['role' => 'murid', 'user' => $user->id]), [
            'name' => 'Anak Diperbarui',
            'nickname' => 'Adek',
            'parent_name' => 'Orang Tua Baru',
            'parent_phone' => '08123456789',
            'child_pickup_person' => 'Ayah',
            'email' => $email,
            'emergency_contact_name' => 'Kontak Darurat',
            'emergency_contact_phone' => '08234567890',
            'birth_date' => '2018-05-12',
            'address' => 'Jl. Anak',
            'child_characteristics' => 'Aktif',
            'child_hobbies' => 'Membaca',
            'reference' => 'Teman',
        ])->assertRedirect(route('master-data.index', ['role' => 'murid']));

        $user->refresh();
        $this->assertSame('Anak Diperbarui', $user->name);
        Storage::disk('local')->assertExists($user->child_photo);

        $this->delete(route('master-data.destroy', ['role' => 'murid', 'user' => $user->id]))
            ->assertRedirect(route('master-data.index', ['role' => 'murid']));

        Storage::disk('local')->assertMissing($user->child_photo);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_guru_can_be_created_with_details_and_private_image_uploads(): void
    {
        Storage::fake('local');
        $selfie = UploadedFile::fake()->image('selfie.jpg');
        $ktp = UploadedFile::fake()->image('ktp.png');

        $this->post(route('master-data.store', ['role' => 'guru']), [
            'name' => 'Guru Baru',
            'nickname' => 'Bu Guru',
            'phone' => '08123456789',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Bank Effata',
            'teaching_quote' => 'Belajar dengan gembira.',
            'birth_date' => '1990-04-09',
            'address' => 'Jl. Pendidikan No. 1',
            'email' => 'guru@example.test',
            'selfie_image' => $selfie,
            'ktp_image' => $ktp,
        ])->assertRedirect(route('master-data.index', ['role' => 'guru']));

        $user = User::query()->where('email', 'guru@example.test')->firstOrFail();

        $this->assertSame('Bu Guru', $user->nickname);
        $this->assertSame('08123456789', $user->phone);
        $this->assertSame('1990-04-09', $user->birth_date->toDateString());
        $this->assertSame('Jl. Pendidikan No. 1', $user->address);
        $this->assertTrue(Hash::check('19900409', $user->password));
        Storage::disk('local')->assertExists($user->selfie_image);
        Storage::disk('local')->assertExists($user->ktp_image);
    }

    public function test_admin_can_be_created_updated_and_deleted_with_profile_images(): void
    {
        Storage::fake('local');

        $this->post(route('master-data.store', ['role' => 'admin']), [
            'name' => 'Admin Baru',
            'nickname' => 'Admin',
            'phone' => '08123456789',
            'birth_date' => '1991-03-14',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Bank Effata',
            'address' => 'Jl. Administrasi No. 2',
            'email' => 'admin-profile@example.test',
            'selfie_image' => UploadedFile::fake()->image('admin-selfie.jpg'),
            'ktp_image' => UploadedFile::fake()->image('admin-ktp.png'),
        ])->assertRedirect(route('master-data.index', ['role' => 'admin']));

        $admin = User::query()->where('email', 'admin-profile@example.test')->firstOrFail();

        $this->assertSame('Admin', $admin->nickname);
        $this->assertSame('1991-03-14', $admin->birth_date->toDateString());
        $this->assertSame('Jl. Administrasi No. 2', $admin->address);
        $this->assertTrue(Hash::check('19910314', $admin->password));
        Storage::disk('local')->assertExists($admin->selfie_image);
        Storage::disk('local')->assertExists($admin->ktp_image);

        $oldSelfie = $admin->selfie_image;
        $this->put(route('master-data.update', ['role' => 'admin', 'user' => $admin->id]), [
            'name' => 'Admin Baru',
            'nickname' => 'Admin Diperbarui',
            'phone' => '08123456780',
            'birth_date' => '1991-03-14',
            'bank_account_number' => '0987654321',
            'bank_name' => 'Bank Baru',
            'address' => 'Alamat baru',
            'email' => 'admin-profile@example.test',
            'selfie_image' => UploadedFile::fake()->image('admin-selfie-new.jpg'),
        ])->assertRedirect(route('master-data.index', ['role' => 'admin']));

        $admin->refresh();
        $this->assertSame('Admin Diperbarui', $admin->nickname);
        $this->assertSame('Bank Baru', $admin->bank_name);
        Storage::disk('local')->assertMissing($oldSelfie);
        Storage::disk('local')->assertExists($admin->selfie_image);
        Storage::disk('local')->assertExists($admin->ktp_image);

        $this->delete(route('master-data.destroy', ['role' => 'admin', 'user' => $admin->id]))
            ->assertRedirect(route('master-data.index', ['role' => 'admin']));

        Storage::disk('local')->assertMissing($admin->selfie_image);
        Storage::disk('local')->assertMissing($admin->ktp_image);
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_guru_name_must_be_unique_on_create_and_update(): void
    {
        $existingGuru = User::factory()->create([
            'name' => 'Nama Guru Sama',
            'role' => 'guru',
        ]);

        $this->post(route('master-data.store', ['role' => 'guru']), [
            'name' => 'Nama Guru Sama',
            'email' => 'guru-duplicate@example.test',
        ])->assertSessionHasErrors('name');

        $otherGuru = User::factory()->create([
            'name' => 'Nama Guru Lain',
            'role' => 'guru',
        ]);

        $this->put(route('master-data.update', ['role' => 'guru', 'user' => $otherGuru->id]), [
            'name' => $existingGuru->name,
            'email' => $otherGuru->email,
        ])->assertSessionHasErrors('name');

        $this->put(route('master-data.update', ['role' => 'guru', 'user' => $existingGuru->id]), [
            'name' => $existingGuru->name,
            'email' => $existingGuru->email,
            'nickname' => 'Panggilan',
            'phone' => '08123456789',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Bank Effata',
            'teaching_quote' => 'Belajar.',
            'birth_date' => '1990-04-09',
            'address' => 'Alamat',
        ])->assertSessionHasNoErrors();
    }

    public function test_guru_birth_date_must_be_before_today_on_create_and_update(): void
    {
        Storage::fake('local');
        $today = today()->toDateString();

        $this->post(route('master-data.store', ['role' => 'guru']), [
            'name' => 'Guru Tanggal Salah',
            'nickname' => 'Guru',
            'phone' => '08123456789',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Bank Effata',
            'teaching_quote' => 'Belajar.',
            'birth_date' => $today,
            'address' => 'Alamat',
            'email' => 'guru-date@example.test',
            'selfie_image' => UploadedFile::fake()->image('selfie.jpg'),
            'ktp_image' => UploadedFile::fake()->image('ktp.jpg'),
        ])->assertSessionHasErrors('birth_date');

        $guru = User::factory()->create([
            'role' => 'guru',
            'birth_date' => '1990-04-09',
        ]);

        $this->put(route('master-data.update', ['role' => 'guru', 'user' => $guru->id]), [
            'name' => $guru->name,
            'nickname' => 'Guru',
            'phone' => '08123456789',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Bank Effata',
            'teaching_quote' => 'Belajar.',
            'birth_date' => today()->addDay()->toDateString(),
            'address' => 'Alamat',
            'email' => $guru->email,
        ])->assertSessionHasErrors('birth_date');
    }

    public function test_guru_images_can_be_replaced_and_are_removed_with_record(): void
    {
        Storage::fake('local');
        $oldSelfie = UploadedFile::fake()->image('old-selfie.jpg')->store('guru-documents/selfie', 'local');
        $oldKtp = UploadedFile::fake()->image('old-ktp.jpg')->store('guru-documents/ktp', 'local');
        $user = User::factory()->create([
            'role' => 'guru',
            'birth_date' => '1990-04-09',
            'selfie_image' => $oldSelfie,
            'ktp_image' => $oldKtp,
        ]);

        $this->put(route('master-data.update', ['role' => 'guru', 'user' => $user->id]), [
            'name' => $user->name,
            'nickname' => 'Panggilan baru',
            'phone' => '08123456789',
            'bank_account_number' => '1234567890',
            'bank_name' => 'Bank Effata',
            'teaching_quote' => 'Mengajar dengan hati.',
            'birth_date' => '1990-04-09',
            'address' => 'Alamat baru',
            'email' => $user->email,
            'selfie_image' => UploadedFile::fake()->image('new-selfie.jpg'),
        ])->assertRedirect(route('master-data.index', ['role' => 'guru']));

        $user->refresh();
        Storage::disk('local')->assertMissing($oldSelfie);
        Storage::disk('local')->assertExists($user->selfie_image);
        Storage::disk('local')->assertExists($oldKtp);

        $this->delete(route('master-data.destroy', ['role' => 'guru', 'user' => $user->id]))
            ->assertRedirect(route('master-data.index', ['role' => 'guru']));

        Storage::disk('local')->assertMissing($user->selfie_image);
        Storage::disk('local')->assertMissing($oldKtp);
    }

    public function test_user_from_another_role_cannot_be_edited_through_master_data(): void
    {
        $user = User::factory()->create(['role' => 'guru']);

        $this->get(route('master-data.edit', ['role' => 'admin', 'user' => $user->id]))
            ->assertNotFound();
    }
}
