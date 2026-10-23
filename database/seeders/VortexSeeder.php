<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VortexSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('cart_items')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('products')->truncate();
        DB::table('plans')->truncate();
        DB::table('pterodactyl_nodes')->truncate();
        DB::table('pterodactyl_eggs')->truncate();
        DB::table('pterodactyl_location')->truncate();
        DB::table('jogos_prod')->truncate();
        DB::table('location_prod')->truncate();
        DB::table('categoria_prod')->truncate();

        Schema::enableForeignKeyConstraints();

        DB::table('categoria_prod')->insert([
            ['id' => 1, 'nome' => 'Hospedagem Web'],
            ['id' => 2, 'nome' => 'Servidores de Jogos'],
            ['id' => 3, 'nome' => 'VPS'],
        ]);

        DB::table('location_prod')->insert([
            ['id' => 1, 'localidade' => 'Brasil', 'ativo' => true],
        ]);

        DB::table('jogos_prod')->insert([
            ['id' => 1, 'nome' => 'Minecraft Java', 'slug' => 'minecraft-java', 'banner' => 'game-minecraft-java-card-banner.jpg', 'hero_banner' => 'game-minecraft-java-hero.jpg', 'icon' => 'icon-minecraft-java.png', 'font_family' => 'minecraft', 'ativo' => true],
            ['id' => 2, 'nome' => 'Minecraft Bedrock', 'slug' => 'minecraft-bedrock', 'banner' => 'game-minecraft-bedrock-card-banner.jpeg', 'hero_banner' => 'game-minecraft-bedrock-hero.jpg', 'icon' => 'icon-minecraft-bedrock.png', 'font_family' => 'minecraft', 'ativo' => true],
            ['id' => 3, 'nome' => 'Terraria', 'slug' => 'terraria', 'banner' => 'game-terraria-card-banner.jpeg', 'hero_banner' => 'game-terraria-hero.jpg', 'icon' => 'icon-terraria.png', 'font_family' => 'terraria', 'ativo' => true],
            ['id' => 4, 'nome' => 'GTA V', 'slug' => 'gta-v', 'banner' => 'game-gta-v-card-banner.webp', 'hero_banner' => 'game-gta-v-hero.jpg', 'icon' => 'icon-gta-v.png', 'font_family' => 'gta', 'ativo' => true],
            ['id' => 5, 'nome' => 'GTA SA', 'slug' => 'gta-sa', 'banner' => 'game-gta-sa-card-banner.jpg', 'hero_banner' => 'game-gta-sa-hero.jpg', 'icon' => 'icon-gta-sa.png', 'font_family' => 'gta', 'ativo' => true],
            ['id' => 6, 'nome' => 'BeamNG Drive', 'slug' => 'beamng-drive', 'banner' => 'game-beamng-drive-card-banner.png', 'hero_banner' => null, 'icon' => 'icon-beamng-drive.png', 'font_family' => null, 'ativo' => true],
            ['id' => 7, 'nome' => 'RDR2', 'slug' => 'rdr2', 'banner' => 'game-rdr2-card-banner.jpg', 'hero_banner' => null, 'icon' => null, 'font_family' => null, 'ativo' => true],
            ['id' => 8, 'nome' => 'Hogwarts Legacy', 'slug' => 'hogwarts-legacy', 'banner' => 'game-hogwarts-legacy-card-banner.jpg', 'hero_banner' => null, 'icon' => 'icon-hogwarts-legacy.webp', 'font_family' => null, 'ativo' => true],
            ['id' => 9, 'nome' => 'ARMA 3', 'slug' => 'arma-3', 'banner' => 'game-arma-3-card-banner.jpg', 'hero_banner' => null, 'icon' => null, 'font_family' => null, 'ativo' => true],
        ]);

        DB::table('pterodactyl_location')->insert([
            ['id' => 1, 'name' => 'vtx-sa-1'],
        ]);

        DB::table('pterodactyl_nodes')->insert([
            ['id' => 1, 'fqdn' => 'node.ion.vortex.corelabs.dev.br', 'locationid' => 1],
        ]);

        DB::table('pterodactyl_eggs')->insert([
            ['id' => 1, 'egg_id' => 5, 'name' => 'Minecraft Java'],
            ['id' => 2, 'egg_id' => 78, 'name' => 'Minecraft Bedrock'],
            ['id' => 3, 'egg_id' => 60, 'name' => 'Terraria'],
            ['id' => 4, 'egg_id' => 63, 'name' => 'FiveM'],
            ['id' => 5, 'egg_id' => 66, 'name' => 'MTA'],
            ['id' => 6, 'egg_id' => 79, 'name' => 'BeamMP'],
            ['id' => 7, 'egg_id' => 71, 'name' => 'RedM'],
            ['id' => 8, 'egg_id' => 70, 'name' => 'Hogwarp'],
            ['id' => 9, 'egg_id' => 69, 'name' => 'ARMA3'],
        ]);

