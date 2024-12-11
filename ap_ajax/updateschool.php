<?php
$UserEmployeeCode = $APP_SESSION->getuserid();
$schoolid = trim(@$_POST['i']) + 0;
$applicationnumber = @$_POST['a'] + 0;
$webreferencenumber = @$_POST['r'] + 0;
$schoolname = trim(@$_POST['n']);
$sn = trim(@$_POST['s']);
$cs = @$_POST['c'];

$schooltype = '';
$i = strpos($schoolname,':');
if ($i !== false) {
    $schooltype = substr($schoolname,0, $i);
    $schoolname = strtoupper(substr($schoolname, $i+1));
}

if (Crypto::GenericChecksum('$schoolid;$applicationnumber;sardinas;ulam;ko;$webreferencenumber') != $cs) {
    echo 'Invalid parameters!';
    return;
}

if (($schoolid == 0) && ($schoolname == '')) {
    echo html::alert('Error', 'Please provide School Name!');
    return;
}


$sql = APP_DB_DATABASEADMISSION . "..Usp_OA_UpdateSchool $applicationnumber, $schoolid, '$UserEmployeeCode', $webreferencenumber, '$schoolname', '$schooltype', '$sn'";
$results = $APP_DBCONNECTION->execute($sql);
if (Tools::emptydataset($results)) {
    echo html::alert('Error', 'Unable to update school id!');
    if (!APP_PRODUCTION) {
        echo "<pre>SQL: $sql<br>", print_r($results,true), '</pre>';
    }
    return;
}
$record = $results[0];
$errormessage = @trim($record['ErrorMessage']);
if ($errormessage) {
    echo html::alert('Error', $errormessage);
    return;
}

echo "<script>location.reload()</script>";
?>