<?php

namespace Alae;

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

