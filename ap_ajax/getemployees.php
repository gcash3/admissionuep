<?php
$UserEmployeeCode = $APP_SESSION->getuserid();
$searchtext = @$_POST['st'];
$sql = "Usp_AP_FindEmployees '$UserEmployeeCode', '$searchtext', 0";
$results = $APP_DBCONNECTION->execute($sql);

$array = array();

if (!Tools::emptydataset($results)) {
	foreach ($results as $record) {
		$array[] = array('id' => $record['EmployeeCode'], 'text' => $record['NameEmployeeCode'] );
	}
}
echo json_encode($array);
?>