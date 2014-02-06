<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Alae\Controller;

use Zend\View\Model\ViewModel,
    Alae\Controller\BaseController,
    Zend\View\Model\JsonModel,
    Alae\Service\Datatable;

class AnalyteController extends BaseController
{
    protected $_document = '\\Alae\\Entity\\Analyte';

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

        $error = "";

        if ($request->isPost())
        {
            $createNames      = $request->getPost('create-name');
            $createShortnames = $request->getPost('create-shortname');
            $updateNames      = $request->getPost('update-name');
            $updateShortnames = $request->getPost('update-shortname');

            if (!empty($createNames))
            {
                $User = $this->_getSession();

                foreach ($createNames as $key => $value)
                {
                    $findByName       = $this->getRepository()->findBy(array("name" => $value));
                    $findByShortnames = $this->getRepository()->findBy(array("shortening" => $createShortnames[$key]));
                    if (count($findByName) > 0)
                    {
                        $error .= sprintf('<li>El analito %s ya está registrado. Por favor, intente de nuevo<li>', $value);
                    }
                    elseif (count($findByShortnames) > 0)
                    {
                        $error .= sprintf('<li>La abreviatura %s ya está registrada. Por favor, intente de nuevo<li>', $createShortnames[$key]);
                    }
                    else
                    {
                        try
                        {
                            $Analyte = new \Alae\Entity\Analyte();
                            $Analyte->setName($value);
                            $Analyte->setShortening($createShortnames[$key]);
                            $Analyte->setFkUser($User);
                            $this->getEntityManager()->persist($Analyte);
                            $this->getEntityManager()->flush();
                            $this->transaction(__METHOD__, "Ingreso de analitos", json_encode(array("User" => $User->getUsername(), "Name" => $Analyte->getName(), "Shortening" => $Analyte->getShortening())));
                        }
                        catch (Exception $e)
                        {
                            $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(array("User" => $User->getUsername(), "Name" => $value, "Shortening" => $createShortnames[$key])));
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

            if (!empty($updateNames))
            {
                $User = $this->_getSession();

                foreach ($updateNames as $key => $value)
                {
                    $Analyte = $this->getRepository()->find($key);

                    if ($Analyte && $Analyte->getPkAnalyte())
                    {
                        $findByName       = $this->getRepository()->findBy(array("name" => $value));
                        $findByShortnames = $this->getRepository()->findBy(array("shortening" => $updateShortnames[$key]));
                        if (count($findByName) > 0)
                        {
                            $error .= sprintf('<li>El analito %s ya está registrado. Por favor, intente de nuevo<li>', $value);
                        }
                        elseif (count($findByShortnames) > 0)
                        {
                            $error .= sprintf('<li>La abreviatura %s ya está registrada. Por favor, intente de nuevo<li>', $createShortnames[$key]);
                        }
                        else
                        {
                            try
                            {
                                $older = array("User" => $Analyte->getFkUser()->getUsername(), "Name" => $Analyte->getName(), "Shortening" => $Analyte->getShortening());

                                $Analyte->setName($updateNames[$key]);
                                $Analyte->setShortening($updateShortnames[$key]);
                                $Analyte->setFkUser($User);
                                $this->getEntityManager()->persist($Analyte);
                                $this->getEntityManager()->flush();

                                $audit = array(
                                    "Antiguos valores" => $older,
                                    "Nuevos valores"   => array("User" => $User->getUsername(), "Name" => $Analyte->getName(), "Shortening" => $Analyte->getShortening())
                                );

                                $this->transaction(__METHOD__, sprintf("Actualización de datos del analito con identificador #%d", $Analyte->getPkAnalyte()), json_encode($audit));
                            }
                            catch (Exception $e)
                            {
                                $message = sprintf("Error! Se ha intentado guardar la siguiente información: %s", json_encode(array("Id" => $Analyte->getPkAnalyte(), "User" => $User->getUsername(), "Name" => $updateNames[$key], "Shortening" => $updateShortnames[$key])));
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
        }

        $data     = array();
        $elements = $this->getRepository()->findBy(array("status" => true));

        foreach ($elements as $analyte)
        {
            $data[] = array(
                "id"        => $analyte->getPkAnalyte(),
                "name"      => $analyte->getName(),
                "shortname" => $analyte->getShortening(),
                "edit"      => $analyte->getPkAnalyte()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_ANALYTE);
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('user', $this->_getSession());
        $viewModel->setVariable('error', $error);
        return $viewModel;
    }

    public function deleteAction()
    {
        $request = $this->getRequest();

        if ($request->isGet())
        {
            $Analyte = $this->getRepository()->find($request->getQuery('pk'));

            if ($Analyte && $Analyte->getPkAnalyte())
            {
                $query = $this->getEntityManager()->createQuery("SELECT COUNT(a.fkAnalyte) FROM \Alae\Entity\AnalyteStudy a WHERE a.fkAnalyte = " . $Analyte->getPkAnalyte());
                $count = $query->getSingleScalarResult();

                if ($count == 0)
                {
                    try
                    {
                        $User = $this->_getSession();
                        $Analyte->setStatus(false);
                        $Analyte->setFkUser($User);
                        $this->getEntityManager()->persist($Analyte);
                        $this->getEntityManager()->flush();
                        $this->transaction(__METHOD__, sprintf("Se ha descativado el analito con identificador #%d", $Analyte->getPkAnalyte()), json_encode(array("User" => $User->getUsername(), "Name" => $Analyte->getName(), "Shortening" => $Analyte->getShortening())));
                        return new JsonModel(array("status" => true));
                    }
                    catch (Exception $e)
                    {
                        $message = sprintf("Se ha presentado un error al desactivar el analito con identificador #%d", $Analyte->getPkAnalyte());
                        /*
                         * Este array debo crearlo en la seccion de errores OJOJOJO!!!!!
                         */
                        $error   = array(
                            "description" => $message,
                            "message"     => $e,
                            "section"     => __METHOD__
                        );
                        $this->transactionError($error);
                        return new JsonModel(array("status" => false, "message" => $message));
                    }
                }
                else
                {
                    $message = sprintf("El analito con identificador #%d, no puede desactivarse debido que esta asociado a uno o más estudios", $Analyte->getPkAnalyte());
                    $error   = array(
                        "description" => $message,
                        "message"     => "",
                        "section"     => __METHOD__
                    );

                    return new JsonModel(array("status" => false, "message" => $message));
                }
            }
        }
    }

    protected function download()
    {
        $data   = array();
        $data[] = array("Id", "Nombre Analito", "Abreviatura");
        $elements = $this->getRepository()->findBy(array("status" => true));

        foreach ($elements as $analyte)
        {
            $data[] = array($analyte->getPkAnalyte(), $analyte->getName(), $analyte->getShortening());
        }

        return json_encode($data);
    }

    public function excelAction()
    {
        \Alae\Service\Download::excel("listado_de_analitos", $this->download());
    }

    public function pdfAction()
    {
        \Alae\Service\Download::pdf("listado_de_analitos", $this->download());
    }
}