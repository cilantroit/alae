<?php

$filename = sprintf('%s.xls', rawurlencode(preg_replace('~&#(\d{3,8});~e', '$fixchar(\'$1\')', $_REQUEST['name'])));
header('Content-Disposition: attachment; filename="'.$filename.'";' );
header('Content-Type: application/vnd.ms-excel;base64');
echo($_REQUEST['data']);

