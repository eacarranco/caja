<?php
class CalculadoraInteres {

    public static function simular($monto, $tasaAnual, $plazoMeses, $metodo) {
        $tasaMensual = ($tasaAnual / 100) / 12;
        return match ($metodo) {
            'simple' => self::simple($monto, $tasaMensual, $plazoMeses),
            'francés' => self::frances($monto, $tasaMensual, $plazoMeses),
            'alemán' => self::aleman($monto, $tasaMensual, $plazoMeses),
            default => throw new InvalidArgumentException('Método no soportado'),
        };
    }

    private static function simple($monto, $tasaMensual, $plazo) {
        $cuotas = [];
        $saldo = $monto;
        for ($i = 1; $i <= $plazo; $i++) {
            $interes = $saldo * $tasaMensual;
            $capital = $monto / $plazo;
            $total = $capital + $interes;
            $saldo -= $capital;
            if ($i === $plazo) $saldo = 0;
            $cuotas[] = [
                'número' => $i,
                'capital' => round($capital, 2),
                'interés' => round($interes, 2),
                'total' => round($total, 2),
                'saldo' => round(max($saldo, 0), 2),
            ];
        }
        return $cuotas;
    }

    private static function frances($monto, $tasaMensual, $plazo) {
        $cuotas = [];
        $factor = (1 + $tasaMensual) ** $plazo;
        $cuotaFija = $monto * $tasaMensual * $factor / ($factor - 1);
        $saldo = $monto;
        for ($i = 1; $i <= $plazo; $i++) {
            $interes = $saldo * $tasaMensual;
            $capital = $cuotaFija - $interes;
            $saldo -= $capital;
            if ($i === $plazo) {
                $capital += $saldo;
                $saldo = 0;
            }
            $cuotas[] = [
                'número' => $i,
                'capital' => round($capital, 2),
                'interés' => round($interes, 2),
                'total' => round($capital + $interes, 2),
                'saldo' => round(max($saldo, 0), 2),
            ];
        }
        return $cuotas;
    }

    private static function aleman($monto, $tasaMensual, $plazo) {
        $cuotas = [];
        $capitalFijo = $monto / $plazo;
        $saldo = $monto;
        for ($i = 1; $i <= $plazo; $i++) {
            $interes = $saldo * $tasaMensual;
            $total = $capitalFijo + $interes;
            $saldo -= $capitalFijo;
            if ($i === $plazo) $saldo = 0;
            $cuotas[] = [
                'número' => $i,
                'capital' => round($capitalFijo, 2),
                'interés' => round($interes, 2),
                'total' => round($total, 2),
                'saldo' => round(max($saldo, 0), 2),
            ];
        }
        return $cuotas;
    }
}
