<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_presents_the_available_catalog_without_unverified_claims(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Hospedagem de sites')
            ->assertSee('Servidores VPS')
            ->assertSee('INFRAESTRUTURA')
            ->assertSee('SEM LIMITES.')
            ->assertSee('data-saturn-scene', false)
            ->assertSee('Infraestrutura sem caixa-preta')
            ->assertDontSee('99.9%')
            ->assertDontSee('24/7')
            ->assertDontSee('Sistemas online');
    }

    public function test_public_institutional_routes_are_available(): void
    {
        foreach (['/modelo-saturno', '/vps', '/vps/linux', '/vps/windows', '/ajuda', '/ajuda/o-que-e-hospedagem-de-sites', '/revenda', '/dominios', '/infraestrutura', '/sobre', '/faq', '/contato', '/sla', '/termos', '/politica-privacidade', '/reembolso'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }
}
