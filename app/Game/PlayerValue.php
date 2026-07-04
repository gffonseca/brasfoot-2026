<?php

namespace App\Game;

/**
 * Fórmula única de valor de mercado e salário (em centavos).
 * Usada pelo seeder, pelo mercado e pela evolução de jogadores — fonte única da verdade.
 */
class PlayerValue
{
    /** Valor de mercado em centavos. */
    public static function valor(int $over, int $idade): int
    {
        $fatorIdade = $idade > 30 ? 0.7 : ($idade < 23 ? 1.15 : 1.0);
        $valorReais = round(pow($over / 50, 4) * 400000 * $fatorIdade);
        return (int) $valorReais * 100;
    }

    /** Salário semanal em centavos (piso R$ 1.500). */
    public static function salario(int $over, int $idade): int
    {
        $valorReais = self::valor($over, $idade) / 100;
        $salarioReais = max(1500, (int) round($valorReais * 0.012 / 30) * 30);
        return (int) $salarioReais * 100;
    }
}
