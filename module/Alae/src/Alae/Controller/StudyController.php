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

class StudyController extends BaseController
{

    protected $_document = '\\Alae\\Entity\\Study';

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
        $data     = array();
        $elements = $this->getRepository()->findBy(array("status" => true));

        foreach ($elements as $study)
        {
            $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
            $data[]         = array(
                "code"        => $study->getCode(),
                "description" => $study->getDescription(),
                "date"        => $study->getCreatedAt(),
                "analyte"     => $counterAnalyte,
                "observation" => $study->getObservation(),
                "closed"      => $study->getCloseFlag() ? "S" : "N",
                "edit"        => $study->getPkStudy()
            );
        }

        $datatable = new Datatable($data, Datatable::DATATABLE_STUDY);
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('user', $this->_getSession());
        return $viewModel;
    }

    public function createAction()
    {
        $request = $this->getRequest();
        $viewModel = new ViewModel();

        if ($request->isPost())
        {
            $User = $this->_getSession();
            $elements = $this->getRepository()->findBy(array("code" => $request->getPost('code')));

            if(count($elements) > 0)
            {
                $viewModel->setVariable('error', "<li>Este estudio ya existe. Intente con otro código, por favor<li>");
            }
            else
            {
                /*
                 * Creación de los datos básicos del estudio
                 */
                try
                {
                    $Study = new \Alae\Entity\Study();
                    $Study->setCode($request->getPost('code'));
                    $Study->setDescription($request->getPost('description'));
                    $Study->setCreatedAt(new \DateTime('now'));
                    $Study->setObservation($request->getPost('observation'));
                    $Study->setFkDilutionTree($request->getPost('dilution_tree'));
                    $Study->setStatus(true);
                    $Study->setCloseFlag(false);
                    $Study->setApprove(false);
                    $Study->setDuplicate(false);
                    $Study->setFkUser($User);
                    $this->getEntityManager()->persist($Study);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Creación de estudio",
                        sprintf('El usuario %1$s ha creado el estudio %2$s - Código: %2$s, Descripción: %3$s, Observaciones: %4$s',
                            $User->getUsername(),
                            $Study->getCode(),
                            $Study->getDescription(),
                            $Study->getObservation()
                        ),
                        false
                    );
                    return $this->redirect()->toRoute('study', array(
                        'controller' => 'study',
                        'action'     => 'edit',
                        'id'         => $Study->getPkStudy()
                    ));
                }
                catch (Exception $e)
                {
                    exit;
                }
            }
        }


        $viewModel->setVariable('user', $this->_getSession());
        return $viewModel;
    }

    public function deleteanastudyAction()
    {
        $request = $this->getRequest();

        if ($request->isGet())
        {
            $AnaStudy = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->find($request->getQuery('pk'));
            if ($AnaStudy && $AnaStudy->getPkAnalyteStudy())
            {
                try
                {
                    $this->getEntityManager()->remove($AnaStudy);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Eliminar de analito en estudio",
                        sprintf('El usuario %1$s ha eliminado el analito %2$s del estudio %3$s',
                            $this->_getSession()->getUsername(),
                            $AnaStudy->getFkAnalyte()->getShortening(),
                            $AnaStudy->getFkStudy()->getCode()
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

    public function editAction()
    {
        $request = $this->getRequest();

        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $Study = $this->getRepository()->find($this->getEvent()->getRouteMatch()->getParam('id'));
        }

        if ($request->isPost())
        {
            $User  = $this->_getSession();
            $Study = $this->getRepository()->find($request->getPost('study_id'));

            /*
             * Creación de los datos básicos del estudio
             */
            if ($request->getPost('form') == 1)
            {
                try
                {
                    $older = sprintf('Valores antes del cambio -> Usuario: %1$s Código: %2$s, Descripción: %3$s, Observaciones: %4$s',
                        $Study->getFkUser()->getUsername(),
                        $Study->getCode(),
                        $Study->getDescription(),
                        $Study->getObservation()
                    );
                    $Study->setDescription($request->getPost('description'));
                    $Study->setObservation($request->getPost('observation'));
                    $Study->setFkDilutionTree($request->getPost('dilution_tree'));
                    $Study->setFkUser($User);
                    $this->getEntityManager()->persist($Study);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Edición de estudios",
                        sprintf('El usuario %1$s ha editado el estudio %2$s <br> %3$s <br> Valores nuevos -> Código: %2$s, Descripción: %4$s, Observaciones: %5$s',
                            $User->getUsername(),
                            $request->getPost('code'),
                            $older,
                            $request->getPost('description'),
                            $request->getPost('observation')
                        ),
                        false
                    );
                }
                catch (Exception $e)
                {
                    exit;
                }

            }

            /*
             * Asociaciones de analitos y ediciones de los existentes
             */
            if ($request->getPost('form') == 2)
            {
                $createAnalyte   = $request->getPost('create-analyte');
                $createAnalyteIs = $request->getPost('create-analyte_is');
                $createCsNumber  = $request->getPost('create-cs_number');
                $createQcNumber  = $request->getPost('create-qc_number');
                $createUnit      = $request->getPost('create-unit');
                $createIs        = $request->getPost('create-is');
                $createUse       = $request->getPost('create-use');
                $updateCsNumber  = $request->getPost('update-cs_number');
                $updateQcNumber  = $request->getPost('update-qc_number');
                $updateIs        = $request->getPost('update-is');
                $updateUse       = $request->getPost('update-use');

                if (!empty($createAnalyte))
                {
                    foreach ($createAnalyte as $key => $value)
                    {
                        $Analyte   = $this->getRepository('\\Alae\\Entity\\Analyte')->find($value);
                        $AnalyteIs = $this->getRepository('\\Alae\\Entity\\Analyte')->find($createAnalyteIs[$key]);
                        $Unit      = $this->getRepository('\\Alae\\Entity\\Unit')->find($createUnit[$key]);

                        try
                        {
                            $AnaStudy = new \Alae\Entity\AnalyteStudy();
                            $AnaStudy->setFkAnalyte($Analyte);
                            $AnaStudy->setFkAnalyteIs($AnalyteIs);
                            $AnaStudy->setFkStudy($Study);
                            $AnaStudy->setCsNumber($createCsNumber[$key]);
                            $AnaStudy->setQcNumber($createQcNumber[$key]);
                            $AnaStudy->setFkUnit($Unit);
                            $AnaStudy->setInternalStandard($createIs[$key]);
                            $AnaStudy->setStatus(false);
                            $AnaStudy->setIsUsed((isset($createUse[$key]) ? true : false));
                            $AnaStudy->setFkUser($User);
                            $this->getEntityManager()->persist($AnaStudy);
                            $this->getEntityManager()->flush();
                            $this->transaction(
                                "Asociar analitos a estudio",
                                sprintf('El usuario %1$s ha agrega el analito %2$s(%3$s) al estudio %4$s.<br>Patrón Interno (IS): %5$s, Núm CS: %6$s, Núm QC: %7$s, Unidades: %8$s, % var IS: %9$s, usar: %10$s',
                                    $User->getUsername(),
                                    $Analyte->getName(),
                                    $Analyte->getShortening(),
                                    $Study->getCode(),
                                    $AnalyteIs->getName(),
                                    $createCsNumber[$key],
                                    $createQcNumber[$key],
                                    $Unit->getName(),
                                    $createIs[$key],
                                    (isset($createUse[$key]) ? "S" : "N")
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

                if (!empty($updateCsNumber))
                {
                    foreach ($updateCsNumber as $key => $value)
                    {
                        $AnaStudy = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->find($key);

                        if ($AnaStudy && $AnaStudy->getPkAnalyteStudy())
                        {
                            try
                            {
                                $older =  sprintf('Valores antiguos -> Núm CS: %1$s, Núm QC: %2$s, % var IS: %3$s, usar: %4$s<br>',
                                    $AnaStudy->getCsNumber(),
                                    $AnaStudy->getQcNumber(),
                                    $AnaStudy->getInternalStandard(),
                                    ($AnaStudy->getIsUsed() ? "S" : "N")
                                );
                                $AnaStudy->setCsNumber($updateCsNumber[$key]);
                                $AnaStudy->setQcNumber($updateQcNumber[$key]);
                                $AnaStudy->setInternalStandard($updateIs[$key]);
                                $AnaStudy->setIsUsed(isset($updateUse[$key]) ? true : false);
                                $this->getEntityManager()->persist($AnaStudy);
                                $this->getEntityManager()->flush();
                                $this->transaction(
                                    "Edición de analitos asociados a estudio",
                                    sprintf('El usuario %1$s ha editado la información del analito %2$s(%3$s) en el estudio %4$s.<br>%5$s'
                                            . 'Valores nuevos -> Núm CS: %6$s, Núm QC: %7$s, % var IS: %8$s, usar: %9$s',
                                        $User->getUsername(),
                                        $AnaStudy->getFkAnalyte()->getName(),
                                        $AnaStudy->getFkAnalyte()->getShortening(),
                                        $Study->getCode(),
                                        $older,
                                        $updateCsNumber[$key],
                                        $updateQcNumber[$key],
                                        $updateIs[$key],
                                        (isset($updateUse[$key]) ? "S" : "N")
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
        $elements = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->findBy(array("fkStudy" => $Study->getPkStudy()));

        foreach ($elements as $anaStudy)
        {
            $data[] = array(
                "analyte"    => $anaStudy->getFkAnalyte()->getShortening(),
                "analyte_is" => $anaStudy->getFkAnalyteIs()->getShortening(),
                "cs_number"  => $anaStudy->getCsNumber(),
                "qc_number"  => $anaStudy->getQcNumber(),
                "unit"       => $anaStudy->getFkUnit()->getName(),
                "is"         => $anaStudy->getInternalStandard(),
                "use"        => $anaStudy->getIsUsed(),
                "edit"       => $anaStudy->getPkAnalyteStudy()
            );
        }

        $Analyte   = $this->getRepository('\\Alae\\Entity\\Analyte')->findAll();
        $Unit      = $this->getRepository('\\Alae\\Entity\\Unit')->findAll();
        $datatable = new Datatable($data, Datatable::DATATABLE_ANASTUDY);
        $viewModel = new ViewModel($datatable->getDatatable());
        $viewModel->setVariable('study', $Study);
        $viewModel->setVariable('error', (isset($error) ? $error : ""));
        $viewModel->setVariable('analytes', $Analyte);
        $viewModel->setVariable('units', $Unit);
        $viewModel->setVariable('user', $this->_getSession());
        $viewModel->setVariable('disabled', (($this->_getSession()->isAdministrador() && !$Study->getCloseFlag()) ? "" : "disabled"));
        return $viewModel;
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        if ($request->isGet())
        {
            $Study = $this->getRepository()->find($request->getQuery('pk'));
            if ($Study && $Study->getPkStudy())
            {
                try
                {
                    $User = $this->_getSession();
                    $Study->setStatus(false);
                    $Study->setFkUser($User);
                    $this->getEntityManager()->persist($Study);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Eliminar estudio",
                        sprintf('El usuario %1$s ha eliminado el estudio %2$s',
                            $User->getUsername(),
                            $Study->getCode()
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

    protected function download()
    {
        $data     = array();
        $data[]   = array("Código", "Descripción", "Fecha", "Nº Analitos", "Observaciones", "Cerrado (S/N)");
        $elements = $this->getRepository()->findAll();

        foreach ($elements as $study)
        {
            $counterAnalyte = $this->counterAnalyte($study->getPkStudy());
            $data[]         = array(
                $study->getCode(),
                $study->getDescription(),
                $study->getCreatedAt(),
                $counterAnalyte,
                $study->getObservation(),
                $study->getCloseFlag() ? "S" : "N"
            );
        }

        return json_encode($data);
    }

    public function excelAction()
    {
        \Alae\Service\Download::excel("listado_de_estudios", $this->download());
    }

    public function approveAction()
    {
        $request = $this->getRequest();

	if ($request->isGet() && ($this->_getSession()->isAdministrador() || $this->_getSession()->isDirectorEstudio()))
	{
            $Study = $this->getRepository('\\Alae\\Entity\\Study')->find($request->getQuery('id'));

	    if ($Study && $Study->getPkStudy())
            {
                try
                {
                    $User = $this->_getSession();
                    $Study->setApprove(true);
                    $Study->setFkUser($User);
                    $this->getEntityManager()->persist($Study);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Aprobar estudio",
                        sprintf('El usuario %1$s ha aprobado el estudio %2$s',
                            $User->getUsername(),
                            $Study->getCode()
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

    public function closeAction()
    {
        $request = $this->getRequest();

	if ($request->isGet() && ($this->_getSession()->isAdministrador() || $this->_getSession()->isDirectorEstudio()))
	{
            $Study = $this->getRepository('\\Alae\\Entity\\Study')->find($request->getQuery('id'));

	    if ($Study && $Study->getPkStudy())
            {
                try
                {
                    $User = $this->_getSession();
                    $Study->setCloseFlag(true);
                    $Study->setFkUser($User);
                    $this->getEntityManager()->persist($Study);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Cerrar estudio",
                        sprintf("El estudio %s ha sido cerrado por el usuario %s ", $Study->getCode(), $User->getUsername()),
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

    public function duplicateAction()
    {
        if ($this->getEvent()->getRouteMatch()->getParam('id') && $this->_getSession()->isAdministrador())
	{
            $Study = $this->getRepository('\\Alae\\Entity\\Study')->find($this->getEvent()->getRouteMatch()->getParam('id'));

	    if ($Study && $Study->getPkStudy())
            {
                try
                {
                    $User = $this->_getSession();
                    $qb = $this->getEntityManager()->getRepository("\\Alae\\Entity\\Study")->createQueryBuilder('s')
                            ->where('s.code like :code')
                            ->setParameter('code', '%' . $Study->getCode() . '%');
                    $studies = $qb->getQuery()->getResult();

                    $newStudy = new \Alae\Entity\Study();
                    $newStudy->setDescription($Study->getDescription());
                    $newStudy->setObservation($Study->getObservation());
                    $newStudy->setCode($Study->getCode(). count($studies));
                    $newStudy->setCloseFlag(false);
                    $newStudy->setStatus(true);
                    $newStudy->setApprove(false);
                    $newStudy->setDuplicate(true);
                    $newStudy->setCreatedAt(new \DateTime('now'));
                    $newStudy->setFkUser($User);
                    $this->getEntityManager()->persist($newStudy);
                    $this->getEntityManager()->flush();

                    $elements = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->findBy(array("fkStudy" => $Study->getPkStudy()));
                    foreach ($elements as $AnaStudy)
                    {
                        $newAnaStudy = new \Alae\Entity\AnalyteStudy();
                        $newAnaStudy->setFkAnalyte($AnaStudy->getFkAnalyte());
                        $newAnaStudy->setFkAnalyteIs($AnaStudy->getFkAnalyteIs());
                        $newAnaStudy->setFkStudy($newStudy);
                        $newAnaStudy->setCsNumber($AnaStudy->getCsNumber());
                        $newAnaStudy->setQcNumber($AnaStudy->getQcNumber());
                        $newAnaStudy->setCsValues($AnaStudy->getCsValues());
                        $newAnaStudy->setQcValues($AnaStudy->getQcValues());
                        $newAnaStudy->setFkUnit($AnaStudy->getFkUnit());
                        $newAnaStudy->setInternalStandard($AnaStudy->getInternalStandard());
                        $newAnaStudy->setStatus(false);
                        $newAnaStudy->setIsUsed($AnaStudy->getIsUsed());
                        $newAnaStudy->setFkUser($User);
                        $this->getEntityManager()->persist($newAnaStudy);
                        $this->getEntityManager()->flush();
                    }

                    $this->transaction(
                        "Duplicar estudio",
                        sprintf('El usuario %1$s ha duplicado el estudio %2$s.<br>',
                            $User->getUsername(),
                            $Study->getCode()
                        ),
                        false
                    );

                    return $this->redirect()->toRoute('study', array(
                        'controller' => 'study',
                        'action'     => 'index'
                    ));
                }
                catch (Exception $e)
                {
                    exit;
                }
            }
	}
        else{
            $this->back($this->getEvent()->getRouteMatch()->getParam('id'));
        }
    }

    protected function back($pkStudy)
    {
        return $this->redirect()->toRoute('study', array(
            'controller' => 'study',
            'action'     => 'edit',
            'id'         => $pkStudy
        ));
    }

    /**
     * Approve: Nominal Concentration
     */
    public function approvencAction()
    {
        $request = $this->getRequest();

	if ($request->isGet() && ($this->_getSession()->isAdministrador() || $this->_getSession()->isDirectorEstudio()))
	{
            $AnaStudy = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->find($request->getQuery('id'));

	    if ($AnaStudy && $AnaStudy->getPkAnalyteStudy())
            {
                try
                {
                    $User = $this->_getSession();
                    $AnaStudy->setStatus(true);
                    $AnaStudy->setFkUser($User);
                    $this->getEntityManager()->persist($AnaStudy);
                    $this->getEntityManager()->flush();
                    $this->transaction(
                        "Aprobación de concentraciones nominales",
                        sprintf('El usuario %1$s ha aprobado las concentraciones nominales del estudio %2$s<br>'
                                . 'Concentración Nominal de los Estándares de Calibración: %3$s<br>'
                                . 'Concentración Nominal de los Controles de Calidad: %4$s',
                            $User->getUsername(),
                            $AnaStudy->getFkStudy()->getCode(),
                            $AnaStudy->getCsValues(),
                            $AnaStudy->getQcValues()
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

    public function unlockAction()
    {
        $request = $this->getRequest();

	if ($request->isGet() && $this->_getSession()->isAdministrador())
	{
            $AnaStudy = $this->getRepository('\\Alae\\Entity\\AnalyteStudy')->find($request->getQuery('id'));

	    if ($AnaStudy && $AnaStudy->getPkAnalyteStudy())
            {
                try
                {
                    $User = $this->_getSession();
                    $AnaStudy->setStatus(false);
                    $AnaStudy->setFkUser($User);
                    $this->getEntityManager()->persist($AnaStudy);
                    $this->getEntityManager()->flush();

                    $this->transaction(
                        "Desbloquear concentraciones nominales",
                        sprintf('El usuario %1$s ha desbloqueado las concentraciones nominales del estudio %2$s<br>'
                                . 'Analito: %3$s<br>'
                                . 'Concentración Nominal de los Estándares de Calibración: %4$s<br>'
                                . 'Concentración Nominal de los Controles de Calidad: %5$s',
                            $this->_getSession()->getUsername(),
                            $AnaStudy->getFkStudy()->getCode(),
                            $AnaStudy->getFkAnalyte()->getName(),
                            $AnaStudy->getCsValues(),
                            $AnaStudy->getQcValues()
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

    public function nominalconcentrationAction()
    {
        $request = $this->getRequest();

        if ($this->getEvent()->getRouteMatch()->getParam('id'))
        {
            $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->find($this->getEvent()->getRouteMatch()->getParam('id'));
        }

        if ($request->isPost())
        {
            $AnaStudy = $this->getRepository("\\Alae\\Entity\\AnalyteStudy")->find($request->getPost('id'));
            $AnaStudy->setCsValues(implode(",", $request->getPost("cs_number")));
            $AnaStudy->setQcValues(implode(",", $request->getPost("qc_number")));
            $this->getEntityManager()->persist($AnaStudy);
            $this->getEntityManager()->flush();
            $this->transaction(
                "Ingreso concentraciones nominales",
                sprintf('El usuario %1$s ha ingresado las concentraciones nominales del estudio %2$s<br>'
                        . 'Analito: %3$s<br>'
                        . 'Concentración Nominal de los Estándares de Calibración: %4$s<br>'
                        . 'Concentración Nominal de los Controles de Calidad: %5$s',
                    $this->_getSession()->getUsername(),
                    $AnaStudy->getFkStudy()->getCode(),
                    $AnaStudy->getFkAnalyte()->getName(),
                    implode(",", $request->getPost("cs_number")),
                    implode(",", $request->getPost("qc_number"))
                ),
                false
            );
        }

        $viewModel = new ViewModel();
        $viewModel->setVariable('AnaStudy', $AnaStudy);
        $viewModel->setVariable('cs_number', explode(",", $AnaStudy->getCsValues()));
        $viewModel->setVariable('qc_number', explode(",", $AnaStudy->getQcValues()));
        $viewModel->setVariable('User', $this->_getSession());
        $viewModel->setVariable('disabled', (!$AnaStudy->getStatus() && ($this->_getSession()->isAdministrador() || $this->_getSession()->isDirectorEstudio()) ? "" : "disabled"));
        return $viewModel;
    }

    protected function counterAnalyte($pkStudy)
    {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(a.fkAnalyte)
            FROM \Alae\Entity\AnalyteStudy a
            WHERE a.fkStudy = " . $pkStudy . "
            GROUP BY a.fkStudy");
        $response = $query->execute();
        return $response ? $query->getSingleScalarResult() : 0;
    }
}