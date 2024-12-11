<?php
// APPLICATION FORM VIEWER
// Date: 06/22/2020

$UserEmployeeCode  = $APP_SESSION->getEmployeeCode();
$ApplicationNumber = @$_GET['_p1'] + 0;
$cs = @$_GET['_p2'];
if (!Crypto::ValidateGenericChecksum("$ApplicationNumber", $cs)) {
    echo HTML::windowclose();
    return;
}
$db = $APP_DBCONNECTION;


$form = '';
$webreferencenumber = $ApplicationNumber;
$sql = APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplication $webreferencenumber";

// -------------------------------------------------------------- 
// START
// FROM OnlineAdmission/printhout.php (after db connection check)
// DO NOT EDIT
// -------------------------------------------------------------- 
$records = $db->execute($sql);
if (!is_array($records))
{
    header("Location: main.html");
    exit();
    return;
}

if (count($records) < 1)
{
    header("Location: main.html");
    exit();
    return;
}
$record = $records[0];

$file = '';
if ($record['Class'] == 'T')
    $file = 'transferee';      

$applicanttype = '';
if ($record['College'] == 'S')
    $applicanttype = 'seniorhigh';
elseif ($record['College'] == 'L')
    $applicanttype = 'law';

if ($record['PhilsatDate'] == null)
    $record['PhilsatDate'] = 'n/a';
if ($record['PhilsatScore'] == '')
    $record['PhilsatScore'] = 'n/a';

    
if ($form == "RS")
    $template = @file_get_contents("ap_template/referenceslip$file$applicanttype.csn");
else {
    $template = @file_get_contents("ap_template/applicationform$file$applicanttype.csn");
}
if ($template == '')
    $template = 'FORM NOT YET AVAILABLE!';

if (!$record['WithDisability'])
    $record['Disability'] = 'NO';
else
    $record['Disability'] = 'YES, ' . $record['Disability'];
    
if ($record['ApplyingAsScholar'])    
    $record['ApplyingAsScholar'] = 'Yes';
else
    $record['ApplyingAsScholar'] = 'No';
    
foreach ($record as $key => $value)
{
    $htmlkey = "_" . strtoupper($key) . "_";
    if ((array_search($key, array("Appl_Date", "ExaminationDate","BirthDate")) !== false) &&
        ($value != null))
        $value = date("m/d/Y", strtotime($value));
    if (($key == "ApplicationNumber") and ($value == 0))
        $value = "";
    $template = str_replace($htmlkey, utf8_encode($value), $template);
}

$template = str_replace("_WEBREFERENCENUMBER_", $webreferencenumber, $template);
// END OF printout.php
// ----------------------------------------------

$html = $template;
$html = str_replace('img/',APP_BASE . 'ap_img/',$html);
$src = getfilesrc(2011, $webreferencenumber, 2011, 0, 0);
include_once('ap_template/ajaxheader.php'); 
echo $html;

$footerscript = '$(document).ready(function () {';
$footerscript .= "\$('#panelPicture1,#panelPicture2').html('<img src=\"$src\">');";
$footerscript .= '});';
?> 
<link rel="stylesheet" href="<?php echo APP_BASE ?>ap_css/applicationform.css">
<style>
#panelPicture1 img, #panelPicture2 img {
    width: 100%;
    height: 100%;
    padding: 0px;
    margin: 0px;
    border: 1px solid #000000;
}
</style>
<?php
include_once('ap_template/ajaxfooter.php');  
return;

function getfilesrc($ImageTypeID, $WebReferenceNumber, $RecordID, $localfile=false, $temporary=false) {
    if ($localfile)
        return APP_BASE . 'ap_img/blank.png';
    $ImageTypeID += 0;
    $WebReferenceNumber += 0;
    $RecordID += 0;
    $localfile = $localfile ? 1 : 0;
    $random = mt_rand();
    $temporary = $temporary ? 1 : 0;
    $cs = Crypto::GetFileChecksum($ImageTypeID, $WebReferenceNumber, $RecordID, $localfile, $random);
    $itid = Tools::base64_encodeNOEQ($ImageTypeID);
    $wrn = Tools::base64_encodeNOEQ($WebReferenceNumber);
    $rid = Tools::base64_encodeNOEQ($RecordID);   
    return APP_BASE . "getfileimage/$random/$itid/$wrn/$rid/$localfile/$cs/$temporary/plain";
}
?>