
<?php
/* APLICACION ALAE
   Este fichero se encarga de configurar los mensajes de error que genera ALAE en la carga de lotes
   Autor: María Quiroz
   Fecha de creación: 10/05/2014
*/
namespace Alae;
//MENSAJES DE ERROR
return array(
    'Invalid_file_name_in_the_export_process_batches_of_analytes' => array(
        'section' => 'Verificacion de ficheros de exportación de lotes analitos',
        'description' => 'El fichero %s no cumple con la estructura de nombre permitido por la ALAE.',
        'message' => 'V1 – EXPORT ERRÓNEO'
    ),
    'The_lot_is_not_associated_with_a_registered_study' => array(
        'section' => 'Verificacion de ficheros de exportación de lotes analitos',
        'description' => 'El lote %s no esta asociado a ningún estudio de ALAE.',
        'message' => 'V1 – EXPORT ERRÓNEO'
    ),
    'The_analyte_is_not_associated_with_the_study' => array(
        'section' => 'Verificacion de ficheros de exportación de lotes analitos',
        'description' => 'El analito %s no esta asociado a ningún estudio de ALAE.',
        'message' => 'V2 – ANALITO ERRÓNEO'
    ),
    'Repeated batch' => array(
        'section' => 'Verificacion de ficheros de exportación de lotes analitos',
        'description' => 'El lote %s ya se encuentra registrado en ALAE',
        'message' => 'V1 – EXPORT ERRÓNEO'
    ),
);
?>

