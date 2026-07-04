<?php

namespace Tests\Feature;

use App\Game\Services\AdvanceSeasonAction;
use App\Game\Services\PlayRoundAction;
use App\Game\Services\StartSeasonAction;
use App\Models\Club;
use Database\Seeders\ClubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvanceSeasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_avanco_aplica_acesso_rebaixamento_e_cria_proxima(): void
    {
        $this->seed(ClubSeeder::class);
        $remo = Club::where('abbr', 'REM')->firstOrFail(); // Série B
        $season = app(StartSeasonAction::class)->execute($remo->id);

        // joga a temporada inteira
        $play = app(PlayRoundAction::class);
        $g = 0;
        while ($season->fresh()->status !== 'encerrada' && $g++ < 40) {
            $play->execute($season->fresh('clubs'));
        }

        $divAntes = Club::pluck('divisao', 'id');
        $nova = app(AdvanceSeasonAction::class)->execute($season->fresh());
        $divDepois = Club::pluck('divisao', 'id');

        // exatamente 2 subiram (B->A) e 2 caíram (A->B)
        $subiram = 0; $caíram = 0;
        foreach ($divAntes as $id => $d) {
            if ($d === 'B' && $divDepois[$id] === 'A') $subiram++;
            if ($d === 'A' && $divDepois[$id] === 'B') $caíram++;
        }
        $this->assertSame(2, $subiram, '2 clubes devem subir');
        $this->assertSame(2, $caíram, '2 clubes devem descer');

        // nova temporada criada para o ano seguinte
        $this->assertSame($season->ano + 1, $nova->ano);
        $this->assertSame('nacional', $nova->tipo);
        $this->assertSame('em_andamento', $nova->status);
    }
}
