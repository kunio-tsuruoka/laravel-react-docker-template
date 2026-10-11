<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_resolves_its_react_component(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
                ->where('auth.user', null)
                ->where('canResetPassword', true)
            );
    }

    public function test_authenticated_navigation_returns_inertia_page_data(): void
    {
        $user = User::factory()->create();
        $version = $this->actingAs($user)->get('/dashboard')->viewData('page')['version'];

        $this->actingAs($user)
            ->get('/dashboard', [
                'X-Inertia' => 'true',
                'X-Inertia-Version' => $version,
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Dashboard')
            ->assertJsonPath('props.auth.user.id', $user->id)
            ->assertJsonMissingPath('props.auth.user.password');
    }
}
