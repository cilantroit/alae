<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
 
 /**
 * Modulo de gestión de Analitos. Este fichero contiene las funciones encargadas
 * de agregar un analito y de eliminar un analito.
 * @author Maria Quiroz
   Fecha de creación: 15/05/2014
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
                    //VERIFICA QUE EL ANALITO YA ESTÉ REGISTRADO
                    if (count($findByName) > 0)
                    {
                        $error .= sprintf('<li>El analito %s ya está registrado. Por favor, intente de nuevo<li>', $value);
                    }
                    //VERIFICA QUE LA ABREVIATURA YA ESTÉ AGREGADA
                    elseif (count($findByShortnames) > 0)
                    {
                        $error .= sprintf('<li>La abreviatura %s ya está registrada. Por favor, intente de nuevo<li>', $createShortnames[$key]);
                    }
                    else
                    {
                        try
                        {
                            //AGREGA A LA TABLA ANALYTE
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

            //ACTUALIZA EL NOMBRE Y SHORTNAME SI ESTOS NO EXISTEN
            if (!empty($updateNames))
            {
                $User = $this->_getSession();

                foreach ($updateNames as $key => $value)
                {
                    $Analyte = $this->getRepository()->find($key);

                    if ($Analyte && $Analyte->getPkAnalyte())
                    {
                        $query = $this->getEntityManager()->createQuery("
                            SELECT COUNT(a.pkAnalyteStudy)
                            FROM Alae\Entity\AnalyteStudy a
                            WHERE a.fkAnalyte = " . $Analyte->getPkAnalyte() . " OR a.fkAnalyteIs = " . $Analyte->getPkAnalyte());
                        $counter = $query->getSingleScalarResult();
                        //VERIFICA QUE EL ANALITO NO ESTÉ ASOCIADO A UN ESTUDIO
                        if($counter > 0)
                        {
                            $error .= sprintf('<li>El analito %s está asociado a un estudio<li>', $value);
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

                                //INGRESO A AUDIT TRANSACTION
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

        //MOSTRAR LOS DATOS
        foreach ($elements as $analyte)
        {
            $query = $this->getEntityManager()->createQuery("
                SELECT COUNT(a.pkAnalyteStudy)
                FROM Alae\Entity\AnalyteStudy a
                WHERE a.fkAnalyte = " . $analyte->getPkAnalyte() . " OR a.fkAnalyteIs = " . $analyte->getPkAnalyte());
            $counter = $query->getSingleScalarResult();

            $buttons = "";
            if($counter == 0)
            {
                if($this->_getSession()->isSustancias() || $this->_getSession()->isAdministrador())
                {
                    $buttons .= '<span class="form-datatable-change" onclick="changeElement(this, ' . $analyte->getPkAnalyte() . ');"></span>';
                }
                if($this->_getSession()->isAdministrador())
                {
                    $buttons .= '<span class="form-datatable-delete" onclick="removeElement(this, ' . $analyte->getPkAnalyte() . ');"></span>';
                }
            }

            $data[] = array(
                "id"        => str_pad($analyte->getPkAnalyte(), 4, '0', STR_PAD_LEFT),
                "name"      => $analyte->getName(),
                "shortname" => $analyte->getShortening(),
                "edit"      => $buttons
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_ANALYTE, $this->_getSession()->getFkProfile()->getName());
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
                $query = $this->getEntityManager()->createQuery("
                    SELECT COUNT(a.pkAnalyteStudy)
                    FROM Alae\Entity\AnalyteStudy a
                    WHERE a.fkAnalyte = " . $Analyte->getPkAnalyte() . " OR a.fkAnalyteIs = " . $Analyte->getPkAnalyte());
                $counter = $query->getSingleScalarResult();

                if ($counter == 0)
                {
                    try
                    {
                        //ELIMINA EL ANALITO SI EXISTE
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
}
