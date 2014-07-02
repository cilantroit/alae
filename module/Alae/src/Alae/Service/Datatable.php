<?php

/**
 * Servicio encargado de la configuración de tablas del sistema
 * y la configuración de estas tablas con los grid del sistema
 *
 *
 * @author Maria Quiroz
 * Fecha de creación: 13/05/2014
 */

namespace Alae\Service;

class Datatable
{

    const DATATABLE_ANALYTE      = 'analyte';
    const DATATABLE_STUDY        = 'study';
    const DATATABLE_PARAMETER    = 'parameter';
    const DATATABLE_REASON       = 'reason';
    const DATATABLE_UNFILLED     = 'unfilled';
    const DATATABLE_ADMIN        = 'admin';
    const DATATABLE_ANASTUDY     = 'analyte_study';
    const DATATABLE_BATCH        = 'batch';
    const DATATABLE_SAMPLE_BATCH = 'sample_batch';
    const DATATABLE_AUDIT_TRAIL  = 'audit';

    protected $_data;
    protected $_datatable;
    protected $_base_url;
    protected $_notFilterable;
    protected $_profile;

    public function __construct($data, $datatable, $profile)
    {
        $this->_profile       = $profile;
        $this->_data          = $data;
        $this->_datatable     = $datatable;
        $this->_base_url      = \Alae\Service\Helper::getVarsConfig("base_url");
        $this->_notFilterable = array("use", "valid_flag", "modify", "accepted_flag", "password", "profile", "audit_description", "created_at","reason");
    }

    protected function getData()
    {
	return $this->_data;
    }

