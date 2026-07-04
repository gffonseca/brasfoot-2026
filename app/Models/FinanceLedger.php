<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceLedger extends Model
{
    protected $table = 'finance_ledger';
    protected $fillable = ['season_id','club_id','rodada','tipo','descricao','valor'];
}
