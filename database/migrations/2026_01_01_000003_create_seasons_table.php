<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('nome')->nullable();                  // "Série A 2026", "Paraense 2026"
            $t->unsignedSmallInteger('ano');
            $t->enum('tipo', ['nacional', 'estadual', 'internacional'])->default('nacional');
            $t->enum('divisao', ['A', 'B'])->nullable();     // só p/ nacional
            $t->string('uf', 2)->nullable();                 // só p/ estadual
            $t->foreignId('club_do_usuario_id')->nullable()->constrained('clubs')->nullOnDelete();
            $t->unsignedSmallInteger('rodada_atual')->default(0);
            $t->enum('status', ['em_andamento', 'encerrada'])->default('em_andamento');
            $t->timestamps();
        });

        Schema::create('season_club', function (Blueprint $t) {
            $t->id();
            $t->foreignId('season_id')->constrained()->cascadeOnDelete();
            $t->foreignId('club_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('forma')->default(50);
            $t->unique(['season_id', 'club_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('season_club');
        Schema::dropIfExists('seasons');
    }
};
