<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FrontendCatalogTest extends TestCase
{
    public function test_vps_view_preserves_product_values_and_purchase_contract(): void
    {
        $product = new Product([
            'nome' => 'Configuração de teste',
            'preco' => '87.43',
            'ram' => '7 GB de teste',
            'armazenamento' => '43 GB de teste',
            'processamento' => '3 vCPU de teste',
            'ddos_protection' => false,
        ]);
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

    public function test_home_has_a_static_fallback_without_javascript(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('img/saturno_branco_quadrado.svg', false)
            ->assertSee('data-saturn-scene', false)
            ->assertDontSee('Ativar animação')
            ->assertSee(route('pages.show', 'sobre'), false);
    }

    public function test_authenticated_commercial_routes_keep_their_access_checks(): void
    {
        foreach (['/carrinho', '/checkout', '/painel', '/suporte'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }
}
