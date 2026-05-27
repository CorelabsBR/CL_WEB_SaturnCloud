<?php

namespace App\Http\Controllers;

use App\Models\JogoProd;
use Illuminate\Support\Collection;

class GameHostingController extends Controller
{
    public function index()
    {
        try {
            $jogos = JogoProd::query()
                ->where('ativo', true)
                ->orderBy('id', 'asc')
                ->get();

            if ($jogos->isEmpty()) {
                $jogos = $this->fallbackJogos();
            }
        } catch (\Throwable $e) {
            $jogos = $this->fallbackJogos();
        }

        return view('games.index', compact('jogos'));
    }

    public function show(JogoProd $jogo)
    {
        abort_if(! $jogo->ativo, 404);

        $plans = $jogo->plans()
            ->orderBy('plan', 'asc')
            ->get();

        $customView = 'games.custom.' . $jogo->slug;

        if ($jogo->slug && view()->exists($customView)) {
            return view($customView, compact('jogo', 'plans'));
        }

        return view('games.show', compact('jogo', 'plans'));
    }

    private function fallbackJogos(): Collection
    {
        return collect([
            (object) [
                'nome' => 'Minecraft',
                'slug' => 'minecraft',
                'banner' => null,
                'ativo' => true,
            ],
            (object) [
                'nome' => 'ARK Survival',
                'slug' => 'ark-survival',
                'banner' => null,
                'ativo' => true,
            ],
            (object) [
                'nome' => 'CS2',
                'slug' => 'cs2',
                'banner' => null,
                'ativo' => true,
            ],
        ]);
    }
}
