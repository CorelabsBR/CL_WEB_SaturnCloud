<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pterodactyl_eggs')
            ->where('id', 9)
            ->update([
                'docker_image' => 'ghcr.io/pterodactyl/yolks:debian',
                'docker_images' => json_encode([
                    'ghcr.io/pterodactyl/yolks:debian' => 'ghcr.io/pterodactyl/yolks:debian',
                ]),
                'updated_at' => now(),
            ]);

        DB::table('pterodactyl_egg_variables')->updateOrInsert(
            ['pterodactyl_egg_id' => 9, 'env_variable' => 'SERVER_BINARY'],
            [
                'name' => 'Server Binary',
                'default_value' => 'arma3server_x64',
                'rules' => 'required|string',
                'required' => 1,
                'user_viewable' => 1,
                'user_editable' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $variables = [
            ['RCON_PORT', '[Host] RCon Port', '2306', 'required|numeric', 1, 1],
            ['STEAMCMD_BETAID', 'Arma 3 Branch', 'public', 'required|string', 1, 1],
            ['PARAM_NOLOGS', 'Disable Verbose Logging', '0', 'required|boolean', 1, 1],
            ['PARAM_LOADMISSIONTOMEMORY', 'Load Mission To Memory', '0', 'required|boolean', 1, 1],
            ['PARAM_AUTOINIT', 'Auto Initialize Mission', '1', 'required|boolean', 1, 1],
            ['PARAM_FILEPATCHING', '[Advanced] Enable File Patching', '0', 'required|boolean', 1, 1],
            ['PARAM_LIMITFPS', '[Advanced] Server FPS', '50', 'required|numeric', 1, 1],
            ['BASIC_CFG_URL', '[System] basic.cfg URL', 'https://raw.githubusercontent.com/pelican-eggs/games-steamcmd/main/arma/arma3/basic.cfg', 'required|string', 0, 0],
        ];

        foreach ($variables as [$env, $name, $default, $rules, $viewable, $editable]) {
            DB::table('pterodactyl_egg_variables')->updateOrInsert(
                [
                    'pterodactyl_egg_id' => 9,
                    'env_variable' => $env,
                ],
                [
                    'pterodactyl_variable_id' => null,
                    'name' => $name,
                    'default_value' => $default,
                    'rules' => $rules,
                    'required' => 1,
                    'user_viewable' => $viewable,
                    'user_editable' => $editable,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('pterodactyl_egg_variables')
            ->where('pterodactyl_egg_id', 9)
            ->whereIn('env_variable', [
                'RCON_PORT',
                'STEAMCMD_BETAID',
                'PARAM_NOLOGS',
                'PARAM_LOADMISSIONTOMEMORY',
                'PARAM_AUTOINIT',
                'PARAM_FILEPATCHING',
                'PARAM_LIMITFPS',
                'BASIC_CFG_URL',
            ])
            ->delete();

        DB::table('pterodactyl_eggs')
            ->where('id', 9)
            ->update([
                'docker_image' => 'ghcr.io/parkervcp/games:arma3',
                'docker_images' => json_encode([
                    'ghcr.io/parkervcp/games:arma3' => 'ghcr.io/parkervcp/games:arma3',
                ]),
                'updated_at' => now(),
            ]);
    }
};