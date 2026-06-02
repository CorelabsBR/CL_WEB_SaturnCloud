<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JogoProd extends Model
{
    protected $table = 'jogos_prod';

    public $timestamps = false;

    public const FONT_OPTIONS = [
        'default' => 'Fonte padrão',
        'minecraft' => 'Minecraft',
        'terraria' => 'Terraria',
        'gta' => 'GTA',
    ];

    private const FONT_CSS = [
        'minecraft' => "'Minecraft', monospace",
        'terraria' => "'Terraria', 'Trebuchet MS', system-ui, sans-serif",
        'gta' => "'GTA-Font', Impact, 'Arial Black', sans-serif",
    ];

    protected $fillable = [
        'nome',
        'slug',
        'banner',
        'hero_banner',
        'icon',
        'font_family',
        'ativo',
        'ativo_admin',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ativo_admin' => 'boolean',
    ];

    public function plans()
    {
        return $this->hasMany(Plan::class, 'jogo_id');
    }

    public function fontFamilyCss(): string
    {
        return self::FONT_CSS[$this->font_family] ?? "'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif";
    }

    public function bannerUrl(): ?string
    {
        if (! $this->banner) {
            return null;
        }

        return asset('img/games/' . $this->banner);
    }

    public function cardImageUrl(): string
    {
        return $this->bannerUrl() ?? $this->heroImageUrl();
    }

    public function heroBannerUrl(): ?string
    {
        if (! $this->hero_banner) {
            return null;
        }

        return asset('img/games/' . $this->hero_banner);
    }

    public function heroImageUrl(): string
    {
        return $this->heroBannerUrl() ?? $this->bannerUrl() ?? asset($this->fallbackHeroImage());
    }

    public function iconUrl(): ?string
    {
        if (! $this->icon) {
            return null;
        }

        if (file_exists(public_path('img/games/icons/' . $this->icon))) {
            return asset('img/games/icons/' . $this->icon);
        }

        if (file_exists(public_path('img/games/' . $this->icon))) {
            return asset('img/games/' . $this->icon);
        }

        return null;
    }

    private function fallbackHeroImage(): string
    {
        return match ($this->slug) {
            'gta-v' => 'img/games/gta-v-banner.webp',
            'gta-sa' => 'img/games/80601.jpg',
            'minecraft-java' => 'img/games/minecraft/fundo_mine.png',
            'minecraft-bedrock' => 'img/games/minecraft/fundo_minebed.png',
            'terraria' => 'img/terraria_fundo.jpg',
            default => 'img/games/images.jpeg',
        };
    }
}
