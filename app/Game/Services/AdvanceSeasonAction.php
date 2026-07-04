<?php

namespace App\Game\Services;

use App\Game\LeagueScheduler;
use App\Game\LeagueTable;
use App\Game\Rng;
use App\Game\SimulationEngine;
use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

/**
 * Encerra uma temporada nacional aplicando acesso/rebaixamento (2 sobem / 2 descem)
 * e cria a próxima temporada (ano+1) para a divisão atual do clube do usuário.
 * Para temporada estadual, apenas cria a próxima temporada nacional.
 */
class AdvanceSeasonAction
{
    public function execute(Season $season): Season
    {
        $ano = $season->ano + 1;
        $userClubId = $season->club_do_usuario_id;

        if ($season->tipo === 'nacional') {
            $this->aplicarAcessoRebaixamento($season);
        }

        // evolução dos jogadores entre temporadas (idade, over, valor, base)
        app(EvolvePlayersAction::class)->execute($season->id + 4242);

        return app(StartSeasonAction::class)->execute($userClubId, $season->user_id, $ano);
    }

    private function aplicarAcessoRebaixamento(Season $season): void
    {
        // tabela REAL da divisão jogada pelo usuário
        $ordemUsuario = $this->tabelaReal($season);              // ids ordenados (1º..8º)
        $divUsuario = $season->divisao;

        // simula a OUTRA divisão (headless) p/ obter a ordem dela
        $outraDiv = $divUsuario === 'A' ? 'B' : 'A';
        $idsOutra = Club::where('divisao', $outraDiv)->pluck('id')->all();
        $ordemOutra = $this->simularTabela($idsOutra);

        // define quem é A e quem é B
        [$ordemA, $ordemB] = $divUsuario === 'A'
            ? [$ordemUsuario, $ordemOutra]
            : [$ordemOutra, $ordemUsuario];

        // 2 últimos da A descem; 2 primeiros da B sobem
        $rebaixados = array_slice($ordemA, -2);
        $promovidos = array_slice($ordemB, 0, 2);

        DB::transaction(function () use ($rebaixados, $promovidos) {
            Club::whereIn('id', $rebaixados)->update(['divisao' => 'B']);
            Club::whereIn('id', $promovidos)->update(['divisao' => 'A']);
        });
    }

    /** Ordem final (ids) a partir das partidas já jogadas da temporada. */
    private function tabelaReal(Season $season): array
    {
        $ids = $season->clubs->pluck('id')->all();
        $tab = LeagueTable::vazia($ids);
        GameMatch::whereHas('round', fn($q) => $q->where('season_id', $season->id))
            ->whereNotNull('gols_home')->get()
            ->each(fn($m) => LeagueTable::aplicarResultado($tab, $m->home_club_id, $m->away_club_id, $m->gols_home, $m->gols_away));
        return array_column(LeagueTable::classificar($tab), 'id');
    }

    /** Simula uma liga inteira (returno duplo) só para ranquear — sem persistir. */
    private function simularTabela(array $ids): array
    {
        $clubs = Club::whereIn('id', $ids)->with('players')->get()->keyBy('id');
        $xi = fn(int $id) => $clubs[$id]->bestXI();
        $tab = LeagueTable::vazia($ids);
        $rng = new Rng(($ids[0] ?? 1) * 2654435761 + (int) (microtime(true)));

        foreach (LeagueScheduler::gerar($ids) as $jogos) {
            foreach ($jogos as $j) {
                $r = SimulationEngine::simularPartida($xi($j['home']), $xi($j['away']), [], $rng);
                LeagueTable::aplicarResultado($tab, $j['home'], $j['away'], $r['golsH'], $r['golsA']);
            }
        }
        return array_column(LeagueTable::classificar($tab), 'id');
    }
}
