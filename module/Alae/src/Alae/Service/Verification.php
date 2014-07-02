<?php
/**
 * Proceso de verificación de lotes
 *
 * @author Maria Quiroz
 * Fecha de creación: 19/05/2014
 */
namespace Alae\Service;

class Verification
{
    /*
     * Actualiza el lote
     */
    public static function updateBatch($where, $fkParameter)
    {
        //ACTUALIZA EL LOTE
        return "
            UPDATE Alae\Entity\Batch b
            SET b.fkParameter = " . self::getPkParameter($fkParameter) . "
            WHERE $where";
    }

    /*
     * obtiene el parametro
     */
    public static function getPkParameter($fkParameter)
    {
        //OBTIENE EL PARAMETRO
        return "(
            SELECT p.pkParameter
            FROM Alae\Entity\Parameter p
            WHERE p.rule = '" . $fkParameter . "'
        )";
    }

    /*
     * actualiza el sampleBatch
     */
    public static function update($where, $fkParameter, $set = array())
    {
        //ACTUALIZA EL SAMPLE BATCH
        $query = "
            UPDATE Alae\Entity\SampleBatch s
            SET s.parameters = CONCAT_WS(',',s.parameters, " . self::getPkParameter($fkParameter) . ") " . ((count($set) > 0) ? ',' . implode(',', $set) : '') . "
            WHERE $where";

        return $query;
    }
}
