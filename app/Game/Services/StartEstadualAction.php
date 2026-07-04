<?php

namespace App\Game\Services;

use App\Models\Club;
use App\Models\Season;

/**
 * Inicia um campeonato estadual (returno duplo) com os clubes de uma UF.
 * Diferencial histórico do JogaFoot. Requer >= 4 clubes na UF.
 */
class StartEstadualAction
{
    private const NOMES_UF = [
        'PA' => 'Paraense', 'SP' => 'Paulista', 'RJ' => 'Carioca', 'MG' => 'Mineiro',
        'RS' => 'Gaúcho', 'BA' => 'Baiano', 'PR' => 'Paranaense', 'PE' => 'Pernambucano',
        'CE' => 'Cearense', 'SC' => 'Catarinense',
    ];

    public function execute(int $clubDoUsuarioId, ?int $userId = null, ?int $ano = null): ?Season
    {
        $ano ??= (int) date('Y');
        $userClub = Club::findOrFail($clubDoUsuarioId);
        $uf = $userClub->uf;
        $clubs = Club::where('uf', $uf)->get();
        if ($clubs->count() < 4) return null;

        $nomeUf = self::NOMES_UF[$uf] ?? $uf;
        return app(StartSeasonAction::class)->criarLiga(
            clubs: $clubs,
            tipo: 'estadual',
            nome: "Campeonato {$nomeUf} {$ano}",
            ano: $ano,
            userClubId: $clubDoUsuarioId,
            userId: $userId,
            uf: $uf,
        );
    }
}
