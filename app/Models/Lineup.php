<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lineup extends Model
{
    protected $fillable = ['season_id', 'club_id', 'formacao', 'tatica', 'ingresso', 'starters'];
    protected $casts = ['starters' => 'array', 'tatica' => 'integer', 'ingresso' => 'integer'];

    public const FORMS = [
        '4-4-2' => ['gol' => 1, 'def' => 4, 'mid' => 4, 'atk' => 2],
        '4-3-3' => ['gol' => 1, 'def' => 4, 'mid' => 3, 'atk' => 3],
        '3-5-2' => ['gol' => 1, 'def' => 3, 'mid' => 5, 'atk' => 2],
    ];

    public static function setor(string $pos): string
    {
        return $pos === 'GOL' ? 'gol'
            : (in_array($pos, ['ZAG', 'LAT']) ? 'def'
            : (in_array($pos, ['VOL', 'MEI']) ? 'mid' : 'atk'));
    }

    /** Monta o melhor XI a partir de uma coleção de Player e uma formação. */
    public static function melhorXI($players, string $formacao): array
    {
        $f = self::FORMS[$formacao] ?? self::FORMS['4-4-2'];
        $pick = fn($s, $n) => $players
            ->filter(fn($p) => self::setor($p->posicao) === $s)
            ->sortByDesc('over')->take($n)->pluck('id')->all();
        return array_merge($pick('gol', $f['gol']), $pick('def', $f['def']), $pick('mid', $f['mid']), $pick('atk', $f['atk']));
    }
}
