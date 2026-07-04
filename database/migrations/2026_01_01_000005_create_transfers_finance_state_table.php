<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('season_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('player_id')->constrained('players');
            $t->foreignId('from_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $t->foreignId('to_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $t->bigInteger('valor');
            $t->enum('tipo', ['compra', 'venda']);
            $t->timestamps();
        });

        Schema::create('finance_ledger', function (Blueprint $t) {
            $t->id();
            $t->foreignId('season_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $t->unsignedSmallInteger('rodada')->nullable();
            $t->string('tipo');          // bilheteria|folha|compra|venda|estadio
            $t->string('descricao');
            $t->bigInteger('valor');     // + entrada, - saída (centavos)
            $t->timestamps();
        });

        // snapshot opcional p/ save/load rápido (mirror do estado G do protótipo)
        Schema::create('game_states', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('season_id')->constrained()->cascadeOnDelete();
            $t->json('snapshot');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('game_states');
        Schema::dropIfExists('finance_ledger');
        Schema::dropIfExists('transfers');
    }
};
