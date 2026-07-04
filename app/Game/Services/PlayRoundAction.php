<?php

namespace App\Game\Services;

use App\Game\Rng;
use App\Game\SimulationEngine;
use App\Models\Club;
use App\Models\FinanceLedger;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\Round;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

/**
 * Joga a próxima rodada: simula todas as partidas, atualiza finanças do clube
 * do usuário e a forma dos clubes. Usa a escalação salva (Lineup) do usuário se existir;
 * senão, cai no melhor XI automático. Tudo em transação.
 */
class PlayRoundAction
{
    public function execute(Season $season, ?int $taticaOverride = null, ?int $ingressoOverride = null): ?Round
    {
        $round = $season->rounds()->where('jogada', false)->orderBy('numero')->first();
        if (!$round) return null;

        $clubs = Club::whereIn('id', $season->clubs->pluck('id'))
            ->with('players')->get()->keyBy('id');

        $userClubId = $season->club_do_usuario_id;
        $lineup = Lineup::where('season_id', $season->id)->where('club_id', $userClubId)->first();
        $tatica = $taticaOverride ?? ($lineup->tatica ?? 0);
        $ingresso = $ingressoOverride ?? ($lineup->ingresso ?? 40);

        // XI por clube: usuário usa Lineup salvo; CPU usa melhor XI
        $xiCache = [];
        $xi = function (int $id) use ($clubs, $userClubId, $lineup, &$xiCache) {
            if (isset($xiCache[$id])) return $xiCache[$id];
            $players = $clubs[$id]->players;
            if ($id === $userClubId && $lineup && count($lineup->starters) === 11) {
                $byId = $players->keyBy('id');
                $xi = [];
                foreach ($lineup->starters as $pid) {
                    if ($p = $byId->get($pid)) $xi[] = ['nome' => $p->nome, 'pos' => $p->posicao, 'over' => $p->over];
                }
                if (count($xi) === 11) return $xiCache[$id] = $xi;
            }
            return $xiCache[$id] = $clubs[$id]->bestXI();
        };

        $forma = $season->clubs->pluck('pivot.forma', 'id')->toArray();
        $rng = new Rng((int) (microtime(true) * 1000) ^ ($round->numero * 2654435761));

        DB::transaction(function () use ($round, $season, $clubs, $xi, &$forma, $userClubId, $tatica, $ingresso, $rng) {
            foreach ($round->matches as $m) {
                $ehUser = $userClubId === $m->home_club_id || $userClubId === $m->away_club_id;
                $souHome = $userClubId === $m->home_club_id;
                $opts = $ehUser
                    ? ['taticaHome' => $souHome ? $tatica : 0, 'taticaAway' => $souHome ? 0 : $tatica]
                    : [];
                $r = SimulationEngine::simularPartida($xi($m->home_club_id), $xi($m->away_club_id), $opts, $rng);

                $publico = null; $renda = null;
                if ($souHome) {
                    $home = $clubs[$m->home_club_id];
                    $folha = (int) $home->players->sum('salario');
                    $publico = SimulationEngine::publico($ingresso, $home->capacidade_estadio, $home->base_forca, $forma[$home->id]);
                    $renda = $ingresso * 100 * $publico; // centavos
                    $home->increment('caixa', $renda - $folha);
                    FinanceLedger::create(['season_id'=>$season->id,'club_id'=>$home->id,'rodada'=>$season->rodada_atual + 1,'tipo'=>'bilheteria','descricao'=>"Bilheteria vs {$clubs[$m->away_club_id]->abbr} ({$publico} torcedores)",'valor'=>$renda]);
                    FinanceLedger::create(['season_id'=>$season->id,'club_id'=>$home->id,'rodada'=>$season->rodada_atual + 1,'tipo'=>'folha','descricao'=>'Folha salarial','valor'=>-$folha]);
                } elseif ($ehUser) {
                    $away = $clubs[$m->away_club_id];
                    $folhaAway = (int) $away->players->sum('salario');
                    $away->decrement('caixa', $folhaAway);
                    FinanceLedger::create(['season_id'=>$season->id,'club_id'=>$away->id,'rodada'=>$season->rodada_atual + 1,'tipo'=>'folha','descricao'=>'Folha salarial (jogo fora)','valor'=>-$folhaAway]);
                }

                $m->update([
                    'gols_home' => $r['golsH'], 'gols_away' => $r['golsA'],
                    'publico' => $publico, 'renda' => $renda,
                    'eventos' => $r['eventos'], 'jogada_em' => now(),
                ]);

                $gh = $r['golsH']; $ga = $r['golsA'];
                $forma[$m->home_club_id] = max(0, min(100, $forma[$m->home_club_id] + ($gh>$ga?12:($gh<$ga?-10:0))));
                $forma[$m->away_club_id] = max(0, min(100, $forma[$m->away_club_id] + ($ga>$gh?12:($ga<$gh?-10:0))));
            }

            foreach ($forma as $cid => $f) {
                $season->clubs()->updateExistingPivot($cid, ['forma' => $f]);
            }
            $round->update(['jogada' => true]);
            $season->increment('rodada_atual');
            if ($season->rounds()->where('jogada', false)->count() === 0) {
                $season->update(['status' => 'encerrada']);
            }
        });

        return $round->fresh('matches');
    }
}
