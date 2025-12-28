<?php
// static/modules/widgets/math_utils.php

/**
 * Calcula la media móvil exponencial (EMA) de un array de valores
 * @param array $values Valores a procesar
 * @param float $alpha Factor de suavizado (0 < alpha <= 1)
 * @return array EMA de los valores
 */
function ema(array $values, float $alpha): array {
    $ema = [];
    $prev = $values[0];
    foreach ($values as $v) {
        $prev = $alpha * $v + (1 - $alpha) * $prev;
        $ema[] = $prev;
    }
    return $ema;
}

/**
 * Calcula la pendiente de una serie temporal usando regresión lineal
 * @param array $times Array de timestamps (numéricos)
 * @param array $values Valores correspondientes
 * @return float Pendiente
 */
function linearTrend(array $times, array $values): float {
    $n = count($values);
    if ($n < 2) return 0;

    $sumX = $sumY = $sumXY = $sumXX = 0;
    for ($i = 0; $i < $n; $i++) {
        $x = $times[$i];
        $y = $values[$i];
        $sumX  += $x;
        $sumY  += $y;
        $sumXY += $x * $y;
        $sumXX += $x * $x;
    }

    $den = ($n * $sumXX - $sumX * $sumX);
    return $den ? ($n * $sumXY - $sumX * $sumY) / $den : 0;
}
