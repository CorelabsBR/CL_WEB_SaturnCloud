<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ServerErrorPageTest extends TestCase
{
    public function test_server_error_shows_the_engineer_without_depending_on_the_shared_layout(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/test-station-server-error', function () {
            throw new RuntimeException('Test server failure');
        });

        $this->get('/test-station-server-error')
            ->assertStatus(500)
            ->assertSee('Houston, temos')
            ->assertSee('um problema.')
            ->assertSee('img/space-engineer-server-500.png', false)
            ->assertSee('Tentar novamente')
            ->assertSee('Voltar ao início')
            ->assertDontSee('Test server failure')
            ->assertDontSee('support-widget', false);
    }
}
