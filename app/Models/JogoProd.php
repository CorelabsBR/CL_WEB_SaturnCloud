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

    private const RENAMED_GAME_IMAGES = [
        '80601.jpg' => 'game-gta-sa-card-banner.jpg',
        'gtasa-ps2img2-si0mcz1x3r-1-1-6a1f11706b6a1.png' => 'game-gta-sa-card-banner.jpg',
        'download-6a27150ecb010.jpg' => 'game-minecraft-java-card-banner.jpg',
        'download-6a1ddb7d806a9.jpg' => 'game-minecraft-java-card-banner.jpg',
        'lonely-but-warm-6a27150ecb303.jpg' => 'game-minecraft-java-hero.jpg',
        'lonely-but-warm-6a1ddb072839d.jpg' => 'game-minecraft-java-hero.jpg',
        'lonely-but-warm-6a1f0a1346668.jpg' => 'game-minecraft-java-hero.jpg',
        'download.jpeg' => 'game-minecraft-bedrock-card-banner.jpeg',
        'aldea-minecraft-6a271540d6e9f.jpg' => 'game-minecraft-bedrock-hero.jpg',
        'aldea-minecraft-6a1f0aa559e58.jpg' => 'game-minecraft-bedrock-hero.jpg',
        'terraria.jpeg' => 'game-terraria-card-banner.jpeg',
        'terraria-map-sunny-1-1-6a27155a165f8.jpg' => 'game-terraria-hero.jpg',
        'terraria-map-sunny-1-1-6a1f0b4020ad4.jpg' => 'game-terraria-hero.jpg',
        'terraria-fundo-6a1891a3724e5.jpg' => 'game-terraria-page-background.jpg',
        'gta-v-banner.webp' => 'game-gta-v-card-banner.webp',
        'a-historia-de-grand-theft-auto-v-6a17285157d8c.jpg' => 'game-gta-v-card-banner.webp',
        'novas-imagens-setembro-de-2014-6a271571f0725.jpg' => 'game-gta-v-hero.jpg',
        'novas-imagens-setembro-de-2014-6a1f0e7482288.jpg' => 'game-gta-v-hero.jpg',
        'novas-imagens-setembro-de-2014-6a1f0eb1e9fae.jpg' => 'game-gta-v-hero.jpg',
        'lb-6a27159343f8d.jpg' => 'game-gta-sa-hero.jpg',
        'lb-6a1f11b63427b.jpg' => 'game-gta-sa-hero.jpg',
        'rtx-beamng-steam-banner-v0-38ms0dl77yrb1.png' => 'game-beamng-drive-card-banner.png',
        'red-dead-redemption-2-desktop-6l9sm1fd0wc2ibs0.jpg' => 'game-rdr2-card-banner.jpg',
        'share.jpg' => 'game-hogwarts-legacy-card-banner.jpg',
        'ff297d0b3712aa48ec0f7e3c0e48e1dc.jpg' => 'game-arma-3-card-banner.jpg',
        'images.jpeg' => 'game-default-card-banner.jpeg',
    ];

    private const RENAMED_GAME_ICONS = [
        'minecraftjava.png' => 'icon-minecraft-java.png',
        'minecraftbedrock.png' => 'icon-minecraft-bedrock.png',
        'terraria.png' => 'icon-terraria.png',
        'gtasa.png' => 'icon-gta-sa.png',
        '21515-64x64x32-6a1ddbdae7094.png' => 'icon-gta-v.png',
        '20343-256x256x8-6a1f140c6945e.png' => 'icon-beamng-drive.png',
        'wooden-magic-wand-isolated-on-transparent-background-png-6a1ddd4b8423c.webp' => 'icon-hogwarts-legacy.webp',
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

        return $this->gameImageAsset($this->banner);
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

        return $this->gameImageAsset($this->hero_banner);
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

        $icon = self::RENAMED_GAME_ICONS[$this->icon] ?? $this->icon;

        if (file_exists(public_path('img/games/icons/' . $icon))) {
            return asset('img/games/icons/' . $icon);
        }

        if (file_exists(public_path('img/games/' . $icon))) {
            return asset('img/games/' . $icon);
        }

        return null;
    }

    private function gameImageAsset(string $image): ?string
    {
        $image = self::RENAMED_GAME_IMAGES[$image] ?? $image;

        if (! file_exists(public_path('img/games/' . $image))) {
            return null;
        }

        return asset('img/games/' . $image);
    }

    private function fallbackHeroImage(): string
    {
        return match ($this->slug) {
            'gta-v' => 'img/games/game-gta-v-card-banner.webp',
            'gta-sa' => 'img/games/game-gta-sa-card-banner.jpg',
            'minecraft-java' => 'img/games/minecraft/game-minecraft-java-page-background.jpg',
            'minecraft-bedrock' => 'img/games/minecraft/game-minecraft-bedrock-page-background.jpg',
            'terraria' => 'img/games/game-terraria-page-background.jpg',
            default => 'img/games/game-default-card-banner.jpeg',
        };
    }
}
