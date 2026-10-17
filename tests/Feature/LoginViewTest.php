<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginViewTest extends TestCase
{
    public function test_login_form_exposes_the_real_authentication_actions(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee(route('password.request'), false)
            ->assertSee(route('social.redirect', 'google'), false)
            ->assertSee(route('social.redirect', 'discord'), false);
    }
}
