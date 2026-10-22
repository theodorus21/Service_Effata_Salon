<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\AuthenticatesAsMasterAdmin;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;
    use AuthenticatesAsMasterAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsMasterAdmin();
    }

    public function test_role_access_menu_lists_existing_and_seeded_roles(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Role Akses')
            ->assertSee(route('role-access.index'), false);

        $this->get(route('role-access.index'))
            ->assertOk()
            ->assertSee('Master Admin')
            ->assertSee('Guru')
            ->assertSee('Admin')
            ->assertSee('Murid')
            ->assertSee('Owner')
            ->assertSee('User');
    }

    public function test_role_can_be_created_edited_and_deleted(): void
    {
        $this->post(route('role-access.store'), ['name' => 'Koordinator Akademik'])
            ->assertRedirect(route('role-access.index'))
            ->assertSessionHas('status', 'Role berhasil ditambahkan.');

        $role = Role::query()->where('code', 'koordinator_akademik')->firstOrFail();
        $this->assertFalse($role->is_system);

        $this->get(route('role-access.edit', $role))
            ->assertOk()
            ->assertSee('Koordinator Akademik');

        $this->put(route('role-access.update', $role), ['name' => 'Koordinator Program'])
            ->assertRedirect(route('role-access.index'));
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'code' => 'koordinator_akademik',
            'name' => 'Koordinator Program',
        ]);

        $this->delete(route('role-access.destroy', $role))
            ->assertRedirect(route('role-access.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_role_code_conflicts_and_duplicate_names_are_rejected(): void
    {
        $this->from(route('role-access.create'))
            ->post(route('role-access.store'), ['name' => 'Master Admin'])
            ->assertRedirect(route('role-access.create'))
            ->assertSessionHasErrors('name');

        $this->from(route('role-access.create'))
            ->post(route('role-access.store'), ['name' => '   !!!   '])
            ->assertRedirect(route('role-access.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_system_roles_and_roles_assigned_to_users_cannot_be_deleted(): void
    {
        $systemRole = Role::query()->where('code', 'guru')->firstOrFail();
        $this->delete(route('role-access.destroy', $systemRole))
            ->assertSessionHasErrors('role');

        $customRole = Role::create([
            'code' => 'reviewer',
            'name' => 'Reviewer',
        ]);
        User::factory()->create(['role' => $customRole->code]);

        $this->delete(route('role-access.destroy', $customRole))
            ->assertSessionHasErrors('role');
        $this->assertDatabaseHas('roles', ['id' => $customRole->id]);
    }

    public function test_guests_cannot_access_role_management(): void
    {
        auth()->logout();

        $this->get(route('role-access.index'))
            ->assertRedirect(route('signin'));
    }

    public function test_master_admin_has_every_permission_checked_and_access_is_linked_to_role(): void
    {
        $masterAdmin = Role::query()->where('code', Role::MASTER_ADMIN_CODE)->firstOrFail();
        $this->get(route('role-access.permissions.edit', $masterAdmin))
            ->assertOk()
            ->assertSee('Master Admin memiliki seluruh akses secara permanen.')
            ->assertSee('checked', false);

        $role = Role::create(['code' => 'operator', 'name' => 'Operator']);
        $this->put(route('role-access.permissions.update', $role), [
            'permissions' => ['dashboard.view', 'learning_classes.view'],
        ])->assertRedirect(route('role-access.permissions.edit', $role));

        $operator = User::factory()->create(['role' => $role->code]);
        $this->actingAs($operator)
            ->get(route('learning-classes.index'))
            ->assertOk()
            ->assertDontSee(route('learning-classes.create'), false)
            ->assertDontSee('Role Akses');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('href="#appointments"', false)
            ->assertDontSee('Buat reservasi')
            ->assertDontSee('Notifikasi');

        $this->get(route('learning-classes.create'))->assertForbidden();
        $this->post(route('learning-classes.store'))->assertForbidden();
        $this->get(route('role-access.index'))->assertForbidden();
    }

    public function test_unchecked_settings_submenus_and_actions_are_hidden_and_forbidden(): void
    {
        $role = Role::create(['code' => 'program_viewer', 'name' => 'Program Viewer']);
        $this->put(route('role-access.permissions.update', $role), [
            'permissions' => ['dashboard.view', 'settings.view', 'settings.programs.view'],
        ])->assertRedirect();

        $user = User::factory()->create(['role' => $role->code]);
        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Program Kelas')
            ->assertDontSee('Kategori Kelas')
            ->assertDontSee('Tambah Program');

        $this->get(route('settings.class-categories.create'))->assertForbidden();
        $this->get(route('settings.programs.create'))->assertForbidden();
    }

    public function test_master_admin_cannot_lose_any_permission(): void
    {
        $masterAdmin = Role::query()->where('code', Role::MASTER_ADMIN_CODE)->firstOrFail();

        $this->put(route('role-access.permissions.update', $masterAdmin), [
            'permissions' => ['dashboard.view'],
        ])->assertRedirect(route('role-access.permissions.edit', $masterAdmin));

        $this->assertSame(
            \App\Models\Permission::query()->count(),
            $masterAdmin->permissions()->count(),
        );
    }

    public function test_user_api_management_requires_its_permission_but_public_signup_still_works(): void
    {
        $role = Role::create(['code' => 'api_operator', 'name' => 'API Operator']);
        $user = User::factory()->create(['role' => $role->code]);

        $this->actingAs($user)
            ->getJson('/api/v1/users')
            ->assertForbidden();

        $role->permissions()->attach('users.api.manage');
        $user = User::query()->findOrFail($user->id);
        $this->actingAs($user)
            ->getJson('/api/v1/users')
            ->assertOk();

        $otherUser = User::factory()->create(['role' => 'user']);
        $this->putJson('/api/v1/users/'.$otherUser->id, ['role' => Role::MASTER_ADMIN_CODE])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->postJson('/api/v1/users', [
            'name' => 'Pendaftar Baru',
            'email' => 'pendaftar@example.com',
            'password' => 'password123',
            'role' => 'user',
        ])->assertCreated();
    }
}
