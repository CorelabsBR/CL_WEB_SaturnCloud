<?php

namespace Tests\Feature;

use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    public function test_missing_page_shows_the_astronaut_and_recovery_links(): void
    {
        $this->get('/esta-pagina-nao-existe-saturn-404')
            ->assertNotFound()
            ->assertSee('Tentando estabelecer')
            ->assertSee('Contato com a nave')
            ->assertSee('img/astronaut-brazil-cutout-404.png', false)
            ->assertSee('css/error404.css', false)
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('href="'.route('help.index').'"', false);
    }
}
