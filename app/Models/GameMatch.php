<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMatch extends Model
{
    public $timestamps = false;
    protected $table = 'matches';
    protected $fillable = ['round_id','home_club_id','away_club_id','gols_home','gols_away','publico','renda','eventos','jogada_em'];
    protected $casts = ['eventos' => 'array', 'jogada_em' => 'datetime'];

    public function round(): BelongsTo { return $this->belongsTo(Round::class); }
    public function homeClub(): BelongsTo { return $this->belongsTo(Club::class, 'home_club_id'); }
    public function awayClub(): BelongsTo { return $this->belongsTo(Club::class, 'away_club_id'); }
}
