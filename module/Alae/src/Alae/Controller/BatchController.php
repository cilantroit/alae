<?php
/**
 * Description of BatchController
 *
 * @author Maria Quiroz
 */

namespace Alae\Controller;

use Zend\View\Model\ViewModel,
    Alae\Controller\BaseController,
    Zend\View\Model\JsonModel,
    Alae\Service\Datatable;

class BatchController extends BaseController
{
    protected $_document = '\\Alae\\Entity\\Batch';

    public function unfilledAction()
    {
        $data     = array();
        $elements = $this->getRepository()->findBy(array("fkAnalyte" => null, "fkStudy" => null));

        foreach ($elements as $unfilled)
        {
            if (!is_null($unfilled->getFkParameter()))
            {
                $data[] = array(
                    "batch"     => $unfilled->getSerial(),
                    "filename"  => $unfilled->getFileName(),
                    "create_at" => $unfilled->getCreatedAt(),
                    "reason"    => utf8_encode($unfilled->getFkParameter()->getMessageError())
                );
            }
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_UNFILLED);
        return new ViewModel($datatable->getDatatable());
    }

    public function downloadAction()
    {
        $data     = array();
        $data[]   = array("# Lote", "Nombre del archivo", "Importado el", "Motivo de descarte");
        $elements = $this->getRepository()->findBy(array("fkAnalyte" => null, "fkStudy" => null));

        foreach ($elements as $unfilled)
        {
            $data[] = array(
                $unfilled->getSerial(),
                $unfilled->getFileName(),
                $unfilled->getCreatedAt(),
                $unfilled->getFkParameter()->getMessageError()
            );
        }

        return new JsonModel($data);
    }

    public function excelAction()
    {
        \Alae\Service\Download::excel("http://localhost/alae/public/batch/download", "lotes_sin_asignar");
    }
}