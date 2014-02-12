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

    public static function update($where, $fkParameter, $set = array())
    {
        $query = "
            UPDATE Alae\Entity\SampleBatch s
            SET s.parameters = CONCAT_WS(',',s.parameters, " . self::getPkParameter($fkParameter) . ") " . ((count($set) > 0) ? ',' . implode(',', $set) : '') . "
            WHERE $where";

        return $query;
    }
}