    //CREA LAS COLUMNAS DEL GRID DE ANALITOS
    protected function getAnalyteColumns()
    {
	$header = array("id", "name", "shortname");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "id", "label" => "Id", "sortable" => true),
		array("key" => "name", "label" => "Nombre Analito", "sortable" => true, "allowHTML" => true),
		array("key" => "shortname", "label" => "Abreviatura", "sortable" => true, "allowHTML" => true),
		array("key" => "edit", "allowHTML" => true)
	    )),
	    "editable" => json_encode(array("name", "shortname")),
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

    //CREA LAS COLUMNAS DEL GRID DE ESTUDIOS
    protected function getStudyColumns()
    {
	$header = array("code", "description", "date", "analyte", "observation", "closed");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "code", "label" => "Código", "sortable" => true),
		array("key" => "description", "label" => "Descripción", "sortable" => true, "allowHTML" => true),
		array("key" => "date", "label" => "Fecha", "sortable" => true, "allowHTML" => true),
		array("key" => "analyte", "label" => "Nº Analitos", "sortable" => true, "allowHTML" => true),
		array("key" => "observation", "label" => "Observaciones", "sortable" => true, "allowHTML" => true),
		array("key" => "closed", "label" => "Cerrado (S/N)", "sortable" => true, "allowHTML" => true),
		array("key" => "edit", "allowHTML" => true)
	    )),
	    "editable" => 0,
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

    //CREA LAS COLUMNAS DE LOS PARAMETROS
    protected function getParameterColumns()
    {
	$header = array("rule", "verification", "min_value", "max_value", "code_error", "message_error");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "rule", "label" => "Regla", "sortable" => true),
		array("key" => "verification", "label" => "Descripción", "sortable" => true, "allowHTML" => true),
		array("key" => "min_value", "label" => "Mín", "sortable" => true, "allowHTML" => true),
		array("key" => "max_value", "label" => "Máx", "sortable" => true, "allowHTML" => true),
		array("key" => "code_error", "label" => "Motivo", "sortable" => true, "allowHTML" => true),
		array("key" => "message_error", "label" => "Mensaje de error", "sortable" => true, "allowHTML" => true),
		array("key" => "edit", "allowHTML" => true, "formatter" => '<span class="form-datatable-change" onclick="changeElement(this, {value});"></span>')
	    )),
	    "editable" => json_encode(array("verification", "min_value", "max_value", "code_error", "message_error")),
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

    //CREA LAS COLUMNAS DE REASON
    protected function getReasonColumns()
    {
	$header = array("rule", "code_error", "message_error");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "rule", "label" => "Regla", "sortable" => true),
		array("key" => "code_error", "label" => "Motivo", "sortable" => true, "allowHTML" => true),
		array("key" => "message_error", "label" => "Mensaje de error", "sortable" => true, "allowHTML" => true),
		array("key" => "edit", "allowHTML" => true, "formatter" => '<span class="form-datatable-change" onclick="changeElement(this, {value});"></span>')
	    )),
	    "editable" => json_encode(array("code_error", "message_error")),
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

    //CREA LAS COLUMNAS DE UNFILLED
    protected function getUnfilledColumns()
    {
	$header = array("batch", "filename", "create_at", "reason");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "batch", "label" => "# Lote", "sortable" => true),
		array("key" => "filename", "label" => "Nombre del archivo", "sortable" => true, "allowHTML" => true),
		array("key" => "create_at", "label" => "Importado el", "sortable" => true, "allowHTML" => true),
		array("key" => "reason", "label" => "Motivo de descarte", "sortable" => true, "allowHTML" => true)
	    )),
	    "editable" => 0,
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

    //COLUMNAS DE ADMINISTRACION
    protected function getAdminColumns()
    {
	$header = array("username", "email", "profile", "password", "status");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "username", "label" => "Nombre de Usuario", "sortable" => true),
		array("key" => "email", "label" => "Correo electrónico", "sortable" => true),
		array("key" => "profile", "label" => "Nivel de Acceso", "sortable" => false, "allowHTML" => true),
		array("key" => "password", "label" => "Contraseña validación", "sortable" => false, "allowHTML" => true),
		array("key" => "status", "label" => "Activo (S/N)", "sortable" => true),
		array("key" => "edit", "allowHTML" => true)
	    )),
	    "editable" => 0,
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

    //COLUMNAS DEL ESTUDIO DE ANALITO
    protected function getAnaStudyColumns()
    {
        $header = array("analyte", "analyte_is", "cs_number", "qc_number", "unit", "is", "use");
        $data   = $this->getData();

        return array(
            "data"     => (!empty($data)) ? json_encode($data) : 0,
            "columns"  => json_encode(array(
                array("key" => "analyte", "label" => "Analito", "sortable" => true),
                array("key" => "analyte_is", "label" => "Patrón Interno (IS)", "sortable" => true, "allowHTML" => true),
                array("key" => "cs_number", "label" => "Núm. CS", "sortable" => true, "allowHTML" => true),
                array("key" => "qc_number", "label" => "Núm. QC", "sortable" => true),
                array("key" => "unit", "label" => "Unidades", "sortable" => false),
                array("key" => "is", "label" => "% var IS", "sortable" => true, "allowHTML" => true),
                array("key" => "use", "label" => "usar", "sortable" => false, "allowHTML" => true, "formatter" => '{value}'),
                array("key" => "edit", "allowHTML" => true)
            )),
            "editable" => json_encode(array("analyte", "analyte_is", "cs_number", "qc_number", "unit", "is", "use")),
            "header"   => json_encode($header),
            "filters"  => $this->getFilters($header)
        );
    }

    //COLUMNAS DEL LOTE
    protected function getBatchColumns()
    {
        $header = array("batch", "filename", "create_at", "valid_flag", "validation_date", "result", "modify", "accepted_flag", "justification");
        $data   = $this->getData();

        return array(
            "data"     => (!empty($data)) ? json_encode($data) : 0,
            "columns"  => json_encode(array(
                array("key" => "batch", "label" => "Lote #", "sortable" => true),
                array("key" => "filename", "label" => "Archivo", "sortable" => true),
                array("key" => "create_at", "label" => "Importado el", "sortable" => true),
                array("key" => "valid_flag", "label" => "Validar", "sortable" => false, "allowHTML" => true),
                array("key" => "validation_date", "label" => "Validado el", "sortable" => true),
                array("key" => "result", "label" => "Resultado", "sortable" => true),
                array("key" => "modify", "label" => "Modificar resultado", "sortable" => false, "allowHTML" => true),
                array("key" => "accepted_flag", "label" => "Válido ADM", "sortable" => true),
                array("key" => "justification", "label" => "Justificar Modificación", "sortable" => false)
            )),
            "editable" => json_encode(array("accepted_flag", "justification")),
            "header"   => json_encode($header),
            "filters"  => $this->getFilters($header)
        );
    }

    //COLUMNAS DEL SAMPLE
    protected function getSampleBatchColumns()
    {
        $header = array("filename", "sample_name", "accuracy", "use_record","reason");
        $data   = $this->getData();

        return array(
            "data"     => (!empty($data)) ? json_encode($data) : 0,
            "columns"  => json_encode(array(
                array("key" => "filename", "label" => "File Name", "sortable" => true),
                array("key" => "sample_name", "label" => "Sample Name", "sortable" => true),
                array("key" => "accuracy", "label" => "Accuracy", "sortable" => true),
                array("key" => "use_record", "label" => "Use Record", "sortable" => true),
                array("key" => "reason", "label" => "Motivo del rechazo", "sortable" => false, "allowHTML" => true),
                array("key" => "edit", "allowHTML" => true)
            )),
            "editable" => 0,
            "header"   => json_encode($header),
            "filters"  => $this->getFilters($header)
        );
    }

    //COLUMNAS DEL AUDIT
    protected function getAuditColumns()
    {
        $header = array("created_at", "user", "section", "audit_description");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "created_at", "label" => "Fecha y hora", "sortable" => true),
		array("key" => "user", "label" => "Usuario", "sortable" => true),
		array("key" => "section", "label" => "Acción", "sortable" => true),
                array("key" => "audit_description", "label" => "Descripción", "sortable" => false, "allowHTML" => true),
		array("key" => "edit", "allowHTML" => true)
	    )),
	    "editable" => 0,
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

    protected function prepare($headers)
    {
	$options = array();
	$data = $this->getData();

	foreach ($headers as $header)
	{
	    foreach ($data as $row)
	    {
		$options[$header][] = $row[$header];
	    }
	}
	return $options;
    }

    protected function getAutoFilter($headers)
    {
	$filters = "";
	foreach ($headers as $key => $value)
	{
	    $filter = '<select id="yui3-datatable-filter-' . $value . '" class="yui3-datatable-filter"><option value="-1">TODOS</option></select>';
	    $filters .= sprintf("<td>%s</td>", $filter);
	}
	return $filters;
    }

    protected function getFilters($headers)
    {
	$filters = "";
	$data = $this->prepare($headers);

	foreach ($data as $key => $value)
	{
            $filter = (!in_array($key, $this->_notFilterable)) ? '<select id="yui3-datatable-filter-' . $key . '" class="yui3-datatable-filter">' . $this->getOptions($value) . '</select>' : "";
	    $filters .= sprintf("<td>%s</td>", $filter);
	}

	if ($filters == "")
	    $filters = $this->getAutoFilter($headers);

        switch ($this->_profile)
        {
            case "Administrador":
                $elements = $this->isAdministrador();
                break;
            case "Director Estudio":
                $elements = $this->isDirectorEstudio();
                break;
            case "UGC":
                $elements = $this->isUGC();
                break;
            case "Laboratorio":
                $elements = $this->isLaboratorio();
                break;
            case "Sustancias":
                $elements = $this->isSustancias();
                break;
        }

	return sprintf('<tr>%1$s<td class="form-datatable-edit">%2$s</td></tr>', $filters, $elements);
    }

    protected function getOptions($data)
    {
	$options = '<option value="-1">TODOS</option>';
        $aux = array();
	foreach ($data as $key => $value)
	{
            if(!in_array($value, $aux))
            {
                $aux[] = $value;
                $options .= '<option value="' . $key . '">' . $value . '</option>';
            }
	}
	return $options;
    }

    public function getDatatable()
    {
	$response = array();

	switch ($this->_datatable)
	{
	    case Datatable::DATATABLE_ANALYTE:
		$response = $this->getAnalyteColumns();
		break;
	    case Datatable::DATATABLE_STUDY:
		$response = $this->getStudyColumns();
		break;
	    case Datatable::DATATABLE_PARAMETER:
		$response = $this->getParameterColumns();
		break;
	    case Datatable::DATATABLE_REASON:
		$response = $this->getReasonColumns();
		break;
	    case Datatable::DATATABLE_UNFILLED:
		$response = $this->getUnfilledColumns();
		break;
	    case Datatable::DATATABLE_ADMIN:
		$response = $this->getAdminColumns();
		break;
            case Datatable::DATATABLE_ANASTUDY:
                $response = $this->getAnaStudyColumns();
                break;
            case Datatable::DATATABLE_BATCH:
                $response = $this->getBatchColumns();
                break;
            case Datatable::DATATABLE_SAMPLE_BATCH:
                $response = $this->getSampleBatchColumns();
                break;
            case Datatable::DATATABLE_AUDIT_TRAIL:
                $response = $this->getAuditColumns();
                break;
        }

	return $response;
    }

    protected function isAdministrador()
    {
        switch ($this->_datatable)
	{
	    case Datatable::DATATABLE_ANALYTE:
		$elements = '<span class="form-datatable-new"></span><span class="form-download-excel" onclick="excel(1);"></span><input value="" type="submit"/>';
                break;
	    case Datatable::DATATABLE_STUDY:
		$elements = '<a href="' . $this->_base_url . '/study/create" class="form-datatable-new"></a><span class="form-download-excel" onclick="excel(2);"></span>';
		break;
	    case Datatable::DATATABLE_PARAMETER:
		$elements = '<span class="form-download-excel" onclick="excel(3);"></span><input value="" type="submit"/>';
		break;
	    case Datatable::DATATABLE_REASON:
		$elements = '<span class="form-datatable-new"></span><span class="form-download-excel" onclick="excel(4);"></span><input value="" type="submit"/>';
		break;
	    case Datatable::DATATABLE_UNFILLED:
		$elements = '<span class="form-download-excel" onclick="excel(5);"></span>';
		break;
	    case Datatable::DATATABLE_ADMIN:
		$elements = '<span class="form-download-excel" onclick="excel(6);"></span>';
		break;
            case Datatable::DATATABLE_ANASTUDY:
                $elements = '<span class="form-datatable-new"></span>';
                break;
            case Datatable::DATATABLE_BATCH:
                $elements = '<input value="" type="submit"/>';
                break;
            case Datatable::DATATABLE_AUDIT_TRAIL:
		$elements = '<span class="form-download-excel" onclick="excel(7);"></span>';
		break;
	    case Datatable::DATATABLE_SAMPLE_BATCH:
                $elements = '<input value="" type="submit"/>';
		break;
        }

        return $elements;
    }

    protected function isDirectorEstudio()
    {
        switch ($this->_datatable)
	{
	    case Datatable::DATATABLE_STUDY:
		$elements = '<a href="' . $this->_base_url . '/study/create" class="form-datatable-new"></a><span class="form-download-excel" onclick="excel(2);"></span>';
		break;
            case Datatable::DATATABLE_ANASTUDY:
                $elements = '<span class="form-datatable-new"></span>';
                break;
            case Datatable::DATATABLE_UNFILLED:
		$elements = '<span class="form-download-excel" onclick="excel(5);"></span>';
		break;
	    case Datatable::DATATABLE_SAMPLE_BATCH:
                $elements = '<input value="" type="submit"/>';
		break;
            default :
                $elements = "";
                break;
        }

        return $elements;
    }

    protected function isUGC()
    {
        switch ($this->_datatable)
	{
	    case Datatable::DATATABLE_STUDY:
		$elements = '<span class="form-download-excel" onclick="excel(2);"></span>';
		break;
            case Datatable::DATATABLE_UNFILLED:
		$elements = '<span class="form-download-excel" onclick="excel(5);"></span>';
		break;
            default :
                $elements = "";
                break;
        }

        return $elements;
    }

    protected function isLaboratorio()
    {
        switch ($this->_datatable)
	{
	    case Datatable::DATATABLE_STUDY:
		$elements = '<span class="form-download-excel" onclick="excel(2);"></span>';
		break;
            case Datatable::DATATABLE_UNFILLED:
		$elements = '<span class="form-download-excel" onclick="excel(5);"></span>';
		break;
	    case Datatable::DATATABLE_SAMPLE_BATCH:
                $elements = '<input value="" type="submit"/>';
		break;
            default :
                $elements = "";
                break;
        }

        return $elements;
    }

    protected function isSustancias()
    {
        switch ($this->_datatable)
	{
            case Datatable::DATATABLE_ANALYTE:
		$elements = '<span class="form-datatable-new"></span><span class="form-download-excel" onclick="excel(1);"></span><input value="" type="submit"/>';
                break;
            default :
                $elements = "";
                break;
        }

        return $elements;
    }

}
