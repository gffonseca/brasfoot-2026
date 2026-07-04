<?php

namespace App\Game;

/**
 * Gera calendário de returno duplo (todos contra todos, casa e fora)
 * pelo algoritmo do círculo (round-robin). Porte de gerarCalendario() do engine.js.
 */
class LeagueScheduler
{
    /**
     * @param  array<int>  $ids  ids dos clubes
     * @return array<int, array<int, array{home:int,away:int}>>  rodadas
     */
    public static function gerar(array $ids): array
    {
        $teams = array_values($ids);
        if (count($teams) % 2 !== 0) {
            $teams[] = null; // bye
        }
        $n = count($teams);
        $metade = intdiv($n, 2);
        $rodadas = [];
        $arr = $teams;

        for ($r = 0; $r < $n - 1; $r++) {
            $jogos = [];
            for ($i = 0; $i < $metade; $i++) {
                $a = $arr[$i];
                $b = $arr[$n - 1 - $i];
                if ($a !== null && $b !== null) {
                    $jogos[] = ($r % 2 === 0)
                        ? ['home' => $a, 'away' => $b]
                        : ['home' => $b, 'away' => $a];
                }
            }
            $rodadas[] = $jogos;
            // rotação mantendo arr[0] fixo
            $arr = array_merge([$arr[0]], [$arr[$n - 1]], array_slice($arr, 1, $n - 2));
        }

        // returno: espelha invertendo o mando
        $returno = array_map(
            fn($jogos) => array_map(fn($j) => ['home' => $j['away'], 'away' => $j['home']], $jogos),
            $rodadas
        );

        return array_merge($rodadas, $returno);
    }
}
