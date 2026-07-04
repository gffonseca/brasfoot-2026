<?php

namespace App\Game\Services;

use App\Game\SimulationEngine;

/**
 * Regras financeiras de uma rodada: bilheteria (mandante) e folha salarial.
 */
class FinanceService
{
    /**
     * @return array{publico:int,renda:int,folha:int,saldo:int}
     */
    public static function rodadaMandante(int $precoIngresso, int $capacidade, float $forca, float $forma, int $folhaSemanal): array
    {
        $publico = SimulationEngine::publico($precoIngresso, $capacidade, $forca, $forma);
        $renda = $precoIngresso * $publico;
        return [
            'publico' => $publico,
            'renda' => $renda,
            'folha' => $folhaSemanal,
            'saldo' => $renda - $folhaSemanal,
        ];
    }

    /** Venda recebe 85% do valor de mercado. */
    public static function valorVenda(int $valorMercado): int
    {
        return (int) round($valorMercado * 0.85);
    }
}
