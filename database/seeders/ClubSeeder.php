<?php

namespace Database\Seeders;

use App\Game\PlayerValue;
use App\Game\Rng;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Database\Seeder;

/**
 * 16 clubes fictícios em 2 divisões (Série A e B). 4 clubes do Pará (divisão B)
 * habilitam o Campeonato Paraense. Valores monetários em centavos.
 */
class ClubSeeder extends Seeder
{
    private const CLUBS = [
        // Série A
        ['nome'=>'Rubro Atlético','abbr'=>'RUB','cor'=>'#b3202b','uf'=>'RJ','div'=>'A','base'=>84,'cap'=>70000,'caixa'=>38000000],
        ['nome'=>'Verde Palmeira','abbr'=>'VER','cor'=>'#0e6b3a','uf'=>'SP','div'=>'A','base'=>83,'cap'=>42000,'caixa'=>36000000],
        ['nome'=>'Cruzeiro do Sul','abbr'=>'CRU','cor'=>'#1e5fbf','uf'=>'MG','div'=>'A','base'=>80,'cap'=>60000,'caixa'=>24000000],
        ['nome'=>'Colorado Gaúcho','abbr'=>'COL','cor'=>'#c0392b','uf'=>'RS','div'=>'A','base'=>78,'cap'=>50000,'caixa'=>20000000],
        ['nome'=>'Alvinegro Praiano','abbr'=>'ALV','cor'=>'#111827','uf'=>'SP','div'=>'A','base'=>77,'cap'=>16000,'caixa'=>16000000],
        ['nome'=>'Tricolor Baiano','abbr'=>'BAH','cor'=>'#1666b0','uf'=>'BA','div'=>'A','base'=>76,'cap'=>48000,'caixa'=>15000000],
        ['nome'=>'Furacão do Sul','abbr'=>'FUR','cor'=>'#c0392b','uf'=>'PR','div'=>'A','base'=>75,'cap'=>42000,'caixa'=>14000000],
        ['nome'=>'Leão do Nordeste','abbr'=>'LEN','cor'=>'#b71c1c','uf'=>'PE','div'=>'A','base'=>74,'cap'=>26000,'caixa'=>11000000],
        // Série B
        ['nome'=>'Clube do Remo','abbr'=>'REM','cor'=>'#0a3a8c','uf'=>'PA','div'=>'B','base'=>73,'cap'=>38000,'caixa'=>9000000],
        ['nome'=>'Galo Mineiro','abbr'=>'GAL','cor'=>'#111827','uf'=>'MG','div'=>'B','base'=>72,'cap'=>23000,'caixa'=>8500000],
        ['nome'=>'Paysandu','abbr'=>'PAY','cor'=>'#0f7a35','uf'=>'PA','div'=>'B','base'=>71,'cap'=>30000,'caixa'=>8000000],
        ['nome'=>'Vozão Cearense','abbr'=>'VOZ','cor'=>'#111827','uf'=>'CE','div'=>'B','base'=>70,'cap'=>45000,'caixa'=>7500000],
        ['nome'=>'Leão da Ilha','abbr'=>'ILH','cor'=>'#c0392b','uf'=>'SC','div'=>'B','base'=>68,'cap'=>18000,'caixa'=>6000000],
        ['nome'=>'Bugre Campineiro','abbr'=>'BUG','cor'=>'#0e6b3a','uf'=>'SP','div'=>'B','base'=>66,'cap'=>19000,'caixa'=>5000000],
        ['nome'=>'Águia de Marabá','abbr'=>'AGU','cor'=>'#c0392b','uf'=>'PA','div'=>'B','base'=>63,'cap'=>12000,'caixa'=>3000000],
        ['nome'=>'Tuna Luso','abbr'=>'TUN','cor'=>'#7a1020','uf'=>'PA','div'=>'B','base'=>60,'cap'=>9000,'caixa'=>2200000],
    ];
    private const NOMES1 = ['Léo','Gabriel','Rafael','Matheus','Bruno','Diego','Wesley','Paulo','João','Vitor','Éverton','Igor','Rodrigo','Kaio','Nathan','Fábio','Marlon','Jean','Renan','Danilo','Yuri','Alan','Caio','Thiago'];
    private const NOMES2 = ['Silva','Souza','Ferreira','Oliveira','Rocha','Lima','Pereira','Alves','Barbosa','Nunes','Ramos','Cardoso','Moraes','Teixeira','Correia','Pinto','Araújo','Gomes','Mendes','Freitas'];
    private const LAYOUT = ['GOL','GOL','GOL','ZAG','ZAG','ZAG','ZAG','LAT','LAT','LAT','LAT','VOL','VOL','VOL','MEI','MEI','MEI','MEI','ATA','ATA','ATA','ATA'];
    private const TRACOS = ['Finalização','Velocidade','Drible','Passe','Marcação','Cabeceio','Liderança','Reflexos','Cruzamento','Força'];

    public function run(): void
    {
        if (\App\Models\Club::query()->exists()) {
            return; // ja semeado: preserva o estado do jogo entre deploys/restarts
        }
        foreach (self::CLUBS as $i => $c) {
            $club = Club::create([
                'nome'=>$c['nome'],'abbr'=>$c['abbr'],'cor'=>$c['cor'],'uf'=>$c['uf'],'divisao'=>$c['div'],
                'capacidade_estadio'=>$c['cap'],'caixa'=>$c['caixa'] * 100,'base_forca'=>$c['base'],
            ]);
            $rng = new Rng($i * 7919 + 13);
            foreach (self::LAYOUT as $pos) {
                $over = max(35, min(93, (int) round($c['base'] + ($rng->next()*20 - 10))));
                $idade = 17 + (int) floor($rng->next()*20);
                $nome = self::NOMES1[(int) floor($rng->next()*count(self::NOMES1))]
                      .' '.self::NOMES2[(int) floor($rng->next()*count(self::NOMES2))];
                $nt = 1 + (int) floor($rng->next()*2);
                $tr = [];
                while (count($tr) < $nt) {
                    $cand = self::TRACOS[(int) floor($rng->next()*count(self::TRACOS))];
                    if (!in_array($cand, $tr)) $tr[] = $cand;
                }
                Player::create([
                    'club_id'=>$club->id,'nome'=>$nome,'posicao'=>$pos,'over'=>$over,'idade'=>$idade,
                    'valor'=>PlayerValue::valor($over, $idade),'salario'=>PlayerValue::salario($over, $idade),
                    'traco1'=>$tr[0] ?? null,'traco2'=>$tr[1] ?? null,
                ]);
            }
        }
    }
}
