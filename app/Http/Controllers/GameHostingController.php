<?php

namespace App\Http\Controllers;

use App\Models\JogoProd;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class GameHostingController extends Controller
{
    public function index()
    {
        try {
            $isAdmin = $this->isAdmin();

            $jogos = JogoProd::query()
                ->when($isAdmin, fn ($query) => $query->where('ativo_admin', true))
                ->when(! $isAdmin, fn ($query) => $query->where('ativo', true))
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
        abort_if(! $this->canViewGame($jogo), 404);

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

    private function canViewGame(JogoProd $jogo): bool
    {
        return $this->isAdmin()
            ? (bool) $jogo->ativo_admin
            : (bool) $jogo->ativo;
    }

    private function isAdmin(): bool
    {
        return Auth::check() && Auth::user()->tipo_usuario === 'root';
    }
}
