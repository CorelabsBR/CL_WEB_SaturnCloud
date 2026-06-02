<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RootUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ROOT_USER_EMAIL', 'root@vortex.local');

        $existingRoot = DB::table('user')
            ->where('email', $email)
            ->orWhere('tipo_usuario', 'root')
            ->first();

        if ($existingRoot) {
            $this->command?->info("Conta root ja existe: {$existingRoot->email}");

            return;
        }

        $password = env('ROOT_USER_PASSWORD') ?: $this->generatePassword();

        DB::table('user')->insert([
            'nome' => 'Root',
            'email' => $email,
            'senha' => Hash::make($password),
            'cpf_cnpj' => 'ROOTACCOUNT0001',
            'celular' => '00000000000',
            'cep' => '00000000',
            'rua' => 'Sistema',
            'numero' => '0',
            'complemento' => null,
            'bairro' => 'Sistema',
            'cidade' => 'Sistema',
            'estado' => 'Sistema',
            'pais' => 'Brasil',
            'data_nasc' => '2000-01-01',
            'tipo_usuario' => 'root',
            'email_verified_at' => now(),
            'profile_completed_at' => now(),
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        if (! env('ROOT_USER_PASSWORD')) {
            $path = 'root-admin-credentials.txt';
            $disk = Storage::disk('local');
            $disk->put($path, "email={$email}\nsenha={$password}\n");

            $fullPath = $disk->path($path);
            @chmod(dirname($fullPath), 0700);
            @chmod($fullPath, 0600);

            $this->command?->warn("Conta root criada. Senha gerada em: {$fullPath}");
        } else {
            $this->command?->info('Conta root criada usando ROOT_USER_PASSWORD do .env.');
        }
    }

    private function generatePassword(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(36)), '+/', '-_'), '=');
    }
}
