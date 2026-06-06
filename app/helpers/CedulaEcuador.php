<?php
class CedulaEcuador {
    public static function validar($cedula) {
        if (strlen($cedula) != 10) return false;
        if (!ctype_digit($cedula)) return false;
        $provincia = intval(substr($cedula, 0, 2));
        if ($provincia < 1 || $provincia > 24) return false;
        $digitoVerificador = intval(substr($cedula, 9, 1));
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $valor = intval($cedula[$i]) * $coeficientes[$i];
            $suma += ($valor >= 10) ? $valor - 9 : $valor;
        }
        $residuo = $suma % 10;
        $check = ($residuo == 0) ? 0 : 10 - $residuo;
        return $check == $digitoVerificador;
    }
}
