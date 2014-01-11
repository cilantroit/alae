<?php

/**
 * Description of Datatable
 *
 * @author Maria Quiroz
 */

namespace Alae\Service;

class Datatable
{

    const DATATABLE_ANALYTE = 'analyte';
    const DATATABLE_STUDY = 'study';
    const DATATABLE_PARAMETER = 'parameter';
    const DATATABLE_REASON = 'reason';
    const DATATABLE_UNFILLED = 'unfilled';
    const DATATABLE_ADMIN = 'admin';

    protected $_data;
    protected $_datatable;
    protected $_base_url;

    public function __construct($data, $datatable)
    {
	$this->_data = $data;
	$this->_datatable = $datatable;
	$this->_base_url = \Alae\Service\Helper::getVarsConfig("base_url");
    }

    protected function getData()
    {
	return $this->_data;
    }

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
		array("key" => "edit", "allowHTML" => true, "formatter" => '<span class="form-datatable-change" onclick="changeElement(this, {value});"></span><span class="form-datatable-delete" onclick="removeElement(this, {value});"></span>')
	    )),
	    "editable" => json_encode(array("name", "shortname")),
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

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
		array("key" => "edit", "allowHTML" => true, "formatter" => '<a href="' . $this->_base_url . '/study/edit/{value}"><span class="form-datatable-change"></span></a><span class="form-datatable-delete" onclick="removeElement(this, {value});"></span>')
	    )),
	    "editable" => 0,
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

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
	    "editable" => json_encode(array("rule", "verification", "min_value", "max_value", "code_error", "message_error")),
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

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
	    "editable" => json_encode(array("rule", "code_error", "message_error")),
	    "header" => json_encode($header),
	    "filters" => $this->getFilters($header)
	);
    }

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

    protected function getAdminColumns()
    {
	$header = array("username", "email", "profile", "password", "status");
	$data = $this->getData();

	return array(
	    "data" => (!empty($data)) ? json_encode($data) : 0,
	    "columns" => json_encode(array(
		array("key" => "username", "label" => "Nombre de Usuario", "sortable" => true),
		array("key" => "email", "label" => "Correo electrónico", "sortable" => true),
		array("key" => "profile", "label" => "Nivel de Acceso", "sortable" => true, "allowHTML" => true),
		array("key" => "password", "label" => "Contraseña validación", "sortable" => true, "allowHTML" => true),
		array("key" => "status", "label" => "Activo (S/N)", "sortable" => true),
		array("key" => "edit", "allowHTML" => true, "formatter" => '<span class="form-datatable-approve" onclick="approve({value})"></span><span class="form-datatable-reject" onclick="reject({value});"></span>')
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
	    $filter = '<select id="yui3-datatable-filter-' . $value . '" class="yui3-datatable-filter"><option value="-1">autofiltro</option></select>';
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
	    $filter = '<select id="yui3-datatable-filter-' . $key . '" class="yui3-datatable-filter">' . $this->getOptions($value) . '</select>';
	    $filters .= sprintf("<td>%s</td>", $filter);
	}

	if ($filters == "")
	    $filters = $this->getAutoFilter($headers);

	switch ($this->_datatable)
	{
	    case Datatable::DATATABLE_ANALYTE:
		$elements = '<span class="form-datatable-new"></span><a href="' . $this->_base_url . '/analyte/excel"><span class="form-download-excel"></span></a><a href="' . $this->_base_url . '/analyte/pdf"><span class="form-download-pdf"></span></a><input value="" type="submit"/>';
		break;
	    case Datatable::DATATABLE_STUDY:
		$elements = '<a href="' . $this->_base_url . '/study/create" class="form-datatable-new"></a><a href="' . $this->_base_url . '/study/excel"><span class="form-download-excel"></span></a>';
		break;
	    case Datatable::DATATABLE_PARAMETER:
		$elements = '<a href="' . $this->_base_url . '/parameter/excel/1"><span class="form-download-excel"></span></a><input value="" type="submit"/>';
		break;
	    case Datatable::DATATABLE_REASON:
		$elements = '<span class="form-datatable-new"></span><a href="' . $this->_base_url . '/parameter/excel/2"><span class="form-download-excel"></span></a><input value="" type="submit"/>';
		break;
	    case Datatable::DATATABLE_UNFILLED:
		$elements = '<a href="' . $this->_base_url . '/batch/excel"><span class="form-download-excel"></span></a>';
		break;
	    case Datatable::DATATABLE_ADMIN:
		$elements = '<a href="' . $this->_base_url . '/user/excel"><span class="form-download-excel"></span></a>';
		break;
	}

	return sprintf('<tr>%1$s<td class="form-datatable-edit">%2$s</td></tr>', $filters, $elements);
    }

    protected function getOptions($data)
    {
	$options = '<option value="-1">autofiltro</option>';
	foreach ($data as $key => $value)
	{
	    $options .= '<option value="' . $key . '">' . $value . '</option>';
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
	}

	return $response;
    }

}
