<?php

namespace App\Livewire;

use App\Game\SimulationEngine;
use App\Game\Services\PlayRoundAction;
use App\Models\Club;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Season;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Partida extends Component
{
    public Season $season;
    public int $tatica = 0;
    public int $ingresso = 40;
    public ?int $ultimoMatchId = null;

    public function mount(): void
    {
        $this->season = Season::latest()->firstOrFail();
        $lineup = Lineup::where('season_id', $this->season->id)
            ->where('club_id', $this->season->club_do_usuario_id)->first();
        $this->tatica = $lineup->tatica ?? 0;
        $this->ingresso = $lineup->ingresso ?? 40;
    }

    #[Computed]
    public function proximoJogo(): ?GameMatch
    {
        $round = $this->season->rounds()->where('jogada', false)->orderBy('numero')->first();
        if (!$round) return null;
        return $round->matches()
            ->where(fn($q) => $q->where('home_club_id', $this->season->club_do_usuario_id)
                ->orWhere('away_club_id', $this->season->club_do_usuario_id))
            ->with(['homeClub', 'awayClub'])->first();
    }

    #[Computed]
    public function publicoEstimado(): int
    {
        $jogo = $this->proximoJogo;
        if (!$jogo || $jogo->home_club_id !== $this->season->club_do_usuario_id) return 0;
        $club = Club::find($this->season->club_do_usuario_id);
        $forma = $this->season->clubs->firstWhere('id', $club->id)->pivot->forma ?? 50;
        return SimulationEngine::publico($this->ingresso, $club->capacidade_estadio, $club->base_forca, $forma);
    }

    public function salvarPrefs(): void
    {
        Lineup::updateOrCreate(
            ['season_id' => $this->season->id, 'club_id' => $this->season->club_do_usuario_id],
            ['tatica' => $this->tatica, 'ingresso' => $this->ingresso]
        );
    }

    public function jogar(PlayRoundAction $action): void
    {
        $this->salvarPrefs();
        $round = $action->execute($this->season->fresh('clubs'), $this->tatica, $this->ingresso);
        $this->season->refresh();
        if ($round) {
            $meu = $round->matches->first(fn($m) =>
                $m->home_club_id === $this->season->club_do_usuario_id
                || $m->away_club_id === $this->season->club_do_usuario_id);
            $this->ultimoMatchId = $meu?->id;
        }
    }

    #[Computed]
    public function ultimoMatch(): ?GameMatch
    {
        return $this->ultimoMatchId
            ? GameMatch::with(['homeClub', 'awayClub'])->find($this->ultimoMatchId)
            : null;
    }

    public function render()
    {
        return view('livewire.partida');
    }
}
