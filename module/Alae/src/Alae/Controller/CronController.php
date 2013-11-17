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
    Alae\Service\Helper as Helper;

class CronController extends BaseController
{

    public function readAction()
    {
        $files = scandir(Helper::getVarsConfig("batch_directory"), 1);

        foreach ($files as $file)
        {
            if (preg_match("/^(\d{2}-\d{4}\_[a-zA-Z0-9]+\.txt)$/i", $file))
            {
                echo "Se encontro una coincidencia: $file\n";
            }
            else
            {
                $error = Helper::getError('Invalid_file_name_in_the_export_process_batches_of_analytes');
                $this->transactionError(sprintf($error['description'], $file), $error['message'], $error['section'], true);
            }
        }
    }

}

?>
