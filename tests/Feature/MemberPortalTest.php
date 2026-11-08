<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_member_login_and_protected_pages_return_to_member_path(): void
    {
        $this->get(route('member.home'))
            ->assertOk()
            ->assertSee('PORTAL MEMBER MURID')
            ->assertSee(route('member.login.store'), false)
            ->assertSee(route('signup'), false)
            ->assertDontSee(route('admin.signin'), false);

        $this->get(route('member.profile'))
            ->assertRedirect(route('member.home'));
    }

    public function test_active_student_sees_their_name_link_to_the_member_portal_on_the_homepage(): void
    {
        $student = User::factory()->create([
            'name' => 'Nara Effata',
            'nickname' => 'Nara',
            'role' => User::ROLE_STUDENT,
            'status' => 'active',
        ]);

        $this->actingAs($student, 'member')
            ->get(route('home'))
            ->assertOk()
            ->assertSee('<a class="landing-member-link landing-member-name" href="'.route('member.home').'">Nara Effata</a>', false)
            ->assertDontSee('<a class="landing-member-link" href="'.route('member.home').'">Member</a>', false)
            ->assertDontSee('<a class="landing-button landing-button-small" href="'.route('signup').'">Daftar</a>', false);
    }

    public function test_only_active_students_can_sign_in_to_member_portal(): void
    {
        $student = User::factory()->create([
            'name' => 'Murid Effata',
            'nickname' => 'Nara',
            'email' => 'nara@example.com',
            'password' => 'password-lama',
            'role' => User::ROLE_STUDENT,
            'status' => 'active',
            'parent_name' => 'Orang Tua Nara',
            'parent_phone' => '08123456789',
            'birth_date' => '2018-05-12',
            'address' => 'Alamat Nara',
        ]);
        User::factory()->create([
            'email' => 'guru@example.com',
            'password' => 'password-guru',
            'role' => 'guru',
            'status' => 'active',
        ]);
        User::factory()->create([
            'email' => 'nonaktif@example.com',
            'password' => 'password-nonaktif',
            'role' => User::ROLE_STUDENT,
            'status' => 'inactive',
        ]);

        $this->from(route('member.home'))
            ->post(route('member.login.store'), [
                'email' => 'guru@example.com',
                'password' => 'password-guru',
            ])
            ->assertRedirect(route('member.home'))
            ->assertSessionHasErrors('email');
        $this->assertGuest('member');

        $this->from(route('member.home'))
            ->post(route('member.login.store'), [
                'email' => 'nonaktif@example.com',
                'password' => 'password-nonaktif',
            ])
            ->assertRedirect(route('member.home'))
            ->assertSessionHasErrors('email');
        $this->assertGuest('member');

        $this->post(route('member.login.store'), [
            'email' => 'nara@example.com',
            'password' => 'password-lama',
            'remember' => '1',
        ])->assertRedirect(route('member.home'));

        $this->assertAuthenticatedAs($student, 'member');
        $this->get(route('member.home'))
            ->assertOk()
            ->assertSee('Halo, Nara!')
            ->assertSee(route('member.profile'), false);
        $this->get(route('member.profile'))
            ->assertOk()
            ->assertSee('Orang Tua Nara')
            ->assertSee('Alamat Nara')
            ->assertSee('Ubah kata sandi')
            ->assertSee('name="new_password_confirmation"', false);

        $this->post(route('logout'))
            ->assertRedirect(route('admin.signin'));
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($student, 'member');

        $this->from(route('admin.signin'))
            ->post(route('admin.signin.store'), [
                'email' => 'nara@example.com',
                'password' => 'password-lama',
            ])
            ->assertRedirect(route('admin.signin'))
            ->assertSessionHasErrors('email');
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($student, 'member');
    }

    public function test_student_can_change_password_without_losing_member_session(): void
    {
        $student = User::factory()->create([
            'email' => 'nara@example.com',
            'password' => 'password-lama',
            'role' => User::ROLE_STUDENT,
        ]);

        $this->actingAs($student, 'member')
            ->put(route('member.profile.password'), [
                'current_password' => 'password-lama',
                'new_password' => 'password-baru',
                'new_password_confirmation' => 'password-baru',
            ])
            ->assertRedirect(route('member.profile'))
            ->assertSessionHas('status', 'Kata sandi berhasil diubah.');

        $this->assertAuthenticatedAs($student, 'member');
        $this->assertTrue(Hash::check('password-baru', $student->fresh()->password));
        $this->get(route('member.home'))->assertOk();

        $this->post(route('member.logout'))
            ->assertRedirect(route('member.home'));
        $this->assertGuest('member');

        $this->post(route('member.login.store'), [
            'email' => 'nara@example.com',
            'password' => 'password-baru',
        ])->assertRedirect(route('member.home'));
        $this->assertAuthenticatedAs($student, 'member');
    }

    public function test_staff_and_member_sessions_can_coexist_and_log_out_independently(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Effata',
            'email' => 'admin@example.com',
            'password' => 'password-admin',
            'role' => User::ROLE_MASTER_ADMIN,
        ]);
        $student = User::factory()->create([
            'name' => 'Nara Effata',
            'nickname' => 'Nara',
            'email' => 'nara@example.com',
            'password' => 'password-lama',
            'role' => User::ROLE_STUDENT,
            'status' => 'active',
        ]);

        $this->actingAs($student, 'member')
            ->get(route('admin.signin'))
            ->assertOk();
        $this->post(route('admin.signin.store'), [
            'email' => 'admin@example.com',
            'password' => 'password-admin',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Admin Effata');
        $this->get(route('member.home'))
            ->assertOk()
            ->assertSee('Halo, Nara!');
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<a class="landing-member-link landing-member-name" href="'.route('member.home').'">Nara Effata</a>', false);
        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertAuthenticatedAs($student, 'member');

        $this->post(route('member.logout'))
            ->assertRedirect(route('member.home'));
        $this->assertGuest('member');
        $this->assertAuthenticatedAs($admin, 'web');

        $this->post(route('member.login.store'), [
            'email' => 'nara@example.com',
            'password' => 'password-lama',
        ])->assertRedirect(route('member.home'));
        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertAuthenticatedAs($student, 'member');

        $this->post(route('logout'))
            ->assertRedirect(route('admin.signin'));
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($student, 'member');
        $this->get(route('member.home'))->assertOk();
    }

    public function test_non_students_cannot_use_an_authenticated_member_guard(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_MASTER_ADMIN]);

        $this->actingAs($admin, 'web')
            ->get(route('member.home'))
            ->assertOk()
            ->assertSee('PORTAL MEMBER MURID');
        $this->get(route('member.profile'))
            ->assertRedirect(route('member.home'));

        $this->actingAs($admin, 'member')
            ->get(route('member.profile'))
            ->assertForbidden();
        $this->get(route('member.home'))->assertForbidden();
    }
}
