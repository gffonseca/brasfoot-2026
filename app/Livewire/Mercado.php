<?php

namespace App\Livewire;

use App\Game\Services\TransferService;
use App\Models\Club;
use App\Models\Player;
use App\Models\Season;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Mercado extends Component
{
    public Season $season;
    public string $flash = '';

    public function mount(): void
    {
        $this->season = Season::has('clubs')->orderByDesc('id')->firstOrFail();
    }

    #[Computed]
    public function caixa(): int
    {
        return (int) Club::whereKey($this->season->club_do_usuario_id)->value('caixa');
    }

    #[Computed]
    public function alvos()
    {
        return Player::whereNotNull('club_id')
            ->where('club_id', '!=', $this->season->club_do_usuario_id)
            ->with('club:id,abbr')
            ->orderByDesc('over')->limit(40)->get();
    }

    #[Computed]
    public function elenco()
    {
        return Player::where('club_id', $this->season->club_do_usuario_id)
            ->orderByDesc('valor')->get();
    }

    public function comprar(int $playerId, TransferService $svc): void
    {
        $r = $svc->comprar($this->season, $playerId);
        $this->flash = $r['msg'];
        unset($this->alvos, $this->elenco, $this->caixa);
    }

    public function vender(int $playerId, TransferService $svc): void
    {
        $r = $svc->vender($this->season, $playerId);
        $this->flash = $r['msg'];
        unset($this->alvos, $this->elenco, $this->caixa);
    }

    public function render()
    {
        return view('livewire.mercado');
    }
}
