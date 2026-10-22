<?php

namespace Tests\Feature\Concerns;

use App\Models\User;

trait AuthenticatesAsMasterAdmin
{
    protected function signInAsMasterAdmin(): void
    {
        $this->actingAs(User::factory()->create([
            'name' => 'Test Master Admin',
            'role' => User::ROLE_MASTER_ADMIN,
        ]));
    }
}
