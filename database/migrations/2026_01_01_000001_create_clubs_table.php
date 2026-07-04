<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $t) {
            $t->id();
            $t->string('nome');
            $t->string('abbr', 4);
            $t->string('cor', 7)->default('#0a3a8c');
            $t->string('cidade')->nullable();
            $t->string('uf', 2)->nullable();
            $t->enum('divisao', ['A', 'B'])->default('A');   // Série A ou B
            $t->unsignedInteger('capacidade_estadio')->default(20000);
            $t->bigInteger('caixa')->default(0);             // centavos
            $t->unsignedTinyInteger('base_forca')->default(65);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('clubs'); }
};
