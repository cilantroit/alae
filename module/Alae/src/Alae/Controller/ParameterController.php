<?php
/**
 * Modulo de gestión de los parámetros a usar en las Verificaciones.
 * En este fichero está creada la clase que se encarga de gestionar y controlar las opciones de parametrización del sistema,
 * accesibles desde la opción parametrización de menú principal.
 * También controla la creación del fichero excel exportable.
 *
 * @author Maria Quiroz
 * Fecha de creación: 12/05/2014
 */

namespace Alae\Controller;

use Zend\View\Model\ViewModel,
    Alae\Controller\BaseController,
    Zend\View\Model\JsonModel,
    Alae\Service\Datatable;

class ParameterController extends BaseController
{
    protected $_document = '\\Alae\\Entity\\Parameter';

    public function init()
    {
        if (!$this->isLogged())
        {
            header('Location: ' . \Alae\Service\Helper::getVarsConfig("base_url"));
            exit;
        }
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost())
        {
            $User = $this->_getSession();

            //$updateRules        = $request->getPost('update-rule');
            $updateVerification = $request->getPost('update-verification');
            $updateMin          = $request->getPost('update-min_value');
            $updateMax          = $request->getPost('update-max_value');
            $updateCode         = $request->getPost('update-code_error');
            $updateMessage      = $request->getPost('update-message_error');

            foreach ($updateVerification as $key => $value)
            {
                $Parameter = $this->getRepository()->find($key);

                if ($Parameter && $Parameter->getPkParameter())
                {
                    try
                    {
                        
                        $older = sprintf('Valores antiguos -> Descripción: %1$s, Min: %2$s, Max: %3$s, Motivo: %4$s, Mensaje de error: %5$s',
                            $Parameter->getVerification(),
                            $Parameter->getMinValue(),
                            $Parameter->getMaxValue(),
                            $Parameter->getCodeError(),
                            $Parameter->getMessageError()
                        );

                        //EDITA LOS PARAMETROS DEL SISTEMA
                        $Parameter->setVerification($updateVerification[$key]);
                        $Parameter->setMinValue($updateMin[$key]);
                        $Parameter->setMaxValue($updateMax[$key]);
                        $Parameter->setCodeError($updateCode[$key]);
                        $Parameter->setMessageError($updateMessage[$key]);
                        $Parameter->setFkUser($User);
                        $this->getEntityManager()->persist($Parameter);
                        $this->getEntityManager()->flush();
                        $this->transaction(
                            "Edición de Parámetros del sistema",
                            sprintf('%1$s<br>Valores nuevos -> Descripción: %2$s, Min: %3$s, Max: %4$s, Motivo: %5$s, Mensaje de error: %6$s',
                                $older,
                                $updateVerification[$key],
                                $updateMin[$key],
                                $updateMax[$key],
                                $updateCode[$key],
                                $updateMessage[$key]
                            ),
                            false
                        );
                    }
                    catch (Exception $e)
                    {
                        exit;
                    }
                }
            }
        }

        //MUESTRA LOS PARAMETROS EN PANTALLA
        $data     = array();
        $elements = $this->getRepository()->findBy(array("typeParam" => true));

        foreach ($elements as $parameter)
        {

            $data[] = array(
                "rule"          => $parameter->getRule(),
                "verification"  => $parameter->getVerification(),
                "min_value"     => ($parameter->getMinValue() > 0) ? $parameter->getMinValue() : "",
                "max_value"     => ($parameter->getMaxValue() > 0) ? $parameter->getMaxValue() : "",
                "code_error"    => $parameter->getCodeError(),
                "message_error" => $parameter->getMessageError(),
                "edit"          => $parameter->getPkParameter()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_PARAMETER, $this->_getSession()->getFkProfile()->getName());
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('user', $this->_getSession());
        return $viewModel;
    }

