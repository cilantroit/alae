<?php

/**
 * Descarga de archivos excel y pdf
 *
 * @author Maria Quiroz
 * Fecha de creación: 15/05/2014
 */

namespace Alae\Service;

require_once "vendor/autoload.php";

class Download
{
    /*
     * Descarga de archivos excel
     */
    public static function excel($filename, $json)
    {
        $data = json_decode($json);
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getActiveSheet()->fromArray($data);
        //HEADER DE EXCEL
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /*
     * Descarga de archivos pdf
     */
    public static function pdf($filename, $json)
    {
        $rendererName        = \PHPExcel_Settings::PDF_RENDERER_DOMPDF;
        $rendererLibrary     = 'dompdf';
        $rendererLibraryPath = $_SERVER['DOCUMENT_ROOT'] . '/alae/vendor/codeplex/phpexcel/PHPExcel/Writer/' . $rendererLibrary;
        $data = json_decode($json);
        //HEADER PDF
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getActiveSheet()->fromArray($data);
        $objPHPExcel->getActiveSheet()->setShowGridLines(false);
        if (!\PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath))
        {
            die(
                    'NOTICE: Please set the ' . $rendererName . ' and ' . $rendererLibraryPath . ' values' .
                    EOL .
                    'at the top of this script as appropriate for your diectory structure'
            );
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
        $objWriter->save('php://output');
        exit;
    }
}