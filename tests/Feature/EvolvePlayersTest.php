<?php

namespace Tests\Feature;

use App\Game\Services\EvolvePlayersAction;
use App\Models\Club;
use App\Models\Player;
use Database\Seeders\ClubSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvolvePlayersTest extends TestCase
{
    use RefreshDatabase;

    public function test_jovem_cresce_veterano_declina_e_base_recompleta(): void
    {
        $this->seed(ClubSeeder::class);
        $remo = Club::where('abbr', 'REM')->firstOrFail();

        // prepara um jovem e um veterano controlados
        $jovem = Player::where('club_id', $remo->id)->first();
        $jovem->update(['idade' => 18, 'over' => 60]);
        $veterano = Player::where('club_id', $remo->id)->where('id', '!=', $jovem->id)->first();
        $veterano->update(['idade' => 31, 'over' => 70]);

        $res = app(EvolvePlayersAction::class)->execute(12345);

        $j = $jovem->fresh(); $v = $veterano->fresh();
        $this->assertSame(19, $j->idade, 'idade +1');
        $this->assertGreaterThan(60, $j->over, 'jovem deve crescer');
        $this->assertSame(32, $v->idade);
        $this->assertLessThan(70, $v->over, 'veterano deve declinar');

        // aposentadoria
        $velho = Player::where('club_id', $remo->id)->where('id', '!=', $jovem->id)->where('id', '!=', $veterano->id)->first();
        $velhoId = $velho->id;
        $velho->update(['idade' => 38, 'over' => 60]);
        app(EvolvePlayersAction::class)->execute(999);
        $this->assertNull(Player::find($velhoId), 'jogador 38+ deve se aposentar');

        // todo clube mantém pelo menos 18 jogadores (base recompleta)
        foreach (Club::all() as $c) {
            $this->assertGreaterThanOrEqual(18, Player::where('club_id', $c->id)->count(), "clube {$c->abbr}");
        }

        $this->assertGreaterThan(0, $res['revelados'] + $res['envelhecidos']);
    }
}
