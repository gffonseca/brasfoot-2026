<?php

namespace App\Livewire;

use App\Game\SimulationEngine;
use App\Models\Lineup;
use App\Models\Season;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Escalacao extends Component
{
    public Season $season;
    public string $formacao = '4-4-2';
    public array $starters = [];   // 11 player_id

    public function mount(): void
    {
        $this->season = Season::has('clubs')->orderByDesc('id')->firstOrFail();
        $lineup = $this->lineup();
        $this->formacao = $lineup->formacao;
        $this->starters = $lineup->starters ?? [];
        if (count($this->starters) !== 11) $this->melhorXI();
    }

    private function clubId(): int { return $this->season->club_do_usuario_id; }

    private function lineup(): Lineup
    {
        return Lineup::firstOrCreate(
            ['season_id' => $this->season->id, 'club_id' => $this->clubId()],
            ['formacao' => '4-4-2', 'starters' => []]
        );
    }

    #[Computed]
    public function players()
    {
        return \App\Models\Player::where('club_id', $this->clubId())
            ->orderByRaw("FIELD(posicao,'GOL','ZAG','LAT','VOL','MEI','ATA')")
            ->orderByDesc('over')->get();
    }

    public function melhorXI(): void
    {
        $this->starters = Lineup::melhorXI($this->players, $this->formacao);
        $this->salvar();
    }

    public function trocarFormacao(string $f): void
    {
        $this->formacao = $f;
        $this->melhorXI();
    }

    public function escalar(int $playerId): void
    {
        if (in_array($playerId, $this->starters)) return;
        $p = $this->players->firstWhere('id', $playerId);
        if (!$p) return;
        $setor = Lineup::setor($p->posicao);
        $titulares = $this->players->whereIn('id', $this->starters)
            ->filter(fn($x) => Lineup::setor($x->posicao) === $setor);
        if ($titulares->isEmpty()) {
            $this->dispatch('flash', msg: "Sem vaga para {$p->posicao} nesta formação.");
            return;
        }
        $pior = $titulares->sortBy('over')->first();
        $this->starters = array_map(fn($id) => $id === $pior->id ? $playerId : $id, $this->starters);
        $this->salvar();
    }

    private function salvar(): void
    {
        Lineup::updateOrCreate(
            ['season_id' => $this->season->id, 'club_id' => $this->clubId()],
            ['formacao' => $this->formacao, 'starters' => array_values($this->starters)]
        );
    }

    #[Computed]
    public function xiObjs(): array
    {
        $byId = $this->players->keyBy('id');
        return collect($this->starters)->map(fn($id) => $byId->get($id))->filter()
            ->map(fn($p) => ['nome' => $p->nome, 'pos' => $p->posicao, 'over' => $p->over])->values()->all();
    }

    #[Computed]
    public function forca(): array
    {
        return SimulationEngine::forcaTime($this->xiObjs, 0);
    }

    public function render()
    {
        return view('livewire.escalacao');
    }
}
