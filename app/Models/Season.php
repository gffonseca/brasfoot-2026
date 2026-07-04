<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = ['user_id','ano','tipo','club_do_usuario_id','rodada_atual','status'];

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class)->withPivot('forma');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }
}
