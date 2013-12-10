<?php

/**
 * Description of Datatable
 *
 * @author Maria Quiroz
 */

namespace Alae\Service;

class Datatable
{

    protected function getAnalyteColumns()
    {
        return array(
            array("key" => "id", "label" => "Id", "sortable" => true),
            array("key" => "name", "label" => "Nombre Analito", "sortable" => true, "allowHTML" => true),
            array("key" => "shortname", "label" => "Abreviatura", "sortable" => true, "allowHTML" => true),
            array("key" => "edit", "allowHTML" => true, "formatter" => '<span onclick="changeElement(this, {value});">edit</span><span onclick="removeElement(this, {value});">delete</span>')
        );
    }

    protected function filters($datatable)
    {
        switch ($datatable)
        {
            case 'analyte':
                $filters = array("id", "name", "shortname");
                break;
        }

        return $filters;
    }

    public static function editable($datatable)
    {
        switch ($datatable)
        {
            case 'analyte':
                $editable = array("name", "shortname");
                break;
        }

        return $editable;
    }

    public static function getColumns($datatable)
    {
        switch ($datatable)
        {
            case 'analyte':
                $columns = self::getAnalyteColumns();
                break;
        }

        return $columns;
    }

    public static function getFilters($data, $datatable)
    {
        $options = array();
        $filters = self::filters($datatable);
        foreach ($filters as $filter)
        {
            foreach ($data as $row)
            {
                $options[$filter][] = $row[$filter];
            }
        }
        return $options;
    }
}