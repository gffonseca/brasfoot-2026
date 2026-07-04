<?php

namespace App\Livewire;

use App\Models\Club;
use App\Models\FinanceLedger;
use App\Models\Player;
use App\Models\Season;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Financas extends Component
{
    public Season $season;

    public function mount(): void
    {
        $this->season = Season::latest()->firstOrFail();
    }

    private function clubId(): int { return $this->season->club_do_usuario_id; }

    #[Computed]
    public function club(): Club { return Club::findOrFail($this->clubId()); }

    #[Computed]
    public function folhaSemanal(): int
    {
        return (int) Player::where('club_id', $this->clubId())->sum('salario');
    }

    #[Computed]
    public function valorElenco(): int
    {
        return (int) Player::where('club_id', $this->clubId())->sum('valor');
    }

    /** Evolução acumulada do caixa a partir do razão (para o gráfico). */
    #[Computed]
    public function serie(): array
    {
        $lanc = FinanceLedger::where('season_id', $this->season->id)
            ->where('club_id', $this->clubId())
            ->orderBy('id')->get(['rodada', 'valor']);

        // saldo inicial = caixa atual - soma de todos os lançamentos
        $totalLanc = (int) $lanc->sum('valor');
        $saldoInicial = $this->club->caixa - $totalLanc;

        $labels = ['Início'];
        $acc = $saldoInicial;
        $valores = [round($acc / 100, 2)];
        foreach ($lanc as $l) {
            $acc += $l->valor;
            $labels[] = 'R' . ($l->rodada ?? '');
            $valores[] = round($acc / 100, 2);
        }
        return ['labels' => $labels, 'valores' => $valores];
    }

    #[Computed]
    public function historico()
    {
        return FinanceLedger::where('season_id', $this->season->id)
            ->where('club_id', $this->clubId())
            ->orderByDesc('id')->limit(60)->get();
    }

    public function render()
    {
        return view('livewire.financas');
    }
}
