<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ForbiddenPageTest extends TestCase
{
    public function test_forbidden_page_shows_the_guard_and_recovery_links(): void
    {
        Route::middleware('web')->get('/test-station-restricted', fn () => abort(403));

        $this->get('/test-station-restricted')
            ->assertForbidden()
            ->assertSee('Acesso negado.')
            ->assertSee('Área restrita da estação.')
            ->assertSee('img/station-guard-403.png', false)
            ->assertSee('css/error403.css', false)
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('href="'.route('help.index').'"', false);
    }
}
