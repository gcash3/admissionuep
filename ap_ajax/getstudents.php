<?php
$results = Data::getstudents(@$_POST['st']);
$array = array();
if (!Tools::emptydataset($results)) {
	foreach ($results as $record) {
		$array[] = array('id' => $record['SN'], 
		                 'text' => $record['Name'],
						 'College' => $record['College'],
						 'Course' => $record['Course'],
						 'CampusCode' => $record['CampusCode']);
	}
}
echo json_encode($array);
?>