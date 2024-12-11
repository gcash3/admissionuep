<?php
// PEF : PEF Viewer
// Date: 06/15/2020

$UserEmployeeCode  = $APP_SESSION->getEmployeeCode();
$ApplicationNumber = @$_GET['_p1'] + 0;
$cs = @$_GET['_p2'];
if (!Crypto::ValidateGenericChecksum("$ApplicationNumber", $cs)) {
    echo HTML::windowclose();
    return;
}

$sql = APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicationbyApplicationNumber '$ApplicationNumber', '', '', '', 1"; 
$application = $APP_DBCONNECTION->execute($sql);


if (Tools::emptydataset($application)) {
    echo $sql;
    echo HTML::windowclose();
    return;    
}

$db = $APP_DBCONNECTION;
define('VERSION', APP_VERSION); 

$application = $application[0];
$results = array();
$results = getPEF($application, $results, '', 'ap_template');
$html = '';


foreach ($results as $result) {
    if ($result[0] == 'html') {
        $html = $result[1];
        break;
    }
}


$html = str_replace('img/',APP_BASE . 'ap_img/',$html);

include_once('ap_template/ajaxheader.php'); 
echo $html;
?>
<style>
@media print  {
    .btn, .noprint {
        display: none !important;
    }
}
.PEFBUTTONS {
    position: fixed;
    top: 10px;
    left: 10px;
}
.PEF td, .PEF th  {
    padding: 4px;
    border: 1px solid #999999;    
    border-collapse: collapse;
}

table.PEF, tr.PEF {
    border: 2px solid #111111 !important;
    border-collapse: collapse;
    width: 100%;
    margin-top: 10px;
    margin-bottom:10px;
}

.PEF tr td:last-child,
.PEF tr th:last-child   {
    border-right: 2px solid #111111 !important;     
}

.PEF tr th {
    border-bottom: 2px solid #111111 !important;  
</style>
<script>
function js_uploadlogin(p) {
    window.close();
}
</script>
<?php
include_once('ap_template/ajaxfooter.php');  
return;



// COPIED FROM OnlineAdmission
// EXACT COPIES. DO NOT EDIT
function getPEF($record=null, $result, $sql, $path='template') {
    global $db;
    if ($record === null)
        $record = $_SESSION;
        
    $semester = $record['Semester'];
    $sn       = $record['SN'];
        
    $sql = "Usp_ORSCSN_GetRGDetails '$semester', '$sn'";
    $subjects = $db->execute($sql);
    if (!is_array($subjects) || (count($subjects) == 0)) {
        $result[] = renderjsalert("Unable to retrieve subject list!");
        return $result;
    }  
    
    $showunits = stripos('KYHS',$record['College']) === false;
    
    $cols = trim($subjects[0]['SubjectCode_2']) <> '' ? 2 : 1;
    $details = "<table class='PEF PEF$cols'>"; 
    if ($cols == 2) {
        $details .= '<tr>';
        $details .= '<th colspan=7 class="text-center" style="width:50%">First Semester</th>';
        $details .= '<th colspan=7 class="text-center" style="width:50%">Second Semester</th>';
        $details .= '</tr>'; 
    }   
    $details .= '<tr>';
    for ($c=1;$c<=$cols;$c++) {
        $details .= '<th>Code</th>';
        $details .= '<th>Section</th>';
        $details .= '<th>Description</th>';
        if ($showunits)
            $details .= '<th class="text-center">Units</th>';
        $details .= '<th>Days</th>';
        $details .= '<th>Time</th>';
        $details .= '<th>Room</th>';
    }
    $details .= '</tr>';
    foreach ($subjects as $subject) {
        $details .= '<tr>';
        for ($c=1;$c<=$cols;$c++) {
            $units = trim($subject["Units_$c"]) ? $subject["Units_$c"] : '';
            $details .= '<td>' . $subject["SubjectCode_$c"] .'</td>';
            $details .= '<td>' . $subject["Section_$c"] .'</td>'; 
            $details .= '<td>' . utf8_encode($subject["Description_$c"]) .'</td>'; 
            if ($showunits)
                $details .= '<td class="text-center">' . $units .'</td>'; 
            $details .= '<td>' . $subject["Days_$c"] .'</td>'; 
            $details .= '<td>' . $subject["Time_$c"] .'</td>'; 
            $details .= '<td>' . $subject["Room_$c"] .'</td>'; 
        }
        $details .= '</tr>';
    }   
    $details .= '</table>';
        
    $print  = "<input type=button value='Print' class='btn btn-success' onclick='window.print()'> ";
    $cancel = "<input type=button value='Close' class='btn btn-default' onclick='js_uploadlogin(0)'> ";
    $html = '';        
    if (!$record['WithRegistration']) {
        $html .= @file_get_contents("$path/pefnotfound.csn"); 
        $html .= $cancel;
        $result[] = array("html", $html);   
        return $result;     
    }
    $html  = "_BUTTONS_<br>";
    $html .= @file_get_contents("$path/pef.csn");
    $date = date('m/d/Y h:i:s');
    $cs = substr(sha1("CSN;$date;OA"),0,16);  
    $html .= '<br><small>UE Online Application for Admission Ver ' . VERSION . ' ' . $date . " $cs</small><br>"; 
    $html .= "<hr><b>Payment procedures</b><br><br>". @file_get_contents("$path/paymentprocedures.csn"); 
    
    
    $html .= '<br><br>_BUTTONS_';
    $record = $subjects[0];
    $record['TotalFeesDiscounted'] = 'Discounted: <b>' . number_format($record['Discounted'],2) . '</b>';
    $record['UponRegistration'] = '<b>'. number_format($record['DownPayment'],2) . '</b>';
    $record['TotalFees'] = '<b>'. number_format($record['TotalFees'],2) . '</b>';
    $record['ValidUntil'] = $record['Valid_Until'] ? formatdate($record['Valid_Until'],'m/d/Y','') : str_repeat('&nbsp;',10);
    $record['Birthdate'] = formatdate($record['Birthdate'],'m/d/Y',''); 
    $html  = replacetag($html, $record,'','',true);
    
    $buttons  = "<div class='buttons PEFBUTTONS'>$print$cancel</div>";    
    
    $html = str_replace('_BUTTONS_', $buttons, $html);;
    $html = str_replace('_DETAILSBODY_',$details, $html);
    $html = str_replace('_WINDOW_', '__________', $html);

    $result[] = array("html", $html);  
    $result[] = array('eval',"$('.navbar').remove()");   
    $result[] = array('eval',"$('#BODY').prop('style','padding:0px 0px 0px 20px')");   
    return $result;   
    
}

function renderjsalert($msg)
{
    return array('eval',"alert('$msg     ');");
}

function formatdate($date, $format = "m/d/Y", $delimiter = "'") {
    $date = trim($date);
    if ($date == '')
        return "NULL";
    $date = strtotime($date);
    if ($date === false)
        return "NULL";
    return $delimiter . date($format, $date) . $delimiter;
}


function replacetag($html, $record, $prefix="_", $suffix="_", $utfencode=false) {
    if ($prefix == '')
        $prefix = '_';
    if ($suffix == '')
        $suffix = '_';
    foreach ($record as $key => $value) {
        if ($utfencode)
            $value = utf8_encode($value);
        $tag = $prefix . strtoupper($key) . $suffix;
        $html = str_replace($tag, $value, $html);
        if (substr($key,0,3) == 'txt') {
            $tag = $prefix . strtoupper(substr($key,3)) . $suffix; 
            $html = str_replace($tag, $value, $html);
        }
    }
    return $html;
}

?> 