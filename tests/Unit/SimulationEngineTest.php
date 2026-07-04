<?php

namespace Tests\Unit;

use App\Game\LeagueScheduler;
use App\Game\LeagueTable;
use App\Game\Rng;
use App\Game\SimulationEngine;
use PHPUnit\Framework\TestCase;

/**
 * Replica em PHP os mesmos invariantes validados no protótipo JS (28.000 jogos).
 * Não depende do banco — testa apenas a engine pura.
 */
class SimulationEngineTest extends TestCase
{
    private function timeXI(int $base, Rng $rng): array
    {
        $layout = ['GOL','ZAG','ZAG','LAT','LAT','VOL','VOL','MEI','MEI','ATA','ATA'];
        return array_map(fn($pos) => [
            'nome' => $pos,
            'pos' => $pos,
            'over' => max(30, min(95, (int) round($base + ($rng->next()*16 - 8)))),
        ], $layout);
    }

    public function test_calendario_returno_duplo_tem_56_jogos_e_14_por_time(): void
    {
        $ids = range(0, 7);
        $cal = LeagueScheduler::gerar($ids);
        $total = array_sum(array_map('count', $cal));
        $this->assertSame(56, $total, 'returno duplo de 8 clubes = 56 jogos');

        $jogos = array_fill_keys($ids, 0);
        foreach ($cal as $rod) foreach ($rod as $j) { $jogos[$j['home']]++; $jogos[$j['away']]++; }
        foreach ($ids as $i) $this->assertSame(14, $jogos[$i], "time $i deve jogar 14 vezes");
    }

    public function test_invariantes_de_tabela_em_muitas_temporadas(): void
    {
        $ids = range(0, 7);
        $NSEASONS = 200;
        $totGols = 0; $nJogos = 0; $vitCasa = 0; $vitFora = 0; $empates = 0;

        for ($s = 0; $s < $NSEASONS; $s++) {
            $rng = new Rng(1000 + $s);
            $XIs = [];
            foreach ($ids as $id) $XIs[$id] = $this->timeXI(55 + $id*4, $rng);
            $cal = LeagueScheduler::gerar($ids);
            $tab = LeagueTable::vazia($ids);

            foreach ($cal as $rod) foreach ($rod as $j) {
                $r = SimulationEngine::simularPartida($XIs[$j['home']], $XIs[$j['away']], [], $rng);
                LeagueTable::aplicarResultado($tab, $j['home'], $j['away'], $r['golsH'], $r['golsA']);
                $totGols += $r['golsH'] + $r['golsA']; $nJogos++;
                if ($r['golsH'] > $r['golsA']) $vitCasa++;
                elseif ($r['golsH'] < $r['golsA']) $vitFora++;
                else $empates++;
            }

            foreach ($ids as $i) {
                $t = $tab[$i];
                $this->assertSame($t['j'], $t['v'] + $t['e'] + $t['d'], "J = V+E+D (time $i)");
                $this->assertSame(14, $t['j']);
                $this->assertSame($t['pts'], $t['v']*3 + $t['e'], "pontos coerentes (time $i)");
            }
            $sgp = array_sum(array_column($tab, 'gp'));
            $sgc = array_sum(array_column($tab, 'gc'));
            $this->assertSame($sgp, $sgc, 'gols pró total = gols contra total');
        }

        $media = $totGols / $nJogos;
        $this->assertGreaterThan(2.0, $media, 'média de gols plausível');
        $this->assertLessThan(4.0, $media);
        $this->assertGreaterThan($vitFora, $vitCasa, 'vantagem de mandante deve aparecer no agregado');
    }

    public function test_demanda_de_publico_cai_com_o_preco(): void
    {
        $p10 = SimulationEngine::publico(10, 40000, 75, 60);
        $p60 = SimulationEngine::publico(60, 40000, 75, 60);
        $p120 = SimulationEngine::publico(120, 40000, 75, 60);
        $this->assertGreaterThan($p60, $p10);
        $this->assertGreaterThan($p120, $p60);
    }
}
