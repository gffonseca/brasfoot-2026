<?php

namespace App\Game\Services;

use App\Game\PlayerValue;
use App\Game\Rng;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

/**
 * Passagem de temporada para os jogadores:
 *  - idade +1;
 *  - "over" cresce (jovens) ou declina (veteranos) conforme a faixa etária;
 *  - valor/salário recalculados;
 *  - aposentadoria aos 38+ (ou 36+ com over baixo);
 *  - base: cada clube é recompletado até 20 jogadores com jovens.
 * Determinístico via Rng(seed) para testes reprodutíveis.
 */
class EvolvePlayersAction
{
    private const NOMES1 = ['Léo','Gabriel','Rafael','Matheus','Bruno','Diego','Wesley','Paulo','João','Vitor','Igor','Kaio','Nathan','Yuri','Alan','Caio'];
    private const NOMES2 = ['Silva','Souza','Ferreira','Oliveira','Rocha','Lima','Pereira','Alves','Ramos','Cardoso','Moraes','Araújo','Gomes','Mendes'];
    private const TRACOS = ['Finalização','Velocidade','Drible','Passe','Marcação','Cabeceio','Liderança','Reflexos','Cruzamento','Força'];
    private const POR_CLUBE_MIN = 18;
    private const POR_CLUBE_ALVO = 20;

    /** @return array{envelhecidos:int,aposentados:int,revelados:int} */
    public function execute(int $seed = 20260703): array
    {
        $rng = new Rng($seed);
        $envelhecidos = 0; $aposentados = 0; $revelados = 0;

        DB::transaction(function () use ($rng, &$envelhecidos, &$aposentados, &$revelados) {
            foreach (Player::orderBy('id')->get() as $p) {
                $idade = $p->idade + 1;
                $delta = self::deltaOver($idade, $rng);
                $over = max(30, min(93, $p->over + $delta));

                $aposenta = $idade >= 38 || ($idade >= 36 && $over < 55);
                if ($aposenta) { $p->delete(); $aposentados++; continue; }

                $p->update([
                    'idade' => $idade,
                    'over' => $over,
                    'valor' => PlayerValue::valor($over, $idade),
                    'salario' => PlayerValue::salario($over, $idade),
                ]);
                $envelhecidos++;
            }

            // base: recompleta cada clube
            foreach (Club::all() as $club) {
                $qtd = Player::where('club_id', $club->id)->count();
                while ($qtd < self::POR_CLUBE_ALVO) {
                    $this->revelarJovem($club, $rng);
                    $qtd++; $revelados++;
                }
                unset($club);
            }
        });

        return compact('envelhecidos', 'aposentados', 'revelados');
    }

    private static function deltaOver(int $idade, Rng $rng): int
    {
        $r = fn(int $lo, int $hi) => $lo + (int) floor($rng->next() * ($hi - $lo + 1));
        return match (true) {
            $idade <= 21 => $r(1, 3),    // cresce rápido
            $idade <= 25 => $r(0, 2),    // ainda sobe
            $idade <= 29 => $r(-1, 1),   // auge/estável
            $idade <= 32 => -$r(1, 2),   // começa a cair
            default      => -$r(2, 4),   // declínio acentuado
        };
    }

    private function revelarJovem(Club $club, Rng $rng): void
    {
        $poslist = ['GOL','ZAG','LAT','VOL','MEI','ATA'];
        $pos = $poslist[(int) floor($rng->next() * count($poslist))];
        $over = max(40, min(72, (int) round($club->base_forca - 8 + ($rng->next() * 10 - 5))));
        $idade = 16 + (int) floor($rng->next() * 3);
        $nome = self::NOMES1[(int) floor($rng->next() * count(self::NOMES1))]
              . ' ' . self::NOMES2[(int) floor($rng->next() * count(self::NOMES2))];
        Player::create([
            'club_id' => $club->id, 'nome' => $nome, 'posicao' => $pos, 'over' => $over, 'idade' => $idade,
            'valor' => PlayerValue::valor($over, $idade), 'salario' => PlayerValue::salario($over, $idade),
            'traco1' => self::TRACOS[(int) floor($rng->next() * count(self::TRACOS))], 'traco2' => null,
        ]);
    }
}
