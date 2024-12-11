<?php
// File Downloader
// Date: 04/22/2019

$UserEmployeeCode  = $APP_SESSION->getEmployeeCode();
$type              = @$_GET['_p1'];
$filename          = @$_GET['_p2'];
$cs                = @$_GET['_p3'];
if (!Crypto::DTValidateChecksum("$UserEmployeeCode;$type;$filename", $cs)) {
    echo HTML::windowclose();
    return;
}


$payperiodcode = '';
$sss           = '';
$tin           = '';
$phic          = '';
$pagibig       = '';
$lastname      = '';
$employeeinfo = $APP_DBCONNECTION->execute(APP_DB_DATABASEPAYROLL . "..Usp_AP_GetEmployeeInfo '$UserEmployeeCode', '$payperiodcode'");
if (is_array($employeeinfo) && count($employeeinfo)) {
    $employeeinfo = $employeeinfo[0];
    $sss          = @$employeeinfo['SSS_No'];
    $tin          = str_replace('-','',@$employeeinfo['TIN']) . '000';
    $phic         = @$employeeinfo['Philhealth'];
    $pagibig      = @$employeeinfo['PagIBIG_No'];
    $lastname     = @$employeeinfo['Lastname'];    
}

if ($type == '2316') {
    $year = $filename;
    $filename = "{$lastname}_2316_1231$year.pdf";    
    $path_2316 = '../' . str_ireplace(APP_TITLE, 'ePayslip', APP_DIR . '/_files/_2316/'); //K_PATH_2316;
    $sourcefile = $path_2316 . "_{$year}/{$tin}_{$year}.pdf";

}
if (!is_readable($sourcefile)) {
    echo HTML::windowclose();             
    return;
}

header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . @filesize($sourcefile));
header('Accept-Ranges: bytes');
    
ob_clean(); 
flush(); 
@readfile($sourcefile);      
DATA::savelog($UserEmployeeCode, 'DF', $year, $type);

?>


   

