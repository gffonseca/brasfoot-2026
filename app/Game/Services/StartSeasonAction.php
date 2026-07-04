<?php

namespace App\Game\Services;

use App\Game\LeagueScheduler;
use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Round;
use App\Models\Season;
use Illuminate\Support\Collection;

/**
 * Cria uma temporada nacional para a divisão do clube do usuário
 * (ou uma divisão explícita), gera o calendário e persiste rodadas + partidas.
 */
class StartSeasonAction
{
    public function execute(int $clubDoUsuarioId, ?int $userId = null, ?int $ano = null): Season
    {
        $ano ??= (int) date('Y');
        $userClub = Club::findOrFail($clubDoUsuarioId);
        $divisao = $userClub->divisao;
        $clubs = Club::where('divisao', $divisao)->get();

        // Novo jogo = estado limpo: remove temporadas anteriores (a cascata apaga
        // rodadas, partidas, escalações e pivôs). Evita temporadas duplicadas e a
        // seleção não-determinística que fazia a classificação aparecer zerada.
        Season::query()->delete();

        return $this->criarLiga(
            clubs: $clubs,
            tipo: 'nacional',
            nome: "Série {$divisao} {$ano}",
            ano: $ano,
            userClubId: $clubDoUsuarioId,
            userId: $userId,
            divisao: $divisao,
        );
    }

    public function criarLiga(Collection $clubs, string $tipo, string $nome, int $ano, int $userClubId, ?int $userId = null, ?string $divisao = null, ?string $uf = null): Season
    {
        $ids = $clubs->pluck('id')->all();

        $season = Season::create([
            'user_id' => $userId, 'nome' => $nome, 'ano' => $ano, 'tipo' => $tipo,
            'divisao' => $divisao, 'uf' => $uf, 'club_do_usuario_id' => $userClubId,
            'rodada_atual' => 0, 'status' => 'em_andamento',
        ]);
        $season->clubs()->attach($ids, ['forma' => 50]);

        foreach (LeagueScheduler::gerar($ids) as $num => $jogos) {
            $round = Round::create(['season_id' => $season->id, 'numero' => $num + 1]);
            foreach ($jogos as $j) {
                GameMatch::create([
                    'round_id' => $round->id,
                    'home_club_id' => $j['home'],
                    'away_club_id' => $j['away'],
                ]);
            }
        }
        return $season;
    }
}
