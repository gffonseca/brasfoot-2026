<?php

namespace App\Game;

/**
 * RNG com seed (mulberry32) — mesma sequência do protótipo JS (engine.js).
 * imul() replica Math.imul (multiplicação 32-bit) via halves de 16 bits,
 * garantindo paridade de sequência entre PHP e JavaScript para saves reprodutíveis.
 */
class Rng
{
    private int $a;

    public function __construct(int $seed)
    {
        $this->a = $seed & 0xFFFFFFFF;
    }

    private static function imul(int $a, int $b): int
    {
        $a &= 0xFFFFFFFF; $b &= 0xFFFFFFFF;
        $ah = ($a >> 16) & 0xFFFF; $al = $a & 0xFFFF;
        $bh = ($b >> 16) & 0xFFFF; $bl = $b & 0xFFFF;
        return ($al * $bl + ((($ah * $bl + $al * $bh) & 0xFFFF) << 16)) & 0xFFFFFFFF;
    }

    /** Retorna float em [0,1). Espelha mulberry32 do engine.js. */
    public function next(): float
    {
        $this->a = ($this->a + 0x6D2B79F5) & 0xFFFFFFFF;
        $t = self::imul($this->a ^ ($this->a >> 15), (1 | $this->a) & 0xFFFFFFFF);
        $t = (($t + self::imul($t ^ ($t >> 7), (61 | $t) & 0xFFFFFFFF)) & 0xFFFFFFFF) ^ $t;
        return (($t ^ ($t >> 14)) & 0xFFFFFFFF) / 4294967296;
    }
}
