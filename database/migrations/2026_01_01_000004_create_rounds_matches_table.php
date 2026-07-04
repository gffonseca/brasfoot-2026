<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('season_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('numero');
            $t->boolean('jogada')->default(false);
            $t->unique(['season_id', 'numero']);
        });

        Schema::create('matches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('round_id')->constrained()->cascadeOnDelete();
            $t->foreignId('home_club_id')->constrained('clubs');
            $t->foreignId('away_club_id')->constrained('clubs');
            $t->unsignedTinyInteger('gols_home')->nullable();
            $t->unsignedTinyInteger('gols_away')->nullable();
            $t->unsignedInteger('publico')->nullable();
            $t->bigInteger('renda')->nullable();
            $t->json('eventos')->nullable();
            $t->timestamp('jogada_em')->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('matches');
        Schema::dropIfExists('rounds');
    }
};
