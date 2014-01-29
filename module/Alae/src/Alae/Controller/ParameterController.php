<?php
/**
 * Description of ParameterControlller
 *
 * @author Maria Quiroz
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

            $updateRules        = $request->getPost('update-rule');
            $updateVerification = $request->getPost('update-verification');
            $updateMin          = $request->getPost('update-min_value');
            $updateMax          = $request->getPost('update-max_value');
            $updateCode         = $request->getPost('update-code_error');
            $updateMessage      = $request->getPost('update-message_error');

            foreach ($updateRules as $key => $value)
            {
                $Parameter = $this->getRepository()->find($key);

                if ($Parameter && $Parameter->getPkParameter())
                {
                    try
                    {
                        $older = array(
                            "User"             => $Parameter->getFkUser()->getUsername(),
                            "Regla"            => $Parameter->getRule(),
                            "Descripcion"      => $Parameter->getVerification(),
                            "Min"              => $Parameter->getMinValue(),
                            "Max"              => $Parameter->getMaxValue(),
                            "Motivo"           => $Parameter->getCodeError(),
                            "Mensaje de error" => $Parameter->getMessageError()
                        );

                        $Parameter->setRule($value);
                        $Parameter->setVerification($updateVerification[$key]);
                        $Parameter->setMinValue($updateMin[$key]);
                        $Parameter->setMaxValue($updateMax[$key]);
                        $Parameter->setCodeError($updateCode[$key]);
                        $Parameter->setMessageError($updateMessage[$key]);
                        $Parameter->setFkUser($User);
                        $this->getEntityManager()->persist($Parameter);
                        $this->getEntityManager()->flush();

                        $audit = array(
                            "Antiguos valores" => $older,
                            "Nuevos valores"   => array(
                                "User"             => $User->getUsername(),
                                "Regla"            => $Parameter->getRule(),
                                "Descripcion"      => $Parameter->getVerification(),
                                "Min"              => $Parameter->getMinValue(),
                                "Max"              => $Parameter->getMaxValue(),
                                "Motivo"           => $Parameter->getCodeError(),
                                "Mensaje de error" => $Parameter->getMessageError()
                            )
                        );

                        $this->transaction(__METHOD__, sprintf("Actualización de datos del parámetro de verificación %s", $Parameter->getRule()), json_encode($audit));
                    }
                    catch (Exception $e)
                    {
                        $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(
                                        array(
                                            "Id"               => $Parameter->getPkParameter(),
                                            "User"             => $User->getUsername(),
                                            "Regla"            => $value,
                                            "Descripcion"      => $updateVerification[$key],
                                            "Min"              => $updateMin[$key],
                                            "Max"              => $updateMax[$key],
                                            "Motivo"           => $updateCode[$key],
                                            "Mensaje de error" => $updateMessage[$key]
                        )));
                        /*
                         * Este array debo crearlo en la seccion de errores OJOJOJO!!!!!
                         */
                        $error   = array(
                            "description" => $message,
                            "message"     => $e,
                            "section"     => __METHOD__
                        );

                        $this->transactionError($error);
                    }
                }
            }
        }

        $data     = array();
        //ALTER TABLE ADD COLUMN type DEFAULT 1.
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

        $datatable = new Datatable($data, Datatable::DATATABLE_PARAMETER);
        return new ViewModel($datatable->getDatatable());
    }

    public function downloadAction()
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

        return new JsonModel($data);
    }

    public function downloadreasonAction()
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

        return new JsonModel($data);
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
                        $Parameter = new \Alae\Entity\Parameter();
                        $Parameter->setRule($value);
                        $Parameter->setCodeError($createCode[$key]);
                        $Parameter->setMessageError($createMessage[$key]);
                        $Parameter->setFkUser($User);
                        $Parameter->setTypeParam(false);
                        $this->getEntityManager()->persist($Parameter);
                        $this->getEntityManager()->flush();

                        $this->transaction(__METHOD__, "Ingreso de código de error no automatizable", json_encode(array(
                            "User"             => $User->getUsername(),
                            "Regla"            => $Parameter->getRule(),
                            "Motivo"           => $Parameter->getCodeError(),
                            "Mensaje de error" => $Parameter->getMessageError()
                        )));
                    }
                    catch (Exception $e)
                    {
                        $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(
                                        array(
                                            "User"             => $User->getUsername(),
                                            "Regla"            => $value,
                                            "Motivo"           => $createCode[$key],
                                            "Mensaje de error" => $createMessage[$key]
                        )));
                        /*
                         * Este array debo crearlo en la seccion de errores OJOJOJO!!!!!
                         */
                        $error   = array(
                            "description" => $message,
                            "message"     => $e,
                            "section"     => __METHOD__
                        );

                        $this->transactionError($error);
                    }
                }
            }

            if (!empty($updateRules))
            {
                foreach ($updateRules as $key => $value)
                {
                    $Parameter = $this->getRepository()->find($key);

                    if ($Parameter && $Parameter->getPkParameter())
                    {
                        try
                        {
                            $older = array(
                                "User"             => $Parameter->getFkUser()->getUsername(),
                                "Regla"            => $Parameter->getRule(),
                                "Motivo"           => $Parameter->getCodeError(),
                                "Mensaje de error" => $Parameter->getMessageError()
                            );

                            $Parameter->setRule($value);
                            $Parameter->setCodeError($updateCode[$key]);
                            $Parameter->setMessageError($updateMessage[$key]);
                            $Parameter->setFkUser($User);
                            $this->getEntityManager()->persist($Parameter);
                            $this->getEntityManager()->flush();

                            $audit = array(
                                "Antiguos valores" => $older,
                                "Nuevos valores"   => array(
                                    "User"             => $User->getUsername(),
                                    "Regla"            => $Parameter->getRule(),
                                    "Motivo"           => $Parameter->getCodeError(),
                                    "Mensaje de error" => $Parameter->getMessageError()
                                )
                            );

                            $this->transaction(__METHOD__, sprintf("Actualización de datos del parámetro de verificación %s", $Parameter->getRule()), json_encode($audit));
                        }
                        catch (Exception $e)
                        {
                            $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(
                                            array(
                                                "Id"               => $Parameter->getPkParameter(),
                                                "User"             => $User->getUsername(),
                                                "Regla"            => $value,
                                                "Motivo"           => $updateCode[$key],
                                                "Mensaje de error" => $updateMessage[$key]
                            )));
                            /*
                             * Este array debo crearlo en la seccion de errores OJOJOJO!!!!!
                             */
                            $error   = array(
                                "description" => $message,
                                "message"     => $e,
                                "section"     => __METHOD__
                            );

                            $this->transactionError($error);
                        }
                    }
                }
            }
        }

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

        $datatable = new Datatable($data, Datatable::DATATABLE_REASON);
        return new ViewModel($datatable->getDatatable());
    }

    public function excelAction()
    {

        $download = ($this->params('param') == "1") ? "" : "reason";
        $filename = ($this->params('param') == "1") ? "verificaciones_de_lotes_de_analitos" : "codigos_de_error_no_automatizables";

        \Alae\Service\Download::excel(\Alae\Service\Helper::getVarsConfig("base_url") . "/parameter/download" . $download, $filename);
    }
}