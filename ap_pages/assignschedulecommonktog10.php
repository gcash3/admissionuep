<?php
class CRUDcredentials extends CSNCRUD  {
    private $semester = '';
    private $campuscode = '';
    public $type = 'B';
    private $titles = array();
    public $Sex = '';
    public $Campus = '';

    
    function __construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage, $type)  {
        $this->type = $type;
        $this->primarykey = $primarykey;
        $_SESSION["TopButton"]  = $defaultcommand;
        parent::__construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage);

//        echo "<br/>"."Assigning of schedule for approved applicants (Kinder to G10)"."<br/>";
    }
    
        function initialize() {
            
        global $APP_SESSION;
        $this->semester = $APP_SESSION->getPageSemester();
        $_SESSION["Semester"] = $this->semester;
        $this->campuscode = $APP_SESSION->getCampusCode();
        $_SESSION["TopButton"]  = $this->command;
        
        $this->titles['t1'] = array('thumbs-up', 'Approved Applications', 'bg-blue');  
        $this->titles['t2'] = array('thumbs-up', 'Old Students', 'bg-blue');  
         
        // set page access
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

        $badges = $this->getbadges();

        // add top buttons
        $this->addtopbutton(1, 't1','Approved','thumbs-up','success',null, $badges[1]);   
        $this->addtopbutton(2, 't2','Old Students','fa fa-id-card-o','success',null, $badges[2]);   

        $_SESSION["YearLevel"]  = '';
        // grid column definitions [columnname;property1;key2=>property2..]
        $columns0   = '[recordselector]';
        $columnsA   = '[WebReferenceNumber;Reference No][DateApplied;Applied;date;m/d/y]';
        $columnsB   = '[Name][College][Course][Birthdate;Birth Date;date;m/d/Y][pef]';
        $columns[1] = '';
        $columns[2] = '[SortDate;On-Hold;date;m/d/y]';
        $columns[3] = '[SortDate;For Reupload;date;m/d/y]';
        $columns[4] = '[ApplicationNumber;AppNo][SortDate;Processed;date;m/d/y]';
        $columns[5] = '[SortDate;Denied;date;m/d/y]';
        $columns[6] = '[Series_No;ORNumber][Date_Trans;Date;date;m/d/Y][Particular][AccountName][Amount;;money;columnclass=>text-right][ApplNo]';  
        $columnsa   = '[actions]';
        
//echo "<br/>"."APP_DB_DATABASEADMISSION 1"."<br/>";

//echo   APP_DB_DATABASEADMISSION;
//echo "<br/>";

// top button commands, modify parameters if necessar
        
    //if (APP_PRODUCTION) {
        // LIVE SERVER
        $_SESSION["SN"]  = "";
        $_SESSION["Birthdate"]  = "";
        $_SESSION["Address"]  = "";
        $_SESSION["Name"]  = "";
        $_SESSION["YearLevel"]  = "";
        $_SESSION["College"]  = "";
        $_SESSION["Course"]  = "";
        $_SESSION["CampusCode"]  = "";
        $_SESSION["Sex"] = "";
        $_SESSION["CourseYear"] = "";
        $_SESSION["selectedCourseYear"] = "";
        $_SESSION["ApplicationNumber"] = "";
        $_SESSION["PaymentMode"] = "C";
        $_SESSION["BlockCode"] = "";
        
        //$sqlget =  "[onlineadmission]..[Usp_OA_GetApplicationKtoG10] '[@primarykeyvalue]'";
        $sqlget = APP_DB_DATABASEADMISSION . "..[Usp_OA_GetApplicationKtoG10] '[@primarykeyvalue]'";
        //$sqlget2 = "[onlineadmission]..[usp_OA_GetOldStudents] '$this->semester', '$this->campuscode', '[@searchtext]'";
        $sqlget2 = APP_DB_DATABASEADMISSION . "..[usp_OA_GetOldStudents] '$this->semester', '$this->campuscode', '[@searchtext]'";

        $sqlsave = '';
        for ($t=1; $t<2; $t++) {
            if ($this->opener == 't6') {
                $columnsA = '';
                $columnsB = '';
            }
            $cols = "$columns0 $columnsA " . $columns[$t] . " $columnsB $columnsa";

            $sql = APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicantsForExamK_G10";
            
// echo $sql." ".$cols."<br/>";
            $this->addtopbuttoncommand("t1", "$sql    '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '4', '[@searchtext]'", $cols,'',($t==6?'Series_No' :''),($t==6?'!' :'R'), $sqlget, $sqlsave);        
        }
        $this->addtopbuttoncommand("t2", "$sqlget2", '[recordselector] [SN;SN][Name][College][Course][YearLevel][Birthdate;Birth Date;date;m/d/Y][pef][actions][pdf]', '','','R', $sqlget2, $sqlsave);                

        if (isset($_POST['btnPrintBlock'])){ 
            // echo "<H1>testing</H1>";
//            echo phpinfo(INFO_VARIABLES);
//            echo base64_decode($_GET["_p3"]);
        }
        
        $this->formclass = '';

        $this->recordselector = false;
        
        $this->primarykeypaddinglength = 0;
        

// Testing Data
//phpinfo(INFO_VARIABLES);
//echo APP_BASE;
//  echo '<pre>'; 
//$_SESSION["Mymodule"]="Applicant's K-G10"."<br/>";
//echo $sqlget;
// print_r($APP_SESSION);
//print_r($APP_SESSION)."<br/>";
//  echo '</pre>';
//ECHO $_COOKIE['PageSemester']."<br/>";

//echo Data::getsemesterdescription($APP_SESSION->getpagesemester())."<br/>";
//echo $_SERVER['SCRIPT_NAME']."<br/>";

        
    }
    
    function callback_fetchformtitle(&$title, &$icon) {
        $icon = $this->titles[$this->opener][0];
    }
    
    function callback_aftershowform(&$html) {
    }
    
    function callback_fetchgridtitle(&$title, &$icon, $recordcount) {  
        $title = $this->titles[$this->command][1]; 
        $icon  = HTML::icon($this->titles[$this->command][0]);
        
    } 
    
    
    function callback_fetchformfooterbuttons(&$footer) {
        global $APP_SESSION;
        $link = "?to=[@To]&subject=[CONFIRMATION OF ENROLLMENT APPLICATION]&body=[@Body]";
        $to = $_SESSION["Email"];
        $body = 'NAME: '.$_SESSION["Name"].',  SN: '. $this->activerecord['SN'] ;
        
        $sem2 = substr($this->semester,0,4) + 1;

        if ($_SESSION["CampusCode"]=="1"){
        $body .= "
        
        Greetings and congratulations! Your uploaded enrollment application has been approved and you may now proceed to payment of tuition fee.

        Attached with this email is a copy of your Pre-Enrollment Form (PEF). Please be advised that the class-schedule options in enrolling online are based 
        on the general schedules of on-campus classes. For enrollment for the incoming school year, which will be blended (limited face-to-face, synchronous 
        and asynchronous), the schedule options will basically be the template of your class program.  Any changes to the schedule will be made known to you 
        close to or on the first day of classes by your assigned Class Adviser.

        You may print or take a screenshot of your PEF as a reference in paying the school fees. Please take note of the validity date on your PEF. In case it 
        expired prior to payment, please send us an email requesting for a new copy of PEF to k12cal.admissions@ue.edu.ph';

        Reminders for the payment procedure:

        1. There are four payment options: UE Cashier, PNB over-the-counter, GCash,  and BancNet ATM or BancNet Online.

        2. Send a copy of the deposit slip or proof of payment to finance@ue.edu.ph for the official receipt.

        3. As soon as your payment has been validated, you will receive an email that you are officially 
        enrolled and other information that you need for the coming school year.

        4. After validation of payment, you may download your Registration Form through your Student Portal, or you may choose to proceed to I.T. Department for printing. 

        For an overview of the UE Basic Education Department (BEd)-Caloocan for SY " . substr($this->semester,0,4) . "-" . $sem2 . " and related information, please click here: https://www.facebook.com/UE.BASICED.CAL/ 

        If you have any questions, please email basiced.cal@ue.edu.ph or call telephone numbers 8367-4572 local 202 or 8-366-5848 (within office hours, Mondays to Fridays).


        Thank you and welcome to University of the East, Warrior Student!

        UE CALOOCAN
        Basic Education Department ";}

        $subject = '';
        Data::replaceparameters($body, $this->activerecord);
        $link = str_replace(array("[@To]","[@Subject]","[@Body]"), array($to, $subject, $body), $link);
        $link = "https://mail.google.com/a/ue.edu.ph/mail/?extsrc=mailto&url=mailto:". urlencode($link);
        $footer.= HTML::linkbutton($link,HTML::icon('envelope',"Compose gmail"),"info","mailto","_gmail","Compose gmail (for current @ue.edu.ph user only)");

    }
    
function callback_aftershowfieldsall(&$form)  {
global $APP_DBCONNECTION;
        
if ($_GET["_p5"]=="t1") {
    $sqlget = APP_DB_DATABASEADMISSION . "..[Usp_OA_GetApplicationKtoG10] '".$this->primarykeyvalue."'";
    $_SESSION["isOldStudent"] = "0";
} 
else 
{
    $sqlget = APP_DB_DATABASEADMISSION . "..[usp_OA_GetOldStudents] '$this->semester', '$this->campuscode','".$this->primarykeyvalue."'";
    $_SESSION["isOldStudent"] = "1";
}

//   echo  $sqlget;

$resulta = $APP_DBCONNECTION->execute($sqlget);         
$College = $resulta[0]['College'];
$_SESSION["BlockCode"] = $resulta[0]['BlockCode'];
$_SESSION["WebReferenceNumber"] = $resulta[0]['WebReferenceNumber'];
$_SESSION["SN"]  = $resulta[0]['SN'];
$YearLevel = $resulta[0]['YearLevel'];
$_SESSION["YearLevel"]  = $resulta[0]['YearLevel'];
$_SESSION["College"]  = $resulta[0]['College'];
$_SESSION["Course"]  = $resulta[0]['Course'];
$_SESSION["CampusCode"]  = $resulta[0]['CampusCode'];
$_SESSION["sex"] = $resulta[0]['sex'];
$_SESSION["CourseYear"] = $resulta[0]['CourseYear'];
$_SESSION["selectedCourseYear"] = $resulta[0]['CourseYear'];
$_SESSION["ApplicationNumber"] = $resulta[0]['ApplicationNumber'];
$_SESSION["PaymentMode"] = $resulta[0]['PaymentMode'];
$_SESSION["Birthdate"]  = $resulta[0]['Birthdate'];
$_SESSION["Address"]  = $resulta[0]['Address2'];
$_SESSION["Name"]  = $resulta[0]['LastName'].", ".$resulta[0]['FirstName']." ".$resulta[0]['MiddleName'];
$_SESSION["Class"]  = $resulta[0]['Class'];
$_SESSION["Email"]  = $resulta[0]['Email'];
$_SESSION["ApplicationNumber"] = $resulta[0]['ApplicationNumber'];
$_SESSION["BankReferenceNumber"] = $resulta[0]['BankReferenceNumber'];
$_SESSION["LastDayOfEnrolment"] = $resulta[0]['LastDayOfEnrolment'];
$_SESSION["LastDayOfEnrolmentDesc"] = $resulta[0]['LastDayOfEnrolmentDesc'];
$_SESSION["Ledgerbalance"] = $resulta[0]['Ledgerbalance'];
$_SESSION["LRN"] = $resulta[0]['LRN'];	
$_SESSION["Date_Validated"] = $resulta[0]['Date_Validated'];
$_SESSION["BlockCode"] = $resulta[0]['BlockCode'];
$sql = APP_DB_DATABASE ."..Usp_SelectFreshBlockSubjectCode '$this->semester','','$College','$YearLevel','".$_SESSION["CampusCode"]."',1";

// echo $sql . '<br>';
$results = $APP_DBCONNECTION->execute($sql);
$numrec = count($results);

$html = '<div class="col-md-8"> <style="height:2px;border-width:0;color:gray;background-color:gray; padding:0px><br>';
$html .= '<div id="BLOCKSCHEDULE" name="BlockCode" class="col-sm-12" style="cursor: default; padding: 0px">
           <table class="table table-bordered table-striped table-hover table-condensed" >
                <thead><tr class="bg-green">
                    <th>BlockCode</th><th>Section</th><th>Curriculum</th></thead>
                <tbody id="Blocktbody">';
        for ($i=0;$i<$numrec;$i++) { 
            $bsc=$results[$i]['BlockSubjectCode'];
$html .= '<tr class="selector '. ($_SESSION["BlockCode"] == $bsc ? "info" : "") .'" id='.$bsc.'><td><a href="#" id = "BlockCode"  onclick="fBlockSchedule(\''.$bsc."','".Crypto::GenericChecksum($bsc).'\')">'.$bsc."</td><td>".
         '<a href="#" id = "BlockCode"  onclick="fBlockSchedule(\''.$bsc."','".Crypto::GenericChecksum($bsc).'\')">'.$results[$i]['section']."</td><td>".
                     $results[$i]['curriculum']."</td></tr>";
        }
$html .= '<tr><td colspan="4" class="text-left bg-warning" style="background-color:  color: white "><b>'.$results[0]['description'].'</b></td></tr>';

$html .= '</tbody></table></div>';

$html .= '<div name="Schedule" id="Schedule" class="col-sm-12 " style="cursor: default; padding: 0px">
            <table class="table table-bordered table-striped table-hover table-condensed" id="tSchedule">
                <thead><tr class="bg-green"><th class="col-sm-3">Subject</th><th class="col-sm-1">Section</th><th class="col-sm-1">Days</th><th class="col-sm-1">Time</th><th class="col-sm-1">Room</th><th class="col-sm-1">Limit</th><th class="col-sm-1">Size</th></tr></thead>
                <tbody id="tbodySchedule"><tr id="trSchedule">
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr>
                    <td>0 Subject(s)</td><td><div align="center"></div></td><td></td><td></td><td></td><td>&nbsp;</td></tr></tbody></table></div>';
         
         $wrn = $this->activerecord['WebReferenceNumber'];

             $x = '</div><div id="Fees" class="col-sm-4 " style="cursor: default; padding: 0px"><b>'.
             '<div  id="Fees"></div>'.
             "</b></div></div>";
             
             $form .= $html.$x; 

if (!is_array($results)) {
    echo '<b>Unable to load records!</b>';
}
else {

    if ($College=="K"){
        $Options = array('Kinder 1','Kinder 2');
    }
    elseif ($College=="Y"){
        if ($_SESSION["YearLevel"] == 1) {
            $Options = array('Grade 1','Grade 2');
            }
        if ($_SESSION["YearLevel"] == 2) {
            $Options = array('Grade 2','Grade 3');
            }
        if ($_SESSION["YearLevel"] == 3) {
            $Options = array('Grade 3','Grade 4');
            }
        if ($_SESSION["YearLevel"] == 4) {
            $Options = array('Grade 4','Grade 5');
            }
        if ($_SESSION["YearLevel"] == 5) {
            $Options = array('Grade 5','Grade 6');
            }
        if ($_SESSION["YearLevel"] == 6) {
            $Options = array('Grade 6');
            }
    }
    else {
        if ($_SESSION["YearLevel"] == 1) {
            $Options = array('Grade 7','Grade 8');
            }
        if ($_SESSION["YearLevel"] == 2) {
            $Options = array('Grade 8','Grade 9');
            }
        if ($_SESSION["YearLevel"] == 3) {
            $Options = array('Grade 9','Grade 10');
            }
        if ($_SESSION["YearLevel"] == 4) {
            $Options = array('Grade 10');
        }
    }

    if ($resulta[0]['PaymentMode']=="C") {
        $PMode  = "CASH"; }
    elseif ($resulta[0]['PaymentMode']=="I3") { 
        $PMode  = "3 INSTALLMENT";}
    elseif ($resulta[0]['PaymentMode']=="I5" || $resulta[0]['PaymentMode']=="I" ) {
        $PMode  = "5 INSTALLMENT";}
    elseif ($resulta[0]['PaymentMode']=="I7") {
        $PMode  = "7 INSTALLMENT";}
    elseif ($resulta[0]['PaymentMode']=="I9") {
        $PMode  = "9 INSTALLMENT";
    }

    $html2 = "<table class='table table-condensed payslip'> 
            <tbody class='table-bordered'>
                <tr><td class='col-sm-2'>Reference No.</td><td class=''><b>".$resulta[0]['WebReferenceNumber']."</b></td>
                    <td class='col-sm-1'>Department</td><td class=''><b>".$resulta[0]['CollegeDesc']."</b></td>
                </tr>
                <tr><td class='col-sm-2'>Student No.</td><td id='StudNo' class=''><b>".$resulta[0]['SN']."</b></td> 
                    <td class='col-sm-2'>Course</td><td class=''><b>"."<select  id='listCourseYear' name='listCourseYear' class='form-control input-sm' onchange=getSelectedCourseYear('.Crypto::GenericChecksum(12345).')".' style="margin-top: 6x; margin-right: 10px; padding-left: 0px;">';
                    foreach ($Options as $Options){
                        if ($_SESSION["CourseYear"] == $Options){
                            $html2 .= "<option value='".$_SESSION["CourseYear"]."' selected>".$_SESSION["CourseYear"]."</option>";
                        } else {$html2 .= "<option value='".$Options."'>".$Options."</option>";}
                    }

                    
    $html2 .= "</select> </b></td></tr>
                <tr><td class='col-sm-3'>Lastname</td><td class=''><b>".$resulta[0]['LastName']."</b></td><td class='col-sm-1'>Campus</td><td class=''><b>".$resulta[0]['CampusCodeDescription']."</b></td></tr>
                <tr><td class='col-sm-3'>Firstname</td><td class=''><b>".$resulta[0]['FirstName']."</b></td><td class='col-sm-1'>Sex</td><td class=''><b>".$resulta[0]['SexDescription']."</b></td></tr>
                <tr><td class='col-sm-3'>Middlename</td><td class=''><b>".$resulta[0]['MiddleName']."</b></td> <td class='col-sm-1'>Mode of Payment</td><td class=''><b>".$PMode."</b></td></tr>
            </tbody>
            </table>";
    echo HTML::box('Applicant Data', $html2, '', 'success',true,false,'table-responsive ApplicantData');
    // echo $_SESSION["BlockCode"] . " - " . Crypto::GenericChecksum($_SESSION[ "BlockCode"] );
    }         
}

    function callback_post(&$cancel) {
        global $APP_DBCONNECTION;
        
        $newstatus = '';
        if (isset($_POST['Approve']))
            $newstatus = '4';
        elseif (isset($_POST['ReUpload']))
            $newstatus = '3';
        elseif (isset($_POST['Deny'])) 
            $newstatus = '5';
        elseif (isset($_POST['OnHold']))   
            $newstatus = '2';
        elseif (isset($_POST['Pending']))   
            $newstatus = '1';    
        if ($newstatus) {
            $cancel = true;
            $saved = false;
            
            $remarks = $this->activerecord['txtRemarks'];
            $wrn = $this->activerecord['WebReferenceNumber'];
            $semester = $this->activerecord['Semester'];
            $sn = $this->activerecord['SN'];
            
            $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveApplicationStatus '{$this->currentuserid}', $this->primarykeyvalue, '$newstatus', '$remarks', '$semester', '$sn'";
//echo $sql;            
            $APP_DBCONNECTION->begintransaction();
            $results = $APP_DBCONNECTION->execute($sql);
            $saved = !Tools::emptydataset($results);
            if ($saved) {
                $APP_DBCONNECTION->commit();
                $redirect = "$this->currentpage/$this->opener/0/0/0/$this->opener"; 
                Tools::redirect($redirect); 
            }
            else {
                $APP_DBCONNECTION->rollback();
                echo HTML::alert('Error','Unable to save application status!','danger');
                $this->showdebugerror($results,$sql); 
            }
        }
        

    }
    
    function getbadges() {
        global $APP_DBCONNECTION;
        $badges = array('','','','','','','','','');
        $colors[1] = '';
        $sql= APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicantCredentialsSummary '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type'";
        return $badges;
    }
    
    function callback_fetchsqlcommand($commandtype, &$sql) {
//        echo Tools::devonly("<h1>$sql</h1>");
//        print_r($_POST);

    } 
    
    function callback_fetchdata($name, $primarykeyvalue, $record, $cs, &$value) {
        
        return;
        if ($this->opener=='t3')   {
            if (($name == 'SortDate') && $record['ReUploadDate']) {
                $date = Data::formatdate($record['ReUploadDate'],'Ymd');
                if ($date > Data::formatdate($value,'Ymd'))
                    $value = Data::formatdate($value,'m/d/y h:s') . ' ' . HTML::icon('exclamation','','','Reuploaded ' . $record['ReUploadDate']);
            }

        }
        if (($name == 'Name') && $record['UEStudent'])  {
            $value .= ' <sup class="text-danger" title="UE Student">UE</sup>';
        }
        if (($this->opener == 't6') && ($name == 'ApplNo'))  {
            if (preg_match('/ref\. A?([0-9]+)/i', $record['AccountName'],$matches)) {
                $value = $matches[1];
                if (strlen($value) == 11)
                    $value = substr($value,3,7);
            }
        }
    }
    
    function getprintlink($ApplicationID, $class='xs bg-orange', $caption='') {
        $cs = Crypto::GenericChecksum("$ApplicationID") . '/' . mt_rand();
        return HTML::linkbutton("applicationform/$ApplicationID/$cs/0/plain",HTML::icon('print',$caption), $class,'pef','pef');
    }
    
}
?>

