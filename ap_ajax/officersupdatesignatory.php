<?php
$UserEmployeeCode = $APP_SESSION->getEmployeeCode(); 

$ec  = $_POST['ec'];
$dc  = $_POST['dc'];
$employeecode = $ec;

$sql = "Usp_AP_SaveOfficer '$dc', '$employeecode', '$UserEmployeeCode'";
$results = $APP_DBCONNECTION->execute($sql);
if (!Tools::emptydataset($results)) {
    $record = $results[0];
    $html = '<script>';
    $name = $record['Name'];
    $position = $record['Position'];
    $html .= "\$('#$dc').html('$name');";
    $html .= "\$('#P_$dc').html('$position');";
    $html .= '</script>';
}
echo $html;
?>