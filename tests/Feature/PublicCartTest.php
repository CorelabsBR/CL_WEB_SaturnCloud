<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicCartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'catalog.web' => [['id' => 'web-basic', 'nome' => 'Web de teste', 'preco' => 120]],
            'catalog.vps' => [['id' => 'vps-basic', 'nome' => 'VPS de teste', 'preco' => 40]],
            'portal.url' => null,
            'portal.cart_url' => null,
        ]);
    }

    public function test_public_pages_and_cart_work_without_a_database(): void
    {
        DB::shouldReceive('connection')->never();
        foreach (['/', '/web', '/vps', '/carrinho', '/sobre', '/ajuda', '/conteudos', '/sitemap.xml'] as $uri) {
            $this->get($uri)->assertOk()->assertDontSee('href="'.url('/login').'"', false);
        }
        $this->post('/carrinho/produto/web-basic', ['quantidade' => 2])
            ->assertRedirect(route('cart.index'))->assertSessionHas('cart.web-basic', 2);
        $this->post('/carrinho/produto/web-basic')->assertSessionHas('cart.web-basic', 3);
        $this->get('/carrinho')->assertOk()->assertSee('Web de teste')->assertSee('disponível em breve');
        $this->patch('/carrinho/update/web-basic', ['quantidade' => 4])->assertSessionHas('cart.web-basic', 4);
        $this->delete('/carrinho/remove/web-basic')->assertSessionMissing('cart.web-basic');
        $this->get('/carrinho')->assertSee('Seu carrinho está vazio');
    }

    public function test_portal_handoff_sends_only_identifiers_and_quantities(): void
    {
        config(['portal.cart_url' => 'https://portal.example.com/carrinho/importar']);
        $this->post('/carrinho/produto/web-basic', ['quantidade' => 2, 'preco' => 0]);
        $response = $this->get('/carrinho')->assertOk()
            ->assertSee('action="https://portal.example.com/carrinho/importar"', false)
            ->assertSee('name="items[0][id]" value="web-basic"', false)
            ->assertSee('name="items[0][quantity]" value="2"', false)
            ->assertDontSee('name="items[0][price]"', false)
            ->assertSee('Continuar no portal');
        preg_match('~<form method="POST" action="https://portal.example.com/carrinho/importar">(.*?)</form>~s', $response->getContent(), $matches);
        $this->assertArrayHasKey(1, $matches);
        $this->assertStringNotContainsString('_token', $matches[1]);
    }

    public function test_invalid_input_and_unknown_products_cannot_enter_cart(): void
    {
        $this->post('/carrinho/produto/unknown')->assertNotFound();
        $this->post('/carrinho/produto/web-basic', ['quantidade' => 0])->assertSessionHasErrors('quantidade');
        $this->post('/carrinho/produto/web-basic', ['quantidade' => 100])->assertSessionHasErrors('quantidade');
        $this->post('/carrinho/produto/web-basic', ['quantidade' => 99]);
        $this->post('/carrinho/produto/web-basic')->assertSessionHas('cart.web-basic', 99);
        $this->delete('/carrinho/clear')->assertSessionMissing('cart');
    }

    public function test_removed_products_and_unsafe_portal_urls_are_not_forwarded(): void
    {
        config(['portal.cart_url' => 'javascript:alert(1)']);
        $this->withSession(['cart' => ['removed' => 1, 'web-basic' => 1]])
            ->get('/carrinho')->assertOk()->assertDontSee('javascript:', false)
            ->assertDontSee('value="removed"', false)->assertDontSee('Continuar no portal');
    }
}
