<?php

namespace App\Game;

/**
 * Motor de simulação — porte fiel de engine.js (validado em 28.000 jogos).
 * Sem dependências de framework: 100% testável em PHPUnit.
 *
 * Jogador esperado como array: ['nome'=>string,'pos'=>string,'over'=>int]
 * Posições: GOL, ZAG, LAT, VOL, MEI, ATA
 */
class SimulationEngine
{
    private const SETOR = [
        'GOL' => 'def', 'ZAG' => 'def', 'LAT' => 'def',
        'VOL' => 'mei', 'MEI' => 'mei', 'ATA' => 'atq',
    ];

    /** Amostragem Poisson (Knuth). */
    public static function poisson(float $lambda, Rng $rng): int
    {
        $L = exp(-$lambda);
        $k = 0; $p = 1.0;
        do { $k++; $p *= $rng->next(); } while ($p > $L);
        return $k - 1;
    }

    /** Força ofensiva/defensiva do XI + tática (-1 def, 0 eq, 1 ofensiva). */
    public static function forcaTime(array $xi, int $tatica = 0): array
    {
        $def = []; $mei = []; $atq = []; $gk = null;
        foreach ($xi as $p) {
            $s = self::SETOR[$p['pos']] ?? 'mei';
            if ($s === 'def') $def[] = $p['over'];
            elseif ($s === 'mei') $mei[] = $p['over'];
            else $atq[] = $p['over'];
            if ($p['pos'] === 'GOL') $gk = $p['over'];
        }
        $avg = fn(array $a) => count($a) ? array_sum($a) / count($a) : 60;
        $fDef = 0.55 * $avg($def) + 0.30 * $avg($mei) + 0.15 * ($gk ?? 60);
        $fAtq = 0.55 * $avg($atq) + 0.35 * $avg($mei) + 0.10 * $avg($def);
        $t = $tatica * 0.06;
        return ['atq' => $fAtq * (1 + $t), 'def' => $fDef * (1 - $t)];
    }

    /**
     * Simula uma partida. Retorna
     * ['golsH'=>int,'golsA'=>int,'eventos'=>array,'lambdaH'=>float,'lambdaA'=>float].
     */
    public static function simularPartida(array $homeXI, array $awayXI, array $opts, Rng $rng): array
    {
        $tH = $opts['taticaHome'] ?? 0;
        $tA = $opts['taticaAway'] ?? 0;
        $fH = self::forcaTime($homeXI, $tH);
        $fA = self::forcaTime($awayXI, $tA);
        $HA = 1.12; $base = 1.35;

        $lH = $base * pow($fH['atq'] / $fA['def'], 1.6) * $HA;
        $lA = $base * pow($fA['atq'] / $fH['def'], 1.6);
        $lH = max(0.15, min(5.5, $lH));
        $lA = max(0.15, min(5.5, $lA));

        $gH = self::poisson($lH, $rng);
        $gA = self::poisson($lA, $rng);

        $eventos = [];
        $marcar = function (string $time, array $xi, int $n) use (&$eventos, $rng) {
            $at = array_values(array_filter($xi, fn($p) => in_array($p['pos'], ['ATA', 'MEI'])));
            $pool = count($at) ? $at : $xi;
            for ($i = 0; $i < $n; $i++) {
                $au = $pool[(int) floor($rng->next() * count($pool))] ?? null;
                $eventos[] = [
                    'min' => 1 + (int) floor($rng->next() * 90),
                    'time' => $time,
                    'autor' => $au['nome'] ?? '—',
                ];
            }
        };
        $marcar('home', $homeXI, $gH);
        $marcar('away', $awayXI, $gA);
        usort($eventos, fn($x, $y) => $x['min'] <=> $y['min']);

        return ['golsH' => $gH, 'golsA' => $gA, 'eventos' => $eventos, 'lambdaH' => $lH, 'lambdaA' => $lA];
    }

    /** Público de um jogo em casa (curva de demanda por preço/força/forma). */
    public static function publico(float $preco, int $cap, float $forca, float $forma): int
    {
        $ref = 20 + $forca * 0.9;
        $el = max(0.15, 1.25 - $preco / $ref * 0.9);
        $af = 0.8 + ($forma / 100) * 0.4;
        $tx = min(1, max(0.05, $el * $af));
        return (int) round($cap * $tx);
    }
}
