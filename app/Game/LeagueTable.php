<?php

namespace App\Game;

/**
 * Tabela de classificação — porte de tabelaVazia/aplicarResultado/classificar do engine.js.
 * Trabalha com array associativo por id de clube.
 */
class LeagueTable
{
    /** @param array<int> $ids */
    public static function vazia(array $ids): array
    {
        $t = [];
        foreach ($ids as $id) {
            $t[$id] = ['id' => $id, 'pts' => 0, 'j' => 0, 'v' => 0, 'e' => 0, 'd' => 0, 'gp' => 0, 'gc' => 0];
        }
        return $t;
    }

    public static function aplicarResultado(array &$tab, int $home, int $away, int $gh, int $ga): void
    {
        $tab[$home]['j']++; $tab[$away]['j']++;
        $tab[$home]['gp'] += $gh; $tab[$home]['gc'] += $ga;
        $tab[$away]['gp'] += $ga; $tab[$away]['gc'] += $gh;

        if ($gh > $ga) {
            $tab[$home]['v']++; $tab[$home]['pts'] += 3; $tab[$away]['d']++;
        } elseif ($gh < $ga) {
            $tab[$away]['v']++; $tab[$away]['pts'] += 3; $tab[$home]['d']++;
        } else {
            $tab[$home]['e']++; $tab[$away]['e']++;
            $tab[$home]['pts']++; $tab[$away]['pts']++;
        }
    }

    /** Ordena por pts, SG, GP, V. Retorna lista reindexada. */
    public static function classificar(array $tab): array
    {
        $arr = array_values($tab);
        usort($arr, function ($a, $b) {
            return $b['pts'] <=> $a['pts']
                ?: (($b['gp'] - $b['gc']) <=> ($a['gp'] - $a['gc']))
                ?: ($b['gp'] <=> $a['gp'])
                ?: ($b['v'] <=> $a['v']);
        });
        return $arr;
    }
}
