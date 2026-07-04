<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = ['season_id','player_id','from_club_id','to_club_id','valor','tipo'];
}
