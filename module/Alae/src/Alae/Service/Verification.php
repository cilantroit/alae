<?php

namespace Alae\Service;

class Verification
{

    /*
     * V4, V6, V10
     */

    public static function update($where, $fkParameter)
    {
        return "
            UPDATE Alae\Entity\SampleBatch s 
            SET s.parameters = (SELECT CONCAT(p.pkParameter, ',') FROM Alae\Entity\Parameter p WHERE p.rule = '" . $fkParameter . "')
            WHERE $where";
    }

    /*
     * V5, V7, V8, V9
     */
    public static function updateInner($table, $join, $fkParameter)
    {
        return "
            UPDATE Alae\Entity\SampleBatch s
            INNER JOIN $table t ON $join
            SET s.parameters = (SELECT CONCAT(p.pkParameter, ',') FROM Alae\Entity\Parameter p WHERE p.rule = '" . $fkParameter . "')";
    }


}


