<?php

namespace App\Game\Services;

use App\Models\Club;
use App\Models\FinanceLedger;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\Season;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

/**
 * Compra e venda de jogadores. Valores em centavos.
 * Regras: venda recebe 85% do valor; manter no mínimo 14 jogadores ao vender.
 */
class TransferService
{
    public const MIN_ELENCO = 14;
    public const FATOR_VENDA = 0.85;

    /** @return array{ok:bool,msg:string} */
    public function comprar(Season $season, int $playerId): array
    {
        $player = Player::find($playerId);
        $comprador = Club::find($season->club_do_usuario_id);
        if (!$player || !$comprador) return ['ok' => false, 'msg' => 'Jogador ou clube inválido.'];
        if ($player->club_id === $comprador->id) return ['ok' => false, 'msg' => 'Jogador já é seu.'];
        if ($comprador->caixa < $player->valor) return ['ok' => false, 'msg' => 'Caixa insuficiente.'];

        $vendedorId = $player->club_id;

        DB::transaction(function () use ($season, $player, $comprador, $vendedorId) {
            $comprador->decrement('caixa', $player->valor);
            if ($vendedorId) Club::whereKey($vendedorId)->increment('caixa', $player->valor);

            $player->update(['club_id' => $comprador->id]);

            Transfer::create([
                'season_id' => $season->id, 'player_id' => $player->id,
                'from_club_id' => $vendedorId, 'to_club_id' => $comprador->id,
                'valor' => $player->valor, 'tipo' => 'compra',
            ]);
            FinanceLedger::create([
                'season_id' => $season->id, 'club_id' => $comprador->id, 'rodada' => $season->rodada_atual,
                'tipo' => 'compra', 'descricao' => "Compra: {$player->nome} ({$player->over})", 'valor' => -$player->valor,
            ]);
        });

        return ['ok' => true, 'msg' => "Contratado: {$player->nome}."];
    }

    /** @return array{ok:bool,msg:string} */
    public function vender(Season $season, int $playerId): array
    {
        $vendedor = Club::find($season->club_do_usuario_id);
        $player = Player::where('id', $playerId)->where('club_id', $vendedor->id)->first();
        if (!$player) return ['ok' => false, 'msg' => 'Jogador não pertence ao seu clube.'];
        if ($vendedor->players()->count() <= self::MIN_ELENCO) {
            return ['ok' => false, 'msg' => 'Mantenha ao menos '.self::MIN_ELENCO.' jogadores.'];
        }

        $valor = (int) round($player->valor * self::FATOR_VENDA);

        DB::transaction(function () use ($season, $player, $vendedor, $valor) {
            $vendedor->increment('caixa', $valor);
            // jogador vira "sem clube" (mercado livre) — some do elenco
            $player->update(['club_id' => null]);

            Transfer::create([
                'season_id' => $season->id, 'player_id' => $player->id,
                'from_club_id' => $vendedor->id, 'to_club_id' => null,
                'valor' => $valor, 'tipo' => 'venda',
            ]);
            FinanceLedger::create([
                'season_id' => $season->id, 'club_id' => $vendedor->id, 'rodada' => $season->rodada_atual,
                'tipo' => 'venda', 'descricao' => "Venda: {$player->nome} ({$player->over})", 'valor' => $valor,
            ]);
            // remove das escalações salvas
            $lineup = Lineup::where('season_id', $season->id)->where('club_id', $vendedor->id)->first();
            if ($lineup) {
                $lineup->update(['starters' => array_values(array_diff($lineup->starters ?? [], [$player->id]))]);
            }
        });

        return ['ok' => true, 'msg' => "Vendido: {$player->nome} por R$ ".number_format($valor/100, 2, ',', '.').'.'];
    }
}
