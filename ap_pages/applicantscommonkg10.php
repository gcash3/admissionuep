<?php
include_once('ap_fw/mailer/class.phpmailer.php');
include_once('ap_php/UE.class.Email.php');

class CRUDcredentials extends CSNCRUD  {
    private $semester = '';
    private $campuscode = '';
    public $type = 'B';
    private $titles = array();
    private $matchedname = false;
    
    
    function __construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage, $type)  {
        $this->type = $type;
        parent::__construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage);

//        echo "Applicants Common K to G10";
    }
    
    
    function initialize() {
        global $APP_SESSION;
        $this->semester = $APP_SESSION->getPageSemester();
        $this->campuscode = $APP_SESSION->getCampusCode();

        $this->titles['t1'] = array('edit', 'Pending Applications','bg-green');
        $this->titles['t2'] = array('question', 'Applications On-hold for Review','bg-red');  
        $this->titles['t3'] = array('upload', 'Applications for Reuploading of Credentials','bg-yellow');
        $this->titles['t4'] = array('thumbs-up', 'Approved Applications', 'bg-blue');  
        $this->titles['t5'] = array('thumbs-down', 'Denied Applications', 'bg-orange'); 
        $this->titles['t6'] = array('money', 'Entrance Test Payments', 'bg-green');    
        $this->titles['t7'] = array('pencil', 'For Examination', 'bg-primary');    
         
        // set page access
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

        $badges = $this->getbadges();
         
        // add top buttons
        $this->addtopbutton(1, 't1','Pending','inbox','primary',null, $badges[1]);
        $this->addtopbutton(1, 't2','On-Hold','question','info',null, $badges[2]);
        $this->addtopbutton(1, 't3','For Reupload','upload','warning',null, $badges[3]);
        $this->addtopbutton(1, 't4','Approved','thumbs-up','success',null, $badges[4]);   
        $this->addtopbutton(1, 't5','Denied','thumbs-down','danger',null, $badges[5]);  
        $this->addtopbutton(1, 't6','Payments','money','primary',null, $badges[6]); 
        $this->addtopbutton(1, 't7','For Examination','pencil','primary',null, $badges[7]); 

               
        // grid column definitions [columnname;property1;key2=>property2..]
        $columns0   = '[recordselector]';
        $columnsA   = '[WebReferenceNumber;Reference No][DateApplied;Applied;date;m/d/y]';
        $columnsB   = '[Name][College][Course][Birthdate;Birth Date;date;m/d/Y][ClassDescr;Class]';
        $columns[1] = '';
        $columns[2] = '[SortDate;On-Hold;date;m/d/y]';
        $columns[3] = '[SortDate;For Reupload;date;m/d/y]';
        $columns[4] = '[ApplicationNumber;AppNo][SortDate;Processed;date;m/d/y]';
        $columns[5] = '[SortDate;Denied;date;m/d/y]';
        $columns[6] = '[Series_No;ORNumber][Date_Trans;Date;date;m/d/Y][Particular][AccountName][Amount;;money;columnclass=>text-right][ApplNo]';  
        $columns[7] = '[ApplicationNumber;AppNo][SortDate;Processed;date;m/d/y]';
        $columnsa   = '[actions]';

//echo "<br/>"."APP_DB_DATABASEADMISSION 1"."<br/>";

//echo   APP_DB_DATABASEADMISSION;
//echo "<br/>";

// top button commands, modify parameters if necessar
        //$sqlget = "[onlineadmission]..[Usp_OA_GetApplicationKtoG10] '[@primarykeyvalue]'";
        $sqlget = APP_DB_DATABASEADMISSION . "..[Usp_OA_GetApplicationKtoG10] '[@primarykeyvalue]'";
        
        $sqlsave = '';
        for ($t=1; $t<8; $t++) {
            if ($this->opener == 't6') {
                $columnsA = '';
                $columnsB = '';
            }
            $cols = "$columns0 $columnsA " . $columns[$t] . " $columnsB $columnsa";
            // if ($t < 6)
            //     //$sql = "[onlineadmission]..Usp_OA_GetApplicantsForExamK_G10";
                 $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicantsForExamK_G10";
            // else
            //     //$sql = "[onlineadmission]..Usp_ADMSGetEntranceTestPayments";
            //      $sql = APP_DB_DATABASE. "..Usp_ADMSGetEntranceTestPayments";

//  echo $sql." ".$cols."<br/>";
//  echo $sqlget." ".$cols."<br/>";

        $this->addtopbuttoncommand("t$t", "$sql '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '$t', '[@searchtext]'", $cols,'',($t==6?'Series_No' :''),($t==6?'!' :'R'), $sqlget, $sqlsave);        

        }
        // echo Tools::devonly($sql);
        $this->addformfield('', 1, 'ApplicationNumber'    ,0  ,'number', 'Application No.','','',false,true);
        $this->addformfield('', 2, 'SN','','','UE Student No.','','',false,true); 
        $this->addformfield('', 3, 'LastName');      
        $this->addformfield('', 4, 'FirstName');
        $this->addformfield('', 5, 'MiddleName');
        $this->addformfield('', 0, 'Birthdate');         
        $this->addformfield('', 0, 'Age');  
        $this->addformfield('', 0, 'sex'); 
        $this->addformfield('', 0, 'Ledgerbalance'); 
        $this->addformfield('', 0, 'WebReferenceNumber','','','Ref. No.','','',false,true);  
        $this->addformfield('', 0, 'Email','','email','','','',false,true); 

        //$this->addformfield('', 0, 'CourseDescription','','','Course/Program');  
        $this->addformfield('', 0, 'CourseDescription','','select','Course/Program','','',true,false,false,array('Secondary Laboratory','Science Based Jr. High School'));  
        $this->addformfield('', 0, 'College'); 
        $this->addformfield('', 0, 'CampusCode');     
        $this->addformfield('', 0, 'Semester');    
        $this->addformfield('', 0, 'CampusCodeDescription','','','Campus');    
        $this->addformfield('', 0, 'UserName');
        $this->addformfield('', 0, 'Address1','','','Present Address');   
        $this->addformfield('', 0, 'ZipCode1','','','ZIP Code'); 
        $this->addformfield('', 0, 'Address2','','','Permanent Address');   
        $this->addformfield('', 0, 'ZipCode2','','select','ZIP Code');  
        $this->addformfield('', 0, 'Benefit_Type','','select','Honor Student, Rank','','',true,false,false,array('','Top 10 Honor'));
        $this->addformfield('', 0, 'LRN'); 
        $this->addformfield('', 0, 'ESCSchoolNumber','','','ESC School No.');  
        $this->addformfield('', 0, 'ESCNumber','','','ESC No.'); 
        $this->addformfield('', 0, 'QVRNumber','','','QVR No.'); 
        $this->addformfield('', 0, 'Mobile','','','Mobile No.'); 
        $this->addformfield('', 0, 'LastSchoolAttended','','','Last School');
        $this->addformfield('', 0, 'CourseYear','','','Level');  
        $this->addformfield('', 0, 'PayMode','','','Payment Mode');  
        $this->addformfield('', 0, 'CitizenDescription','','','Citizenship'); 
        $this->addformfield('', 0, 'ForeignCode','','select','Foreign','','',true,false,false,array('','Non Resident','Permanent Resident')); 
        $this->addformlayout('', '[WebReferenceNumber;3][LastName;3][FirstName;3][MiddleName;3]');
        $this->addformlayout('', '[ApplicationNumber;2][UserName;2][Email;2][Birthdate;2][Age;1][sex;1][Ledgerbalance;2]');
        $this->addformlayout('', '[Address1;8][ZipCode1;2][Mobile;2]');
        $this->addformlayout('', '[Address2;8][ZipCode2;2][Benefit_Type;2]');
        $this->addformlayout('', '[LastSchoolAttended;8][CitizenDescription;2][ForeignCode;2]'); 
        $this->addformlayout('', '[Semester;2][CampusCodeDescription;2][CourseDescription;3][CourseYear;2][PayMode ;2]');
        $this->formclass = '';

        $this->recordselector = false;
        
        $this->primarykeypaddinglength = 0;


// print_r($APP_SESSION)."<br/>";       
// Testing Data
//phpinfo(INFO_VARIABLES);

//echo '<pre>'; 
//$_SESSION["Mymodule"]="Applicant's K-G10"."<br/>";
// print_r($_SESSION)."<br/>";
//echo '<pre>';
//ECHO $_COOKIE['PageSemester']."<br/>";
//print_r($APP_SESSION)."<br/>";
//echo Data::getsemesterdescription($APP_SESSION->getpagesemester())."<br/>";
//echo $_SERVER['SCRIPT_NAME']."<br/>";

    }
    
    function callback_fetchformtitle(&$title, &$icon) {
        $icon = $this->titles[$this->opener][0];
    }
    
    function callback_aftershowform(&$html) {
        //echo Tools::devonly('<pre>' . print_r($this->activerecord,true) . '</pre>');
        
    }
    
    function callback_fetchgridtitle(&$title, &$icon, $recordcount) {  
        $title = $this->titles[$this->command][1]; 
        $icon  = HTML::icon($this->titles[$this->command][0]);
    } 
    
    
    function callback_fetchformfooterbuttons(&$footer) {
        global $APP_SESSION;
        if ($APP_SESSION->getCanPrint())
            $footer .= $this->getprintlink($this->activerecord['WebReferenceNumber'],'btn btn-success','Print');
        if ($this->canupdate) {
            if (stripos('t1,t2,t3,t7',$this->opener) !== false) {
                $footer .= HTML::submitbutton('Approve', HTML::icon('thumbs-up','Approve'), 'success');
                if (!$this->matchedname) {
                    $footer .= HTML::submitbutton('ReUpload',HTML::icon('upload','For Reupload'), 'warning'); 
                }
                $footer .= HTML::submitbutton('OnHold',HTML::icon('question','Put On-Hold'), 'info'); 
                // $footer .= HTML::submitbutton('Approve', HTML::icon('thumbs-up','Approve'), 'success');
                $footer .= HTML::submitbutton('Deny',HTML::icon('thumbs-down','Deny'), 'danger'); 
                if (stripos('t1,t2,t3',$this->opener) !== false) {                
                $footer .= HTML::submitbutton('ForExamination',HTML::icon('pencil','For Examination'), 'primary'); 
                }
            } 
        }
        else {
            $footer .= '<em>Current user has insufficient rights to update status</em>';
        }
        $link = "?to=[@To]&subject=[@Subject]&body=[@Body]";
        $to = $this->activerecord['Email'];
        $body = '<title>[NAME: [@LastName], [@FirstName] [@MiddleName], APPLICATION NO.: [@ApplicationNumber] ACCOUNT NO.: [@BankReferenceNumber]</title>';

        if ($this->activerecord['SN'])
            $body .= ', SN: [@SN]';
        $body .= ']';
        $subject = 'STATUS OF YOUR REQUEST FOR ADMISSION FOR THE SY2024-2025';
        Data::replaceparameters($body, $this->activerecord);
        $link = str_replace(array("[@To]","[@Subject]","[@Body]"), array($to, $subject, $body), $link);
        $link = "https://mail.google.com/a/ue.edu.ph/mail/?extsrc=mailto&url=mailto:". urlencode($link);
        $footer.= HTML::linkbutton($link,HTML::icon('envelope',"Compose gmail"),"info pull-right","mailto","_gmail","Compose gmail (for current @ue.edu.ph user only)");

        if ($this->opener === 't7') {
            $footer .= HTML::submitbutton('resendemail',HTML::icon('envelope','Email: Request for admission'),'success verify');         
        }

        // print_r($this->opener);
    }

    function resendemail() {
        global $APP_SESSION;
        global $APP_DBCONNECTION;
        $sql = APP_DB_DATABASEADMISSION. "..Usp_BasicED_EmailNotif '{$this->currentuserid}', $this->primarykeyvalue, 1, 1";        
// print_r($sql);
        $results = $APP_DBCONNECTION->execute($sql);
        if (Tools::emptydataset($results)) {
            echo HTML::alert('Error', 'Unable to send email to applicant!');
            return;
        }
        $emailmessage = $this->sendemailexamdate($results[0]) ? 'Email sent to applicant.' : 'Unable to send email to applicant.';
        $redirect = "$this->currentpage/$this->opener/0/0/0/$this->opener"; 
        $APP_SESSION->setApplicationMessage($emailmessage, true);
        Tools::redirect($redirect); 
    }

    function sendemailexamdate($record) {
        $message = "<h3>STATUS OF YOUR REQUEST FOR ADMISSION FOR THE SY2024-2025</h3>
        <div>
        Greetings from the Basic Education Department of UE Caloocan!
                We kindly inform you that we have received the pre-enrolment application you submitted. We are
                delighted by your interest in being admitted to the Basic Education Department, and we look
                forward to welcoming you to the classrooms and learning halls of the LCT Building here at UE
                Caloocan.</div>
                
        <br>
        <div>
                Regarding your request for admission, please note that part of the screening process involves an
                ENTRANCE EXAMINATION, which must be completed before confirmation of enrolment.</div><br>
        
        <h3>   Below are important reminders for your guidance: </h3> 
        
        <div><ol>   1. Upon receipt of this email, you may proceed to the UE Cashier’s Office to pay the
                Application Fee. Specific amounts for each grade level are provided below:</ol>
        </div>
        
        <table align='center' style='font-family: arial, sans-serif;
                  border-collapse: collapse;
                  border-style:solid;
                  border-width:1px;
                  width: 60%;'>
        <tbody align='center'>
        <tr bgcolor='lightgray' >
            <th style='border-collapse: collapse; border-style:solid; border-width:1px;'>GRADE LEVEL</th>
            <th style='border-collapse: collapse; border-style:solid; border-width:1px;'>APPLICATION FEE</th>
          </tr>
          <tr >
            <td style='border-collapse: collapse; border-style:solid; border-width:1px;'>KINDER - GRADE 6</td>
            <td style='border-collapse: collapse; border-style:solid; border-width:1px;'>P150.00</td>
          </tr>
          <tr >
            <td style='border-collapse: collapse; border-style:solid; border-width:1px;'>GRADES 7 - 10</td>
            <td style='border-collapse: collapse; border-style:solid; border-width:1px;'>P300.00</td>
          </tr>
          </tbody></table>
        
        <div><ol>
        2. After payment, please bring the proof of payment or receipt to the Office of the Principal
        (Window 1). In exchange, an Examination Slip will be issued indicating the schedule of the
        examination, which will be taken in person at the Computer Laboratory of the LCT Building,
        Basic Education Department, UE Caloocan.
        </ol></div>
        <div><ol>
        3. After successfully passing the screening process, please proceed to the Office of the
        Principal for the issuance of the Pre-Enrollment Form (PEF).
        </div></ol>
        <div><ol>
        4. Proceed to the Cashier’s Office and pay the corresponding fees indicated in the PEF.
        </div></ol>
          
        <div><h4>Payment Options:</h4>
        
        <div><ol>
        1. Payments of tuition fees for enrolment to UE will be accepted by the following:<ol></div>
            <ol><ol>a. UE Cashier (cash, cheque or credit card)</ol></ol>
              <ol><ol>b. PNB over-the-counter</ol></ol>
               <ol><ol>c. Gcash</ol></ol>
        
        <div><ol>2. Send a copy of the deposit slip or proof of payment to finance@ue.edu.ph for validation
        and an official receipt.</ol></div>
        
        <div><ol>3. Once your payment has been validated, you will receive an email confirming your official
        enrollment and providing other necessary information for the upcoming school year.</ol></div>
        
        <div><ol>4. After validation of payment, you may download your Registration Form through your
        Student Portal, or you may choose to proceed to the I.T. Department for printing.</ol></div>
        
        <div><ol>For an overview of the UE Basic Education Department - Caloocan for SY 2024-2025 and related
        information, please visit: https://www.facebook.com/UE.BASICED.CAL/</ol></div>
        
        <div><ol>If you have any questions, please email basiced.cal@ue.edu.ph or call telephone numbers 8367-
        4572 local 202 or 8-366-5848 (during office hours, Mondays to Fridays).</ol></div>
        
        <div><ol>Thank you, warrior student!</ol></div>";
        $to = $record['Email'];
        $mail = new Email();
        $mail->DemoAddress = false;
        $bcc = '';
        $record['MessageBody'] = $message; 
        return @$mail->sendTemplate(0, $record, $to, $bcc, true, false);
        // return @$mail->sendTemplate(0, $record, $to, $bcc, APP_PRODUCTION, !APP_PRODUCTION);
    }    
    
    function callback_aftershowfieldsall(&$form)  {
         global $APP_DBCONNECTION;

         $html = '<hr><b>Uploaded Credentials:</b><br><br>';
         $wrn = $this->activerecord['WebReferenceNumber'];

         $sql = APP_DB_DATABASEADMISSION . "..Usp_OA_GetFiles $wrn";
// echo "<br>".$sql."<br>";

        $results = $APP_DBCONNECTION->execute($sql);
         if (Tools::emptydataset($results)) {
             $html .= HTML::alert('','No files uploaded!','danger',false);
             if (!is_array($results))
                $this->showdebugerror($results, $sql); 
         }
         else {
             $html .= '<div class="row">';
             $i=0;
             foreach ($results as $file) {
                $fpclass = $file['Width'] > $file['Height'] ? 'fpLandscape' : 'fpPortrait';
                if ($file['Width'] == $file['Height'])
                    $fpclass = 'fpSquare';
                $i++;
                $fn = $file['ImageTypeID']; 
                $page = $fn % 10; 
                $src = getfilesrc($fn, $wrn, $fn, 0, 0); 

                $html .= '<div class="col-sm-4 " >';
                $html .= "<img class='img-thumbnail $fpclass' onclick=\"js_zoom('tnfile$fn')\" src='$src' id='tnfile$fn'><br>";
                $html .= "<span class='btn btn-sm btn-info fileicon' title='Zoom' onclick=\"js_zoom('tnfile$fn')\"><i class='glyphicon glyphicon-zoom-in'></i></span>";   
                $html .= "<sub title='Page $page'>P$page</sub><br>";
                $html .= "<span class='imageDescription'>".$file['Description']."</span>".'<br>';
                $html .= '<small>'.Data::formatdate($file['Timestamp'],'m/d/Y h:i') .' ' . round($file['Size']/1024) . 'Kb</small><br>';
                $html .= '</div>';
                if ($i>2) {
                    $i=0;
                    $html .= '</div><div class="row">';
                }
             }
             $html .= '</div>';

         }
         
         $t = substr($this->opener,1,1);
         //$sql ="[onlineadmission]..Usp_OA_GetApplicantsForExamK_G10 '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '$t', '$this->primarykeyvalue'";                                                               
         $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicantsForExamK_G10 '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '$t', '$this->primarykeyvalue'";                                                               

//  echo '<pre>'; 
//  echo print_r($sql);
// echo $t;
//  echo '</pre>'; 

        //   echo Tools::devonly($sql);
         $results = $APP_DBCONNECTION->execute($sql);
         $status = '';
         if (!Tools::emptydataset($results)) {
             $status = $results[0]['StatusRemarks'];
         }
         $this->matchedname = false;
         $columns = array();
         $columns['1']= '<span title="Top1">Top 1</span>';
         $columns['2']= '<span title="Top2">Top 2</span>';
         $coursecolumn = array();
         $coursecolumn['1'] = "";

         if (stripos('t4,t5',$this->opener) === false) {
             $html .= '<hr><div class="row">';
             $html .= HTML::hforminputtext('txtStatus','Current Status:',$status,'Status',false,true,false,0,0,'text','col-sm-10');
             $html .= '</div>';
             $html .= '<div class="row">';
             $html .= HTML::hforminputtext('txtRemarks','Remarks:',@$this->activerecord['txtRemarks'],'Remarks',true,!$this->canupdate,false,0,0,'text','col-sm-10');
             $html .= '</div>';
             if (trim($results[0]['MatchedName'])) {
                $html .= HTML::callout('Attention!','Matching application found: ' . utf8_encode($results[0]['MatchedName']) . ', Application No.: ' . $results[0]['MatchedApplicationNumber'],'info');
                $this->matchedname = true;
             }
             
         }
         else {
             $html .= '<hr><div class="row">';
             $html .= HTML::hforminputtext('txtStatus','Current Status:',$status,'Status',false,true,false,0,0,'text','col-sm-8');
             $html .= HTML::hforminputtext('txtATMReferenceNumber','Account Number:',$this->activerecord['BankReferenceNumber'],'Bank Reference Number',false,true,false,0,0,'text','col-sm-4');
             $html .= '</div>';
            //  if ($this->opener  == 't4') {
            //      $sql = APP_DB_DATABASEADMISSION. ".[dbo].Usp_ADMSCheckEnranceTestPayment '{$this->semester}', '{$this->campuscode}', '{$this->activerecord['LastName']}', '{$this->activerecord['FirstName']}', '{$this->activerecord['MiddleName']}'";
            //      $payments = $APP_DBCONNECTION->execute($sql);
            //      if (Tools::emptydataset($payments)) {
            //          $html .= 'No CET/CAIA payment found!' . Tools::devonly($sql);
            //      }
            //      else {
            //          $html .= '<div class="row">';
            //          $i = 0;
            //          $size = 4;
            //          if (count($payments) < 2 )
            //             $size = 8;
            //          elseif (count($payments) == 2)
            //             $size = 6;
            //          foreach ($payments as $payment) {
            //             $ordata = 'O.R. No.: ' . $payment['Series_No'] . ' ' . 
            //                       'Date: ' . Data::formatdate($payment['Date_Trans']) . ' ' .
            //                       'Amount: ' . number_format($payment['Amount'],2) . ' ' .
            //                       'Particular: ' . $payment['Particular'];
            //             $html .= HTML::hforminputtext("Payment$i",'Entrance Test Payment:',$ordata,'',false,true,false,0,0,'text',"col-sm-$size");
            //             $i++;
            //             if ($i >= 3) {
            //                 $html .= '</div><div class="row">';  
            //                 $i = 0;   
            //             }
            //          }
            //          $html .= '</div>';    
            //      }
            //  }
         }
         $form .= $html;
    }
    
    
    function callback_post(&$cancel) {
        global $APP_DBCONNECTION;
        global $APP_SESSION;
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
        elseif (isset($_POST['ForExamination']))   
            $newstatus = '7';    
        elseif (isset($_POST['SaveBenefitType']))    
            $newstatus = 'SaveBenefitType';       
        elseif (isset($_POST['CourseDescr']))    
            $newstatus = 'CourseDescr';       
        elseif (isset($_POST['SaveForeignCode']))    
        $newstatus = 'SaveForeignCode';            
        elseif (isset($_POST['resendemail'])) {
            $this->resendemail();
            return;
        }        
        // $CourseYear = $this->activerecord['CourseYear'];            
        // if ($CourseYear <> "Grade 7") {
        //     ? > <script>
        //         $('#Benefit_type').hide();
        //         </script>
        //     < ?php
        // }

        if ($newstatus) {
            $cancel = true;
            $saved = false;
            
            if ($newstatus == 'SaveBenefitType') {
                $BenefitType = $this->activerecord['SaveBenefitType'];

                $sql = APP_DB_DATABASEADMISSION.".[dbo].[usp_UpdateHonorBenefitsTop10]   $this->primarykeyvalue,  '$BenefitType'";
                //  echo '<pre>',print_r($sql), '</pre>';   
                //  echo '<pre>',print_r($this->activerecord['SaveBenefitType']), '</pre>';   
                //  echo '<pre>',print_r($this->activerecord,true), '</pre>';                
            }
            elseif ($newstatus == 'SaveForeignCode') {
                $ForeignCode = $this->activerecord['SaveForeignCode'];

                $sql = APP_DB_DATABASEADMISSION.".[dbo].[usp_UpdateForeignStatus]   $this->primarykeyvalue,  '$ForeignCode'";
            }
            else {
                $remarks = $this->activerecord['txtRemarks'];
                $wrn = $this->activerecord['WebReferenceNumber'];
                $semester = $this->activerecord['Semester'];
                $sn = $this->activerecord['SN'];
                
                //$sql = "[onlineadmission]..Usp_OA_SaveApplicationStatus '{$this->currentuserid}', $this->primarykeyvalue, '$newstatus', '$remarks', '$semester', '$sn'";
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveApplicationStatus '{$this->currentuserid}', $this->primarykeyvalue, '$newstatus', '$remarks', '$semester', '$sn'";
            }
            // echo '<pre>'; 
            // echo $sql;    
            // echo '</pre>';         
            // return;

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
        //$sql=  "[ue database]..Usp_OA_GetApplicantCredentialsSummary '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type'";
        $sql= APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicantCredentialsSummary '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type'";
        //  print_r($sql) ;
        /*
        $results = $APP_DBCONNECTION->execute($sql);
        if (!Tools::emptydataset($results)) {
            foreach ($results as $record) {
                $key = $record['Status'];
                if ($record['Total'])
                    $badges[$key] = HTML::badge($record['Total'],$this->titles["t$key"][2]);
            }
        }
        else {
            $this->showdebugerror($results,$sql);  
        }
        */
        return $badges;
    }
    
    
    function callback_fetchsqlcommand($commandtype, &$sql) {
        // echo Tools::devonly("<h1>$sql</h1>");
//        print_r($_POST);
    } 
    
    function callback_fetchdata($name, $primarykeyvalue, $record, $cs, &$value) {
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

<script>
    
</script>