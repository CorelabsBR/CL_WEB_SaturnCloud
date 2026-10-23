<?php

namespace Tests\Feature;

use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FrontendCatalogTest extends TestCase
{
    public function test_vps_view_preserves_product_values_and_purchase_contract(): void
    {
        $product = (object) [
            'nome' => 'Configuração de teste',
            'preco' => '87.43',
            'ram' => '7 GB de teste',
            'armazenamento' => '43 GB de teste',
            'processamento' => '3 vCPU de teste',
            'ddos_protection' => false,
            'featured' => false,
            'descricao' => null,
        ];
        $product->id = 987;

        $this->view('vps.index', ['produtos' => collect([$product]), 'errors' => new ViewErrorBag])
            ->assertSee('87,43')
            ->assertSee('7 GB de teste')
            ->assertSee('43 GB de teste')
            ->assertSee('3 vCPU de teste')
            ->assertSee('Conforme plano')
            ->assertSee(route('cart.addProduct', 987), false)
            ->assertSee('method="POST"', false)
            ->assertSee('name="_token"', false)
            ->assertDontSee('99.9%');
    }

    public function test_home_reserves_the_scene_without_a_placeholder_logo(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('class="saturn-artwork"', false)
            ->assertSee('data-saturn-scene', false)
            ->assertDontSee('Ativar animação')
            ->assertSee(route('pages.show', 'sobre'), false);
    }

    public function test_header_logo_uses_an_available_public_asset(): void
    {
        $this->assertFileExists(public_path('img/saturno_branco_quadrado.svg'));

        $this->get('/')
            ->assertOk()
            ->assertSee('img/saturno_branco_quadrado.svg', false)
            ->assertSee('class="site-header__logo"', false)
            ->assertDontSee('</Saturn>', false);
    }

    public function test_production_manifest_references_existing_assets(): void
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach ($manifest as $entry => $chunk) {
            $this->assertFileExists(public_path('build/'.$chunk['file']), $entry);

            foreach (array_merge($chunk['imports'] ?? [], $chunk['dynamicImports'] ?? []) as $dependency) {
                $this->assertArrayHasKey($dependency, $manifest, $entry);
            }
        }
    }

    public function test_former_application_routes_are_not_available(): void
    {
        foreach (['/login', '/registro', '/checkout', '/painel', '/admin', '/suporte', '/recuperar-senha', '/auth/google/redirect'] as $uri) {
            $this->get($uri)->assertNotFound();
        }
        foreach (['/login', '/checkout/pagar', '/webhooks/mercadopago', '/marketing/events'] as $uri) {
            $this->post($uri)->assertNotFound();
        }
    }
}
