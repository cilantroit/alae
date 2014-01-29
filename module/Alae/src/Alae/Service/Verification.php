<?php

namespace Alae\Service;

class Verification
{

    public static function updateBatch($where, $fkParameter)
    {
        return "
            UPDATE Alae\Entity\Batch b
            SET b.fkParameter = " . self::getPkParameter($fkParameter) . "
            WHERE $where";
    }

    public static function getPkParameter($fkParameter)
    {
        return "(
            SELECT p.pkParameter
            FROM Alae\Entity\Parameter p
            WHERE p.rule = '" . $fkParameter . "'
        )";
    }
    /*
     * V4, V5, V6, V10
     */
    public static function update($where, $fkParameter, $set = array())
    {
        $query = "
            UPDATE Alae\Entity\SampleBatch s
            SET s.parameters = " . self::getPkParameter($fkParameter) . ((count($set) > 0) ? ',' . implode(',', $set) : '') . "
            WHERE $where";

        //echo "<br>" . $query . "<br>";

        return $query;
    }

    /*
     * V7, V8, V9
     */
    public static function updateInner($table, $join, $fkParameter)
    {
        return "
            UPDATE Alae\Entity\SampleBatch s
            INNER JOIN $table t ON $join
            SET s.parameters = CONCAT_WS(';', s.parameters, " . self::getPkParameter($fkParameter) . ")";
    }


}


