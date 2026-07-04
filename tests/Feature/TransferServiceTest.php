<?php

namespace Tests\Feature;

use App\Game\Services\StartSeasonAction;
use App\Game\Services\TransferService;
use App\Models\Club;
use App\Models\FinanceLedger;
use App\Models\Player;
use App\Models\Transfer;
use Database\Seeders\ClubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private function novaTemporada(): array
    {
        $this->seed(ClubSeeder::class);
        $remo = Club::where('abbr', 'REM')->firstOrFail();
        $season = app(StartSeasonAction::class)->execute($remo->id);
        return [$season, $remo];
    }

    public function test_compra_move_jogador_e_debita_caixa(): void
    {
        [$season, $remo] = $this->novaTemporada();
        $svc = app(TransferService::class);

        $alvo = Player::where('club_id', '!=', $remo->id)->whereNotNull('club_id')->orderByDesc('over')->first();
        $vendedorId = $alvo->club_id;
        $caixaCompradorAntes = $remo->fresh()->caixa;
        $caixaVendedorAntes = Club::find($vendedorId)->caixa;

        // garante caixa suficiente
        $remo->update(['caixa' => $alvo->valor + 1_000_000]);
        $r = $svc->comprar($season, $alvo->id);

        $this->assertTrue($r['ok'], $r['msg']);
        $this->assertSame($remo->id, $alvo->fresh()->club_id);
        $this->assertSame($remo->fresh()->caixa, $alvo->valor + 1_000_000 - $alvo->valor);
        $this->assertSame($caixaVendedorAntes + $alvo->valor, Club::find($vendedorId)->caixa);
        $this->assertDatabaseHas('transfers', ['player_id' => $alvo->id, 'tipo' => 'compra']);
        $this->assertDatabaseHas('finance_ledger', ['club_id' => $remo->id, 'tipo' => 'compra']);
    }

    public function test_venda_credita_85_porcento_e_respeita_minimo(): void
    {
        [$season, $remo] = $this->novaTemporada();
        $svc = app(TransferService::class);

        $p = Player::where('club_id', $remo->id)->orderByDesc('valor')->first();
        $caixaAntes = $remo->fresh()->caixa;
        $r = $svc->vender($season, $p->id);

        $this->assertTrue($r['ok'], $r['msg']);
        $this->assertNull($p->fresh()->club_id);
        $this->assertSame($caixaAntes + (int) round($p->valor * 0.85), $remo->fresh()->caixa);

        // reduz o elenco a 14 e tenta vender de novo -> bloqueia
        $ids = Player::where('club_id', $remo->id)->orderBy('id')->pluck('id');
        Player::whereIn('id', $ids->slice(14))->update(['club_id' => null]);
        $sobra = Player::where('club_id', $remo->id)->first();
        $r2 = $svc->vender($season, $sobra->id);
        $this->assertFalse($r2['ok']);
    }
}
