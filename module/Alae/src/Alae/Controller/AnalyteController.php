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
                            $this->transaction(
                                "Ingreso de analitos",
                                sprintf('Se ha ingresado el analito %1$s(%2$s)',
                                    $Analyte->getName(),
                                    $Analyte->getShortening()
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
                                $older = sprintf('Valores antiguos -> %1$s(%2$s)',
                                    $Analyte->getName(),
                                    $Analyte->getShortening()
                                );
                                $Analyte->setName($updateNames[$key]);
                                $Analyte->setShortening($updateShortnames[$key]);
                                $Analyte->setFkUser($User);
                                $this->getEntityManager()->persist($Analyte);
                                $this->getEntityManager()->flush();

                                $this->transaction(
                                    "Editar analito",
                                    sprintf('%1$s<br> Valores nuevos -> %2$s(%3$s)',
                                        $older,
                                        $updateNames[$key],
                                        $updateShortnames[$key]
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
                        $this->transaction(
                            "Eliminar analito",
                            sprintf('Se ha eliminado el analito %1$s(%2$s)',
                                $Analyte->getName(),
                                $Analyte->getShortening()
                            ),
                            false
                        );
                        return new JsonModel(array("status" => true));
                    }
                    catch (Exception $e)
                    {
                        exit;
                    }
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