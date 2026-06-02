<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jogos_prod', function (Blueprint $table) {
            $table->string('font_family')->nullable()->after('icon');
        });

        DB::table('jogos_prod')->whereIn('slug', ['minecraft-java', 'minecraft-bedrock'])->update(['font_family' => 'minecraft']);
        DB::table('jogos_prod')->where('slug', 'terraria')->update(['font_family' => 'terraria']);
        DB::table('jogos_prod')->whereIn('slug', ['gta-v', 'gta-sa'])->update(['font_family' => 'gta']);
    }

    public function down(): void
    {
        Schema::table('jogos_prod', function (Blueprint $table) {
            $table->dropColumn('font_family');
        });
    }
};