    public function reasonAction()
    {
        $request = $this->getRequest();

        if ($request->isPost())
        {
            $User = $this->_getSession();

            $createRules   = $request->getPost('create-rule');
            $createCode    = $request->getPost('create-code_error');
            $createMessage = $request->getPost('create-message_error');
            $updateRules   = $request->getPost('update-rule');
            $updateCode    = $request->getPost('update-code_error');
            $updateMessage = $request->getPost('update-message_error');

            if (!empty($createRules))
            {
                foreach ($createRules as $key => $value)
                {
                    try
                    {
                        //CREA CODIGOS DE ERROR NO AUTOMATIZABLES
                        $Parameter = new \Alae\Entity\Parameter();
                        $Parameter->setRule($value);
                        $Parameter->setCodeError($createCode[$key]);
                        $Parameter->setMessageError($createMessage[$key]);
                        $Parameter->setFkUser($User);
                        $Parameter->setTypeParam(false);
                        $this->getEntityManager()->persist($Parameter);
                        $this->getEntityManager()->flush();
                        $this->transaction(
                            "Creación de Códigos de error no automatizables",
                            sprintf('Regla: %1$s, Motivo: %2$s, Mensaje de error: %3$s',
                                $value,
                                $createCode[$key],
                                $createMessage[$key]
                            ),
                            false
                        );
                    }
                    catch (Exception $e)
                    {
                        exit;
                    }
                }
            }

            if (!empty($updateCode))
            {
                foreach ($updateCode as $key => $value)
                {
                    $Parameter = $this->getRepository()->find($key);

                    if ($Parameter && $Parameter->getPkParameter())
                    {
                        try
                        {
                            //EDITA LOS CODIGOS DE ERROR NO AUTOMATIZABLES
                            $older = sprintf('Valores antiguos -> Motivo: %1$s, Mensaje de error: %2$s',
                                $Parameter->getCodeError(),
                                $Parameter->getMessageError()
                            );
                            $Parameter->setCodeError($updateCode[$key]);
                            $Parameter->setMessageError($updateMessage[$key]);
                            $Parameter->setFkUser($User);
                            $this->getEntityManager()->persist($Parameter);
                            $this->getEntityManager()->flush();
                            $this->transaction(
                                "Edición de Códigos de error no automatizables",
                                sprintf('%1$s<br>Motivo: %2$s, Mensaje de error: %3$s',
                                    $older,
                                    $updateCode[$key],
                                    $updateMessage[$key]
                                ),
                                false
                            );
                        }
                        catch (Exception $e)
                        {
                            exit;
                        }
                    }
                }
            }
        }

        //MUESTRA LOS TIPOS DE PARAMETROS EN PANTALLA
        $data     = array();
        $elements = $this->getRepository()->findBy(array("typeParam" => false));

        foreach ($elements as $parameter)
        {

            $data[] = array(
                "rule"          => $parameter->getRule(),
                "code_error"    => $parameter->getCodeError(),
                "message_error" => $parameter->getMessageError(),
                "edit"          => $parameter->getPkParameter()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_REASON, $this->_getSession()->getFkProfile()->getName());
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('user', $this->_getSession());
        return $viewModel;
    }

    protected function download()
    {
        $data     = array();
        $data[]   = array("Regla", "Descripción", "Mín", "Máx", "Motivo", "Mensaje de error");
        $elements = $this->getRepository()->findBy(array("typeParam" => true));

        foreach ($elements as $parameter)
        {
            $data[] = array(
                $parameter->getRule(),
                $parameter->getVerification(),
                ($parameter->getMinValue() > 0) ? $parameter->getMinValue() : "",
                ($parameter->getMaxValue() > 0) ? $parameter->getMaxValue() : "",
                $parameter->getCodeError(),
                $parameter->getMessageError()
            );
        }

        return json_encode($data);
    }

    protected function downloadreason()
    {
        $data     = array();
        $data[]   = array("Regla", "Motivo", "Mensaje de error");
        $elements = $this->getRepository()->findBy(array("typeParam" => false));

        foreach ($elements as $parameter)
        {
            $data[] = array(
                $parameter->getRule(),
                $parameter->getCodeError(),
                $parameter->getMessageError()
            );
        }

        return json_encode($data);
    }

    public function excelAction()
    {
        //EXCEL DE VERIFICACION DE LOTES DE ANALITOS
        $json = ($this->params('param') == "1") ? "" : $this->downloadreason();
        $filename = ($this->params('param') == "1") ? "verificaciones_de_lotes_de_analitos" : "codigos_de_error_no_automatizables";

        \Alae\Service\Download::excel($filename, $json);
    }
}
