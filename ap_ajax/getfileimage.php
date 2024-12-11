<?php
/*
$EmployeeCode   = $APP_SESSION->getEmployeeCode();
$filenameserver = base64_decode($_GET['_p1']);
$filenameactual = base64_decode($_GET['_p2']);
$cs             = $_GET['_p3'];

$mimetype = Tools::getMimeType($filenameactual, true);

if (!Crypto::DLValidateChecksum($filenameserver, $filenameactual, $cs)) {
    echo HTML::windowclose();
    return;
}
$filenameserver = APP_UPLOADDIR . $filenameserver;
if (!file_exists($filenameserver)) {
    echo HTML::windowclose();
    return;
}
ob_clean();
//$mimetype = @mime_content_type($filenameserver);
header("Content-Type: $mimetype");
header("Content-Disposition: inline; filename='$filenameactual'");
header("Cache-Control: no-cache, must-revalidate");
echo @file_get_contents($filenameserver);
*/


 
//r=$random&itid=$itid&wrn=$wrn&rid=$rid&l=$localfile&cs=$cs

// return APP_BASE . "getfileimage/$random/$itid/$wrn/$rid/$localfile/$cs/$temporary/plain";
$random             = @$_GET['_p1']; 
$ImageTypeID        = @$_GET['_p2'];
$WebReferenceNumber = @$_GET['_p3'];
$RecordID           = @$_GET['_p4'];
$localfile          = @$_GET['_p5'];
$cs                 = @$_GET['_p6'];
$temporary          = @$_GET['_p7'] ? 1 : 0;

$errormime = "Content-type: image/png";

if (($ImageTypeID == '') || ($cs == '')) {
    header($errormime); 
    return;
}


$ImageTypeID        = @base64_decode($ImageTypeID) + 0;
$WebReferenceNumber = @base64_decode($WebReferenceNumber) + 0;
$RecordID           = @base64_decode($RecordID) + 0;

if ($cs != Crypto::GetFileChecksum($ImageTypeID, $WebReferenceNumber, $RecordID, $localfile, $random)) {
    header($errormime);
    return;
}



    if ($temporary)
        $WebReferenceNumber = '0';
    $SessionID = $WebReferenceNumber ? '' : session_id();
    $sql = APP_DB_DATABASEADMISSION  . "..Usp_OA_GetFile $WebReferenceNumber, $ImageTypeID, '$SessionID'";
    $records = $APP_DBCONNECTION->execute($sql);
    if (is_array($records) && count($records)) {
        $image = '';
        $mime = Tools::getMimeType($records[0]['Extension']);
        foreach ($records as $record) {
            $image .= base64_decode($record['Image']);
        }
        header("Content-type: $mime"); 
        echo $image;
        return;
    }


header($errormime);
return;



?>



?>
