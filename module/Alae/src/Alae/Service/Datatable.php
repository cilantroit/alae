<?php

/**
 * Description of Datatable
 *
 * @author Maria Quiroz
 */

namespace Alae\Service;

class Datatable
{
    protected function getAnalyteColumns($data)
    {
        $filters = self::getFilters($data, array("id", "name", "shortname"));

        return array(
            "data"     => (!empty($data)) ? json_encode($data) : 0,
            "columns"  => json_encode(array(
                array("key" => "id", "label" => "Id", "sortable" => true),
                array("key" => "name", "label" => "Nombre Analito", "sortable" => true, "allowHTML" => true),
                array("key" => "shortname", "label" => "Abreviatura", "sortable" => true, "allowHTML" => true),
                array("key" => "edit", "allowHTML" => true, "formatter" => '<span onclick="changeElement(this, {value});">edit</span><span onclick="removeElement(this, {value});">delete</span>')
            )),
            "editable" => json_encode(array("name", "shortname")),
            "filters"  => (!empty($filters)) ? json_encode($filters) : 0
        );
    }

    protected function getFilters($data, $filters)
    {
        $options = array();

        foreach ($filters as $filter)
        {
            foreach ($data as $row)
            {
                $options[$filter][] = $row[$filter];
            }
        }
        return $options;
    }

    public static function getDatatable($data, $datatable)
    {
        $response = array();

        switch ($datatable)
        {
            case 'analyte':
                $response = self::getAnalyteColumns($data);
                break;
        }

        return $response;
    }
}