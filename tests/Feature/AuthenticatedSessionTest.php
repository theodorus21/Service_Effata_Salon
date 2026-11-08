<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_introduces_effata_and_links_to_signup_and_signin(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Effata Fun Learning')
            ->assertSee('Pelajaran')
            ->assertSee('Musik')
            ->assertSee(route('member.home'), false)
            ->assertSee(route('signup'), false)
            ->assertDontSee(route('admin.signin'), false);
    }

    public function test_guest_is_redirected_to_signin_from_dashboard_and_management_pages(): void
    {
        foreach ([
            route('dashboard'),
            route('learning-classes.index'),
            route('class-schedules.index'),
            route('settings.index'),
            route('master-data.index', ['role' => 'guru']),
        ] as $url) {
            $this->get($url)
                ->assertRedirect(route('admin.signin'));
        }
    }

    public function test_admin_signin_is_a_separate_simple_page(): void
    {
        $this->assertSame('/admin/signin', route('admin.signin', [], false));

        $this->get(route('admin.signin'))
            ->assertOk()
            ->assertSee(route('admin.signin.store'), false)
            ->assertDontSee(route('signup'), false)
            ->assertDontSee(route('member.home'), false);

        $this->get('/signin')->assertNotFound();
    }

    public function test_authenticated_pages_use_the_admin_path_prefix(): void
    {
        $this->assertSame('/admin', route('dashboard', [], false));
        $this->assertSame('/admin/learning-classes', route('learning-classes.index', [], false));
        $this->assertSame('/admin/settings', route('settings.index', [], false));
        $this->assertSame('/admin/role-access', route('role-access.index', [], false));

        $this->get('/dashboard')->assertNotFound();
        $this->get('/learning-classes')->assertNotFound();
    }

    public function test_user_can_sign_in_and_access_dashboard_routes(): void
    {
        $user = User::factory()->create([
            'name' => 'Theo Master',
            'email' => 'theomaster@effata.com',
            'role' => User::ROLE_MASTER_ADMIN,
            'password' => '1q2w3e4r5t',
        ]);

        $this->get(route('learning-classes.index'))
            ->assertRedirect(route('admin.signin'));

        $this->post(route('admin.signin.store'), [
            'email' => 'theomaster@effata.com',
            'password' => '1q2w3e4r5t',
        ])->assertRedirect(route('learning-classes.index'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Theo Master')
            ->assertSee(route('settings.index'), false);
        $this->get(route('learning-classes.index'))->assertOk();
    }

    public function test_brand_logo_is_used_on_signin_dashboard_and_as_favicon(): void
    {
        $logoUrl = asset('images/effata-logo.jpg');

        $this->get(route('admin.signin'))
            ->assertOk()
            ->assertSee('rel="icon" type="image/jpeg" href="'.$logoUrl.'"', false)
            ->assertSee('class="admin-login-brand"', false)
            ->assertSee($logoUrl, false);

        $this->get(route('member.home'))
            ->assertOk()
            ->assertSee('class="brand-logo" src="'.$logoUrl.'"', false);

        $this->get(route('signup'))
            ->assertOk()
            ->assertSee('rel="icon" type="image/jpeg" href="'.$logoUrl.'"', false)
            ->assertSee('class="brand-logo" src="'.$logoUrl.'"', false);

        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_MASTER_ADMIN,
        ]))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('rel="icon" type="image/jpeg" href="'.$logoUrl.'"', false)
            ->assertSee('class="dashboard-logo" src="'.$logoUrl.'"', false);

        $this->assertFileExists(public_path('images/effata-logo.jpg'));
    }

    public function test_invalid_credentials_return_to_signin_with_error(): void
    {
        User::factory()->create([
            'email' => 'theomaster@effata.com',
            'password' => 'correct-password',
        ]);

        $this->from(route('admin.signin'))
            ->post(route('admin.signin.store'), [
                'email' => 'theomaster@effata.com',
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('admin.signin'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_log_out_and_session_is_invalidated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('admin.signin'));

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('admin.signin'));
    }
}