DB::table('plans')->insert([
    // minecraft java
    ['plan' => 1, 'name' => 'Tier Carvão', 'jogo_id' => 1, 'ram' => 2000, 'disk' => 40000, 'egg' => 1, 'cpu' => null, 'price' => 1999, 'location_id' => 1],
    ['plan' => 2, 'name' => 'Tier Ferro', 'jogo_id' => 1, 'ram' => 4000, 'disk' => 80000, 'egg' => 1, 'cpu' => null, 'price' => 3849, 'location_id' => 1],
    ['plan' => 3, 'name' => 'Tier Ouro', 'jogo_id' => 1, 'ram' => 8000, 'disk' => 80000, 'egg' => 1, 'cpu' => null, 'price' => 4999, 'location_id' => 1],
    ['plan' => 4, 'name' => 'Tier Diamante', 'jogo_id' => 1, 'ram' => 0, 'disk' => 100000, 'egg' => 1, 'cpu' => null, 'price' => 5949, 'location_id' => 1],
    ['plan' => 5, 'name' => 'Tier Netherite', 'jogo_id' => 1, 'ram' => 0, 'disk' => 0, 'egg' => 1, 'cpu' => null, 'price' => 7049, 'location_id' => 1],

    ['plan' => 6, 'name' => 'Tier Carvão', 'jogo_id' => 2, 'ram' => 2000, 'disk' => 40000, 'egg' => 1, 'cpu' => null, 'price' => 1999, 'location_id' => 1],
    ['plan' => 7, 'name' => 'Tier Ferro', 'jogo_id' => 2, 'ram' => 4000, 'disk' => 80000, 'egg' => 1, 'cpu' => null, 'price' => 3849, 'location_id' => 1],
    ['plan' => 8, 'name' => 'Tier Ouro', 'jogo_id' => 2, 'ram' => 8000, 'disk' => 80000, 'egg' => 1, 'cpu' => null, 'price' => 4999, 'location_id' => 1],
    ['plan' => 9, 'name' => 'Tier Diamante', 'jogo_id' => 2, 'ram' => 0, 'disk' => 100000, 'egg' => 1, 'cpu' => null, 'price' => 5949, 'location_id' => 1],
    ['plan' => 10, 'name' => 'Tier Netherite', 'jogo_id' => 2, 'ram' => 0, 'disk' => 0, 'egg' => 1, 'cpu' => null, 'price' => 7049, 'location_id' => 1],

    // gta v
    ['plan' => 16, 'name' => 'Tier Vagos', 'jogo_id' => 4, 'ram' => 4000, 'disk' => 100000, 'egg' => 4, 'cpu' => null, 'price' => 8000, 'location_id' => 1],
    ['plan' => 17, 'name' => 'Tier Ballas', 'jogo_id' => 4, 'ram' => 8000, 'disk' => 150000, 'egg' => 4, 'cpu' => null, 'price' => 12000, 'location_id' => 1],
    ['plan' => 18, 'name' => 'Tier Marabunta', 'jogo_id' => 4, 'ram' => 12000, 'disk' => 200000, 'egg' => 4, 'cpu' => null, 'price' => 16000, 'location_id' => 1],
    ['plan' => 19, 'name' => 'Tier Aztecas', 'jogo_id' => 4, 'ram' => 16000, 'disk' => 0, 'egg' => 4, 'cpu' => null, 'price' => 18000, 'location_id' => 1],
    ['plan' => 20, 'name' => 'Tier Merriweather', 'jogo_id' => 4, 'ram' => 0, 'disk' => 0, 'egg' => 4, 'cpu' => null, 'price' => 22000, 'location_id' => 1],

    // terraria
    ['plan' => 11, 'name' => 'Tier Rei Slime', 'jogo_id' => 3, 'ram' => 2000, 'disk' => 20000, 'egg' => 3, 'cpu' => null, 'price' => 2000, 'location_id' => 1],
    ['plan' => 12, 'name' => 'Tier Olho de Cthulhu', 'jogo_id' => 3, 'ram' => 4000, 'disk' => 40000, 'egg' => 3, 'cpu' => null, 'price' => 4000, 'location_id' => 1],
    ['plan' => 13, 'name' => 'Tier Devorador de Mundos', 'jogo_id' => 3, 'ram' => 8000, 'disk' => 60000, 'egg' => 3, 'cpu' => null, 'price' => 7500, 'location_id' => 1],
    ['plan' => 14, 'name' => 'Tier Mechdusa', 'jogo_id' => 3, 'ram' => 12000, 'disk' => 0, 'egg' => 3, 'cpu' => null, 'price' => 9000, 'location_id' => 1],
    ['plan' => 15, 'name' => 'Tier EverScream', 'jogo_id' => 3, 'ram' => 0, 'disk' => 0, 'egg' => 3, 'cpu' => null, 'price' => 12000, 'location_id' => 1],

    // gta sa
    ['plan' => 21, 'name' => 'Tier Grove Street', 'jogo_id' => 5, 'ram' => 2000, 'disk' => 10000, 'egg' => 5, 'cpu' => 2, 'price' => 900, 'location_id' => 1],
    ['plan' => 22, 'name' => 'Tier Ballas', 'jogo_id' => 5, 'ram' => 4000, 'disk' => 20000, 'egg' => 5, 'cpu' => 4, 'price' => 2150, 'location_id' => 1],
    ['plan' => 25, 'name' => 'Tier Aztecas', 'jogo_id' => 5, 'ram' => 12000, 'disk' => 0, 'egg' => 5, 'cpu' => null, 'price' => 3450, 'location_id' => 1],
    ['plan' => 24, 'name' => 'Tier Vagos', 'jogo_id' => 5, 'ram' => 8000, 'disk' => 90000, 'egg' => 5, 'cpu' => null, 'price' => 2850, 'location_id' => 1],
    ['plan' => 26, 'name' => 'Tier San Fierro Rifa', 'jogo_id' => 5, 'ram' => 0, 'disk' => 0, 'egg' => 5, 'cpu' => null, 'price' => 4500, 'location_id' => 1],

    // beamng drive
    ['plan' => 27, 'name' => 'Tier Pigeon', 'jogo_id' => 6, 'ram' => 2000, 'disk' => 40000, 'egg' => 6, 'cpu' => null, 'price' => 2500, 'location_id' => 1],
    ['plan' => 28, 'name' => 'Tier Wigeon', 'jogo_id' => 6, 'ram' => 4000, 'disk' => 60000, 'egg' => 6, 'cpu' => null, 'price' => 4200, 'location_id' => 1],
    ['plan' => 29, 'name' => 'Tier Piccolina', 'jogo_id' => 6, 'ram' => 6000, 'disk' => 0, 'egg' => 6, 'cpu' => null, 'price' => 6000, 'location_id' => 1],
    ['plan' => 30, 'name' => 'Tier Bastion', 'jogo_id' => 6, 'ram' => 8000, 'disk' => 0, 'egg' => 6, 'cpu' => null, 'price' => 8000, 'location_id' => 1],
    ['plan' => 31, 'name' => 'Tier Bolide', 'jogo_id' => 6, 'ram' => 0, 'disk' => 0, 'egg' => 6, 'cpu' => null, 'price' => 10000, 'location_id' => 1],

    // rdr2
    ['plan' => 32, 'name' => 'Tier Van der Linde', 'jogo_id' => 7, 'ram' => 4000, 'disk' => 40000, 'egg' => 7, 'cpu' => 2, 'price' => 9000, 'location_id' => 1],
    ['plan' => 33, 'name' => 'Tier O’Driscoll', 'jogo_id' => 7, 'ram' => 8000, 'disk' => 80000, 'egg' => 7, 'cpu' => 4, 'price' => 15000, 'location_id' => 1],
    ['plan' => 34, 'name' => 'Tier Murfree Brood', 'jogo_id' => 7, 'ram' => 8000, 'disk' => 100000, 'egg' => 7, 'cpu' => null, 'price' => 19000, 'location_id' => 1],
    ['plan' => 35, 'name' => 'Tier Lemoyne Raiders', 'jogo_id' => 7, 'ram' => 16000, 'disk' => 0, 'egg' => 7, 'cpu' => null, 'price' => 35000, 'location_id' => 1],
    ['plan' => 36, 'name' => 'Tier Del Lobo', 'jogo_id' => 7, 'ram' => 0, 'disk' => 0, 'egg' => 7, 'cpu' => null, 'price' => 40000, 'location_id' => 1],

    // hogwarts legacy
    ['plan' => 37, 'name' => 'Tier Sonserina', 'jogo_id' => 8, 'ram' => 4000, 'disk' => 40000, 'egg' => 8, 'cpu' => null, 'price' => 9000, 'location_id' => 1],
    ['plan' => 38, 'name' => 'Tier Corvinal', 'jogo_id' => 8, 'ram' => 8000, 'disk' => 60000, 'egg' => 8, 'cpu' => null, 'price' => 13000, 'location_id' => 1],
    ['plan' => 39, 'name' => 'Tier Lufa-Lufa', 'jogo_id' => 8, 'ram' => 12000, 'disk' => 80000, 'egg' => 8, 'cpu' => null, 'price' => 18000, 'location_id' => 1],
    ['plan' => 40, 'name' => 'Tier Grifinória', 'jogo_id' => 8, 'ram' => 16000, 'disk' => 0, 'egg' => 8, 'cpu' => null, 'price' => 23000, 'location_id' => 1],
    ['plan' => 41, 'name' => 'Tier Pukwudgie', 'jogo_id' => 8, 'ram' => 0, 'disk' => 0, 'egg' => 8, 'cpu' => null, 'price' => 25000, 'location_id' => 1],

    // arma 3
    ['plan' => 42, 'name' => 'Tier CSAT', 'jogo_id' => 9, 'ram' => 4000, 'disk' => 40000, 'egg' => 9, 'cpu' => null, 'price' => 4900, 'location_id' => 1],
    ['plan' => 43, 'name' => 'Tier NATO', 'jogo_id' => 9, 'ram' => 8000, 'disk' => 60000, 'egg' => 9, 'cpu' => null, 'price' => 8900, 'location_id' => 1],
    ['plan' => 44, 'name' => 'Tier Independent', 'jogo_id' => 9, 'ram' => 12000, 'disk' => 80000, 'egg' => 9, 'cpu' => null, 'price' => 12900, 'location_id' => 1],
    ['plan' => 45, 'name' => 'Tier Civilian', 'jogo_id' => 9, 'ram' => 16000, 'disk' => 0, 'egg' => 9, 'cpu' => null, 'price' => 16900, 'location_id' => 1],
    ['plan' => 46, 'name' => 'Tier Guerrilha', 'jogo_id' => 9, 'ram' => 0, 'disk' => 0, 'egg' => 9, 'cpu' => null, 'price' => 19999, 'location_id' => 1],
]);


        DB::table('products')->insert([
            [
                'nome' => 'Tier Amador',
                'descricao' => 'Plano inicial para sites pequenos.',
                'preco' => 32.00,
                'categoria_id' => 1,
                'jogo_id' => null,
                'plan' => null,
                'local_id' => 1,
                'ram' => '500 MB',
                'armazenamento' => '2 GB',
                'processamento' => '-',
                'bancos_dados' => '2',
                'dominios' => '1',
                'ddos_protection' => false,
                'featured' => false,
                'sort_order' => 1,
            ],
            [
                'nome' => 'Tier Profissional',
                'descricao' => 'Plano intermediário para projetos profissionais.',
                'preco' => 45.00,
                'categoria_id' => 1,
                'jogo_id' => null,
                'plan' => null,
                'local_id' => 1,
                'ram' => '1 GB',
                'armazenamento' => '10 GB',
                'processamento' => '-',
                'bancos_dados' => '5',
                'dominios' => '2',
                'ddos_protection' => false,
                'featured' => true,
                'sort_order' => 2,
            ],
            [
                'nome' => 'Tier Elite',
                'descricao' => 'Plano avançado com recursos ilimitados.',
                'preco' => 60.00,
                'categoria_id' => 1,
                'jogo_id' => null,
                'plan' => null,
                'local_id' => 1,
                'ram' => 'Ilimitado',
                'armazenamento' => 'Ilimitado',
                'processamento' => '-',
                'bancos_dados' => 'Ilimitado',
                'dominios' => 'Ilimitado',
                'ddos_protection' => false,
                'featured' => false,
                'sort_order' => 3,
            ],
        ]);
    }
}
