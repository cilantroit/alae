<?php

/**
 * Servicio encargado de hacer las conversiones necesarias en la evaluación de lotes analíticos
 * @author Maria Quiroz
 * Fecha de creación: 16/05/2014
 */

namespace Alae\Service;

class Conversion
{
    /*
     * Función para convertir unidades
     */
    public static function conversion($unit1, $unit2, $value)
    {
        $matrix = array("mg/mL", "µg/mL", "ng/mL", "pg/mL");
        $conversion = $value;

        if ($unit1 != $unit2)
        {
            $min = array_search($unit1, $matrix);
            $max = array_search($unit2, $matrix);

            if($min < $max)
            {
                $multiplier = 1;
                for($i = $min; $i < $max; $i++)
                {
                    $multiplier = $multiplier * 1000;
                }

                $conversion = $conversion * $multiplier;
            }
            else
            {
                $multiplier = 1;
                for($i = $min; $i < $max; $i++)
                {
                    $multiplier = $multiplier * 1000;
                }

                $conversion = $conversion / $multiplier;
            }
        }

        return $conversion;
    }
}
