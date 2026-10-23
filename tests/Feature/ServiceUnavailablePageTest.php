<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ServiceUnavailablePageTest extends TestCase
{
    public function test_service_unavailable_uses_the_station_page_and_preserves_retry_header(): void
    {
        config(['app.debug' => false]);
        Route::get('/test-station-maintenance', function () {
            abort(503, 'Internal maintenance detail', ['Retry-After' => '120']);
        });

        $this->get('/test-station-maintenance?source=status')
            ->assertStatus(503)
            ->assertHeader('Retry-After', '120')
            ->assertSee('Estação espacial')
            ->assertSee('em manutenção.')
            ->assertSee('Tentar novamente')
            ->assertSee(url('/test-station-maintenance').'?source=status', false)
            ->assertDontSee('Internal maintenance detail')
            ->assertDontSee('support-widget', false);
    }
}
