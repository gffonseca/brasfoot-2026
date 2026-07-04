<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lineups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('season_id')->constrained()->cascadeOnDelete();
            $t->foreignId('club_id')->constrained()->cascadeOnDelete();
            $t->string('formacao', 5)->default('4-4-2');
            $t->tinyInteger('tatica')->default(0);      // -1,0,1
            $t->unsignedSmallInteger('ingresso')->default(40);
            $t->json('starters');                        // array de player_id (11)
            $t->timestamps();
            $t->unique(['season_id', 'club_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('lineups'); }
};
