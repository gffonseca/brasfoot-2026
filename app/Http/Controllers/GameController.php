<?php

namespace App\Http\Controllers;

use App\Game\LeagueTable;
use App\Game\Services\AdvanceSeasonAction;
use App\Game\Services\StartEstadualAction;
use App\Game\Services\StartSeasonAction;
use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Season;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function home()
    {
        $season = Season::has('clubs')->orderByDesc('id')->first();
        $clubs = Club::orderBy('divisao')->orderByDesc('base_forca')->get();
        $tabela = $season ? $this->montarTabela($season) : [];
        // pode iniciar estadual? (clube do usuário tem >=4 na UF)
        $podeEstadual = false; $ufNome = null;
        if ($season) {
            $userClub = Club::find($season->club_do_usuario_id);
            if ($userClub) {
                $podeEstadual = Club::where('uf', $userClub->uf)->count() >= 4;
                $ufNome = $userClub->uf;
            }
        }
        return view('game.dashboard', compact('season', 'clubs', 'tabela', 'podeEstadual', 'ufNome'));
    }

    public function start(Request $r, StartSeasonAction $action)
    {
        $action->execute((int) $r->input('club_id'));
        return redirect()->route('game.home');
    }

    public function startEstadual(Request $r, StartEstadualAction $action)
    {
        $season = Season::has('clubs')->orderByDesc('id')->firstOrFail();
        $action->execute($season->club_do_usuario_id);
        return redirect()->route('game.home');
    }

    public function advance(Request $r, AdvanceSeasonAction $action)
    {
        $season = Season::has('clubs')->orderByDesc('id')->firstOrFail();
        $action->execute($season);
        return redirect()->route('game.home');
    }

    private function montarTabela(Season $season): array
    {
        $ids = $season->clubs->pluck('id')->all();
        $tab = LeagueTable::vazia($ids);
        GameMatch::whereHas('round', fn($q) => $q->where('season_id', $season->id))
            ->whereNotNull('gols_home')->get()
            ->each(fn($m) => LeagueTable::aplicarResultado($tab, $m->home_club_id, $m->away_club_id, $m->gols_home, $m->gols_away));
        $cls = LeagueTable::classificar($tab);
        $nomes = Club::pluck('nome', 'id');
        return array_map(fn($t) => $t + ['nome' => $nomes[$t['id']] ?? '?'], $cls);
    }
}
