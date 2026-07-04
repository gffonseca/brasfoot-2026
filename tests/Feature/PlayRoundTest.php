<?php

namespace Tests\Feature;

use App\Game\Services\PlayRoundAction;
use App\Game\Services\StartSeasonAction;
use App\Models\Club;
use App\Models\FinanceLedger;
use App\Models\GameMatch;
use Database\Seeders\ClubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayRoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_jogar_rodada_preenche_placares_e_lanca_financas(): void
    {
        $this->seed(ClubSeeder::class);
        $remo = Club::where('abbr', 'REM')->firstOrFail();
        $season = app(StartSeasonAction::class)->execute($remo->id);

        app(PlayRoundAction::class)->execute($season->fresh('clubs'));
        $season->refresh();

        $this->assertSame(1, $season->rodada_atual);
        // toda partida da 1ª rodada tem placar
        $r1 = $season->rounds()->where('numero', 1)->first();
        $this->assertGreaterThan(0, $r1->matches()->whereNotNull('gols_home')->count());
        $this->assertSame(0, $r1->matches()->whereNull('gols_home')->count());
        // razão do usuário recebeu lançamento de folha
        $this->assertDatabaseHas('finance_ledger', ['club_id' => $remo->id, 'tipo' => 'folha']);
    }

    public function test_temporada_completa_encerra(): void
    {
        $this->seed(ClubSeeder::class);
        $remo = Club::where('abbr', 'REM')->firstOrFail();
        $season = app(StartSeasonAction::class)->execute($remo->id);
        $action = app(PlayRoundAction::class);

        $guard = 0;
        while ($season->fresh()->status !== 'encerrada' && $guard++ < 40) {
            $action->execute($season->fresh('clubs'));
        }
        $season->refresh();
        $this->assertSame('encerrada', $season->status);
        // 8 clubes, returno duplo = 14 rodadas
        $this->assertSame(14, $season->rodada_atual);
    }
}
