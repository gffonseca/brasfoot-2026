<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    protected $fillable = ['club_id','nome','posicao','over','idade','valor','salario','traco1','traco2'];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
