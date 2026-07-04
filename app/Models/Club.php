<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    protected $fillable = ['nome', 'abbr', 'cor', 'cidade', 'uf', 'capacidade_estadio', 'caixa', 'base_forca'];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /** Melhor XI 4-4-2 automático (usado pela CPU). */
    public function bestXI(string $formacao = '4-4-2'): array
    {
        $forms = ['4-4-2' => ['gol'=>1,'def'=>4,'mid'=>4,'atk'=>2],
                  '4-3-3' => ['gol'=>1,'def'=>4,'mid'=>3,'atk'=>3],
                  '3-5-2' => ['gol'=>1,'def'=>3,'mid'=>5,'atk'=>2]];
        $f = $forms[$formacao] ?? $forms['4-4-2'];
        $setor = fn($p) => $p->posicao === 'GOL' ? 'gol'
            : (in_array($p->posicao, ['ZAG','LAT']) ? 'def'
            : (in_array($p->posicao, ['VOL','MEI']) ? 'mid' : 'atk'));
        $pick = function ($s, $n) use ($setor) {
            return $this->players
                ->filter(fn($p) => $setor($p) === $s)
                ->sortByDesc('over')->take($n)
                ->map(fn($p) => ['nome'=>$p->nome,'pos'=>$p->posicao,'over'=>$p->over])
                ->values()->all();
        };
        return array_merge($pick('gol',$f['gol']), $pick('def',$f['def']), $pick('mid',$f['mid']), $pick('atk',$f['atk']));
    }
}
