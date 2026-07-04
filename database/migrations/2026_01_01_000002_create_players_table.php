<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('players', function (Blueprint $t) {
            $t->id();
            $t->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $t->string('nome');
            $t->enum('posicao', ['GOL', 'ZAG', 'LAT', 'VOL', 'MEI', 'ATA']);
            $t->unsignedTinyInteger('over');
            $t->unsignedTinyInteger('idade');
            $t->bigInteger('valor')->default(0);     // centavos
            $t->bigInteger('salario')->default(0);   // centavos / semana
            $t->string('traco1')->nullable();
            $t->string('traco2')->nullable();
            $t->timestamps();
            $t->index(['club_id', 'posicao']);
        });
    }
    public function down(): void { Schema::dropIfExists('players'); }
};
