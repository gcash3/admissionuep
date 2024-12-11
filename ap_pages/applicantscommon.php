<?php
include_once('ap_fw/mailer/class.phpmailer.php');
include_once('ap_php/UE.class.Email.php');

$applicationnumber = '';
$webreferencenumber = '';
class CRUDcredentials extends CSNCRUD  {
    private $semester = '';
    private $campuscode = '';
    public $type = 'C';
    private $titles = array();
    private $matchedname = false;
    private $class_array = array();
    private $graduate;
    private $TEXT_NOCET = 'No application fee found!';
    
    function __construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage, $type)  {
        $this->type = $type;
        $this->graduate = substr($type,0,1) == 'G';
        if ($this->graduate)
            $this->TEXT_NOCET = 'No entrance exam payment found!';

        parent::__construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage);
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
        if ($this->type != 'L') 
            $this->titles['t6'] = array('money', 'Entrance Test Payments', 'bg-green');    
        $this->titles['t7'] = array('upload', 'Applications with Re-Uploaded Credentitals', 'bg-green');    
        $this->titles['t8'] = array('verified', 'Applications with Verified Application', 'bg-red');
        if ($this->type == 'C')
            $this->titles['t9'] = array('heart', 'Pending Applications (Free CET)','bg-green');
         
        // set page access
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

        $badges = $this->getbadges();
         
        // add top buttons
        $this->addtopbutton(1, 't1','Pending','inbox','primary',null, $badges[1]);
        $this->addtopbutton(1, 't8','Verified','check','success',null, $badges[8]);         
        $this->addtopbutton(1, 't2','On-Hold','question','info',null, $badges[2]);
        $this->addtopbutton(1, 't3','For Reupload','upload','warning',null, $badges[3]);
        $this->addtopbutton(1, 't7','Uploaded','cloud-upload','warning',null, $badges[7]);
        $this->addtopbutton(1, 't4','Approved','thumbs-up','success',null, $badges[4]);   
        $this->addtopbutton(1, 't5','Denied','thumbs-down','danger',null, $badges[5]);  
        if ($this->type != 'L') 
            $this->addtopbutton(1, 't6','Payments','money','primary',null, $badges[6]); 
        if ($this->type == 'C') 
            $this->addtopbutton(1, 't9','Free CET','heart','danger',null, $badges[9]);
        
               
        // grid column definitions [columnname;property1;key2=>property2..]
        $yearcolumn = $this->type == 'S' ? '[YearLevel;Year]' : '';
        $columns0   = '[recordselector]';
        $columnsA   = '[WebReferenceNumber;Reference No][DateApplied;Applied;date;m/d/y]';
        $columnsB   = "[Name][College][Course][Birthdate;Birth Date;date;m/d/Y][Class]{$yearcolumn}[DatePaid;App. Fee;date;m/d/y][File8011;POP][Series_No;OR No.]";
        $columns[1] = '[VerifiedDate;Verified;date;m/d/y]';
        $columns[2] = '[SortDate;On-Hold;date;m/d/y]';
        $columns[3] = '[SortDate;For Reupload;date;m/d/y]';
        $columns[4] = '[ApplicationNumber;AppNo][AccountNumber;Acct. No.][SortDate;Processed;date;m/d/y][ExaminationDateTime;Exam Date]';
        $columns[5] = '[SortDate;Denied;date;m/d/y]';
        $columns[6] = '[Series_No;ORNumber][Date_Trans;Date;date;m/d/Y][Particular][AccountName][Amount;;money;columnclass=>text-right][ApplNo]';  
        $columns[7] = '[ReUploadDate;Reupload;date;m/d/y][SortDate;Uploaded;date;m/d/y]';        
        $columns[8] = '[SortDate;Verified;date;m/d/y][ExaminationDate;Exam Date;date;m/d/y]';
        $columns[9] = '[FreeCETDate;CET Date;date;m/d/y h:i A]';
        $columnsa   = '[actions]';
    

        // top button commands, modify parameters if necessar
        $sqlget = APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicationV2 '[@primarykeyvalue]'";
        $sqlsave = '';
        for ($t=1; $t<10; $t++) {
            if (isset($this->titles["t$t"])) {
                if ($this->opener == 't6') {
                    $columnsA = '';
                    $columnsB = '';
                }
                $cols = "$columns0 $columnsA " . $columns[$t] . " $columnsB $columnsa";
                if ($t == 5)
                    $cols .= '[DeniedReason;Remarks]';
                if (($t < 6) || ($t == 7) || ($t == 8) || ($t==9))
                    $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicantsForExam";
                else
                    $sql = APP_DB_DATABASE. "..Usp_ADMSGetEntranceTestPayments";
                $this->addtopbuttoncommand("t$t", "$sql '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '$t', '[@searchtext]'", $cols,'',($t==6?'Series_No' :''),($t==6?'!' :'R'), $sqlget, $sqlsave);        
            }
        }
        $this->addformfield('', 1, 'ApplicationNumber'    ,0  ,'number', 'Application No.','','',false,true);
        $this->addformfield('', 2, 'SN','','','UE Student No.','','',false,true); 
        $this->addformfield('', 3, 'LastName');      
        $this->addformfield('', 4, 'FirstName');
        $this->addformfield('', 5, 'MiddleName','','','','','',false);
        $this->addformfield('', 6, 'BirthDate','','date','','','');         
        $this->addformfield('', 0, 'WebReferenceNumber','','','Ref. No.','','',false,true);  
        $this->addformfield('', 0, 'Email','','email','','','',false,true); 

        $this->addformfield('', 0, 'CourseDescription','','','Course/Program');  
        $this->addformfield('', 0, 'College'); 
        $this->addformfield('', 0, 'CampusCode');     
        if ($this->canupdate) {
            $semesters = $this->getSemestersArray($this->activerecord['Semester']);
            $this->addformfield('', 0, 'Semester','','select','','','',true,false,false,$semesters);    
        }
        else
            $this->addformfield('', 0, 'Semester');    
        $this->addformfield('', 0, 'CampusCodeDescription','','','Campus');    
        $this->addformfield('', 0, 'Class2','','select','Class', '', '', false, true, false, $this->getclass_array($this->type=='S', false, false, $this->semester));
        $this->addformfield('', 0, 'Age');  
        $this->addformfield('', 0, 'Sex'); 
        $this->addformfield('', 0, 'CivilStatus','','','Civil Status'); 
        $this->addformfield('', 0, 'Address1','','','Present Address');   
        $this->addformfield('', 0, 'ZipCode1','','','ZIP Code'); 
        $this->addformfield('', 0, 'Address2','','','Permanent Address');   
        $this->addformfield('', 0, 'ZipCode2','','','ZIP Code');        
        $this->addformfield('', 0, 'LRN'); 
        $this->addformfield('', 0, 'ESCSchoolNumber','','','ESC School No.');  
        $this->addformfield('', 0, 'ESCNumber','','','ESC No.'); 
        $this->addformfield('', 0, 'QVRNumber','','','QVR No.'); 
        $this->addformfield('', 0, 'Mobile','','','Mobile No.'); 
        $this->addformfield('', 0, 'LastSchoolAttended','','','Last School');
        $this->addformfield('', 0, 'LastSchoolID','','','School ID');  

        if ($this->graduate) {
            $this->addformfield('', 0, 'GraduationYear','','','Year');
            $this->addformfield('', 0, 'GraduationYear2','','','Year');
            $this->addformfield('', 0, 'GraduationYear3','','','Year');
            $this->addformfield('', 0, 'GraduationYear4','','','Year');
            $this->addformfield('', 0, 'PreviousSchool1','','','School');	
            $this->addformfield('', 0, 'PreviousSchool2','','','School');	
            $this->addformfield('', 0, 'PreviousSchool3','','','School');	
            $this->addformfield('', 0, 'PreviousSchool4','','','School');	
            $this->addformfield('', 0, 'PreviousDegree1','','','Degree');		
            $this->addformfield('', 0, 'PreviousDegree2','','','Degree');		
            $this->addformfield('', 0, 'PreviousDegree3','','','Degree');		
            $this->addformfield('', 0, 'PreviousDegree4','','','Degree');		
            $this->addformfield('', 0, 'ContactPerson');
            $this->addformfield('', 0, 'ContactPersonRelationship','','','Relationship');			
            $this->addformfield('', 0, 'ContactAddress','','','Address');			
            $this->addformfield('', 0, 'ContactPersonNumber','','','Contact Number');		
            $this->addformfield('', 0, 'AccountNumber','','','Account Number for Entrance Exam Fee');
            $this->addformfield('', 0, 'DatePaid','','','Date Paid');
            $this->addformfield('', 0, 'Series_No','','','O.R. Number');
        }
  
        $this->addformlayout('', '[WebReferenceNumber;3][LastName;3][FirstName;3][MiddleName;3]');
        $this->addformlayout('', '[ApplicationNumber;2][Class2;4][Email;6]');
        $this->addformlayout('', '[BirthDate;2][Age;2][Sex;2][CivilStatus;2]');
        $this->addformlayout('', '[Semester;2][CampusCodeDescription;2][CourseDescription;8]');
        $this->addformlayout('', '[Address1;8][ZipCode1;2][Mobile;2]');
        $this->addformlayout('', '[Address2;8][ZipCode2;2]');
        if ($this->graduate) {
            $this->addformlayout('','[ContactPerson;4][ContactPersonRelationship;4][ContactPersonNumber;4]');
            $this->addformlayout('','[PreviousDegree1;5][GraduationYear;2] [PreviousSchool1;5]');
            $this->addformlayout('','[PreviousDegree2;5][GraduationYear2;2][PreviousSchool2;5]');
            $this->addformlayout('','[PreviousDegree3;5][GraduationYear3;2][PreviousSchool3;5]');
            $this->addformlayout('','[PreviousDegree4;5][GraduationYear4;2][PreviousSchool4;5]');
            $this->addformlayout('','[AccountNumber;5][DatePaid;2][Series_No;2]'); 
        }
        else
            $this->addformlayout('', '[LastSchoolAttended;8][LastSchoolID;2]'); 

        $this->formclass = '';

        $this->recordselector = false;
        
        $this->primarykeypaddinglength = 0;
    }
    
    function callback_fetchformtitle(&$title, &$icon) {
        $icon = $this->titles[$this->opener][0];
    }
    
    function callback_aftergeneraterowfields($linenumber, &$html, $readonly) {
        if ($this->graduate) {
            if (($linenumber >= 8) && ($linenumber <= 10)) {
                $i = $linenumber-6;
                if ( (trim(($this->activerecord["PreviousSchool$i"])) == '')  && (trim(($this->activerecord["PreviousDegree$i"])) == '') )
                    $html = '';
            }
        }
    }
    
    
    function callback_aftershowform(&$html) {
        global $APP_SESSION;
        global $applicationnumber;
        global $webreferencenumber;
        $applicationnumber = $this->activerecord['ApplicationNumber'];
        $webreferencenumber = $this->activerecord['WebReferenceNumber'];

        if ($APP_SESSION->getCanUpdate())
            $body = '<form class="form-inline">
            <div class="form-group">
              <label for="email">School Name</label>
              <input type="text" class="form-control no-warning" id="SearchSchoolText" autofocus>
            </div>
            <button type="button" class="btn btn-default" id="SearchSchoolButton">Search</button> 
            <button type="button" class="btn btn-success SearchSchoolUE" data-search="UNIVERSITY OF THE EAST">UE</button> 
            <button type="button" class="btn btn-danger SearchSchoolUE" data-search="UNIVERSITY OF THE EAST CALOOCAN">UE Caloocan</button>
            <button type="button" class="btn btn-default SearchSchoolUE" data-search="N/L">Not Listed</button>
            </form>
            <hr><div class="container table-responsive" id="SearchSchoolResults" style="width:100%"></div>';

            echo HTML::modal('SearchSchoolsModal','Search School',$body,'','default');        
    }
    
    function callback_fetchgridtitle(&$title, &$icon, $recordcount) {  
        $title = $this->titles[$this->command][1]; 
        $icon  = HTML::icon($this->titles[$this->command][0]);
    } 
    
    function callback_fetchgridfooter(&$gridfooter, $searchtext, $totalrecords, $selectedrecords) {
        if ($this->opener == 't8')    
            $gridfooter .= 'Note: Verified applicants may select examination date/time.';
    }
    
    
    function callback_fetchformfooterbuttons(&$footer) {
        global $APP_SESSION;
        if ($APP_SESSION->getCanPrint() && (!$this->graduate))
            $footer .= $this->getprintlink($this->activerecord['WebReferenceNumber'],'btn btn-default','Print');
        if ($this->canupdate) {
            if (stripos('t1,t2,t3,t7,t8,t9',$this->opener) !== false) {
                if (trim($this->activerecord['VerifiedBy'])) {
                    $footer .= HTML::submitbutton('Unverify',HTML::icon('remove','Unverify'),'danger verify'); 
                    $footer .= HTML::submitbutton('ResendEmail',HTML::icon('envelope','Resend Email'),'success verify'); 
                }
                else
                    $footer .= HTML::submitbutton('Verify',HTML::icon('check','Verify'),'primary verify'); 
                if (!$this->matchedname) {
                    $footer .= HTML::submitbutton('Approve', HTML::icon('thumbs-up','Approve'), 'success');
                    $footer .= HTML::submitbutton('ReUpload',HTML::icon('upload','For Reupload'), 'warning'); 
                }
                $footer .= HTML::submitbutton('OnHold',HTML::icon('question','Put On-Hold'), 'info'); 
                $footer .= HTML::submitbutton('Deny',HTML::icon('thumbs-down','Deny'), 'danger'); 
            } 
            elseif ($this->opener == 't5') {
                $footer .= HTML::submitbutton('Pending',HTML::icon('inbox','Revert to Pending'), 'primary'); 
            }
            elseif (($this->opener == 't4') && !$this->graduate) {
                if (trim($this->activerecord['BypassUpload']) == 0)
                    $footer .= HTML::submitbutton('BypassUploadButton',HTML::icon('remove','Bypass Upload (On-Campus Admission)','fa','For On-Campus Admission'), 'warning'); 
                else
                    $footer .= HTML::submitbutton('RequireUploadButton',HTML::icon('upload','Require Upload','fa','For On-Campus Admission'), 'danger'); 
                $footer .= HTML::submitbutton('Unapprove','Revert to Pending','danger');
            }
            elseif ($this->opener = 't8') {
                $footer .= HTML::submitbutton('Unverify',HTML::icon('remove','Unverify'),'danger verify'); 
            }

        }

        else {
            $footer .= '<em>Current user has insufficient rights to update status</em>';
        }
        $link = "?to=[@To]&subject=[@Subject]&body=[@Body]";
        $to = $this->activerecord['Email'];
        $body = '[NAME: [@LastName], [@FirstName] [@MiddleName], APPLICATION NO.: [@ApplicationNumber] ACCOUNT NO.: [@BankReferenceNumber]';
        if ($this->activerecord['SN'])
            $body .= ', SN: [@SN]';
        $body .= ']';
        $subject = '';
        Data::replaceparameters($body, $this->activerecord);
        $link = str_replace(array("[@To]","[@Subject]","[@Body]"), array($to, $subject, $body), $link);
        $link = "https://mail.google.com/a/ue.edu.ph/mail/?extsrc=mailto&url=mailto:". urlencode($link);
        $footer.= HTML::linkbutton($link,HTML::icon('envelope',"Compose gmail"),"info pull-right","mailto","_gmail","Compose gmail (for current @ue.edu.ph user only)");
        
    }
    
    
    function callback_aftershowfieldsall(&$form)  {
         global $APP_DBCONNECTION;

         $html = '<hr><b>Uploaded Credentials:</b><br><br>';
         $wrn = $this->activerecord['WebReferenceNumber'];
         $sql = APP_DB_DATABASEADMISSION . "..Usp_OA_GetFiles $wrn";
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

                $html .= '<div class="col-sm-2">';
                $html .= "<img class='img-thumbnail $fpclass' onclick=\"js_zoom('tnfile$fn')\" src='$src' id='tnfile$fn'><br>";
                $html .= "<span class='btn btn-sm btn-info fileicon' title='Zoom' onclick=\"js_zoom('tnfile$fn')\"><i class='glyphicon glyphicon-zoom-in'></i></span>";   
                $html .= " <sub title='Page $page'>P$page</sub><br>";
                $html .= $file['Description'] .'<br>';
                $html .= '<small>'.Data::formatdate($file['Timestamp'],'m/d/Y h:i') . 
                         ' ' . round($file['Size']/1024) . 'Kb</small><br>';
                if ($this->canupdate) {
                    $checked = isset($_POST['files'][$fn]) ? 'checked' : '';
                    $html .= "<div class='checkbox'><label title='Delete to reupload'><input type=checkbox name='files[]' value='$fn' $checked> Delete</label></div>";
                    $html .= $this->getotherfields($fn);
                }
                $html .= '</div>';
                if ($i>5) {
                    $i=0;
                    $html .= '</div><div class="row">';
                }
             }
             $html .= '</div>';

         }
         
         $t = substr($this->opener,1,1);
         $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicantsForExam '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '$t', '$this->primarykeyvalue'";                                                               
         //echo Tools::devonly($sql);
         $results = $APP_DBCONNECTION->execute($sql);
         $status = '';
         if (!Tools::emptydataset($results)) {
             $status = $results[0]['StatusRemarks'];
         }
         $this->matchedname = false;
         if (stripos('t4,t5,t6',$this->opener) === false) {
            $html .= '<hr><div class="row">';
            $html .= HTML::hforminputtext('txtStatus','Current Status:',$status,'Status',false,true,false,0,0,'text','col-sm-10');
            $html .= '</div>';
            $html .= '<div class="row">';
            $html .= HTML::hforminputtext('txtRemarks','Remarks:',@$this->activerecord['txtRemarks'],'Remarks',true,!$this->canupdate,false,0,0,'text','col-sm-10');
            $html .= '</div>';
            if (trim(@$results[0]['MatchedName'])) {
                $html .= HTML::callout('Attention!','Matching application found: ' . utf8_encode($results[0]['MatchedName']) . ', Application No.: ' . $results[0]['MatchedApplicationNumber'],'info');
                $this->matchedname = true;
            }
            $newcourses_array = array();
            if ($this->canupdate) {
                $sql = APP_DB_DATABASEADMISSION . "..Usp_OA_GetCoursesOtherCampus '$this->primarykeyvalue', '$this->type', 1";
                $newcourses_array = Data::getoptionsarray($sql, "CCC","Description");
                $ccc = '';
                $targetcampus = "";
                foreach ($newcourses_array as $key => $value) {
                    if ($value && ($targetcampus == ""))
                        $targetcampus = substr($value,0, stripos($value," "));
                    if (substr($key,2) == $this->activerecord['Course']) {
                        $ccc = $key;
                        break;
                    }
                }
                foreach ($newcourses_array as $key => $value) {
                    $campus = substr($value,0, stripos($value,' '));
                    $newcourses_array[$key] = array($value, $campus);
                }
                $label = ($this->graduate) ? 'Change program to:<span class="hidden prompt">Do you want to edit program?</span>' : "Change Campus ($targetcampus) and/or Program:<span class='hidden prompt'>Do you want to change campus and course?</span>";
                $html .= '<div class="row">';
                $html .= HTML::hformselect('NewCourse',$label,$ccc,$newcourses_array,false,true,false,0,0,'col-sm-10');
                $html .= '</div>';
                if (!$this->graduate) {
                    $html .= '<div class="row">';
                    $html .= HTML::hformradio('BypassUpload','Bypass Uploading of Credentials (On-Campus Admission Only)', isset($_POST['BypassUpload']),false,false,0);
                    $html .= '</div>';
                }

            }
         }
         else {
             $html .= '<hr><div class="row">';
             $html .= HTML::hforminputtext('txtStatus','Current Status:',$status,'Status',false,true,false,0,0,'text','col-sm-8');
             $html .= HTML::hforminputtext('txtATMReferenceNumber','Account Number:',$this->activerecord['BankReferenceNumber'],'Bank Reference Number',false,true,false,0,0,'text','col-sm-4');
             $html .= '</div>';
             if ($this->opener == 't4') {
                 $sql = "Usp_ADMSCheckEnranceTestPayment '{$this->semester}', '{$this->campuscode}', '{$this->activerecord['LastName']}', '{$this->activerecord['FirstName']}', '{$this->activerecord['MiddleName']}'";
                 $payments = $APP_DBCONNECTION->execute($sql);
                 if (Tools::emptydataset($payments)) {
                     $html .= $this->TEXT_NOCET;
                 }
                 else {
                     $html .= '<div class="row">';
                     $i = 0;
                     $size = 4;
                     if (count($payments) < 2 )
                        $size = 8;
                     elseif (count($payments) == 2)
                        $size = 6;
                     foreach ($payments as $payment) {
                        $ordata = 'O.R. No.: ' . $payment['Series_No'] . ' ' . 
                                  'Date: ' . Data::formatdate($payment['Date_Trans']) . ' ' .
                                  'Amount: ' . number_format($payment['Amount'],2) . ' ' .
                                  'Particular: ' . $payment['Particular'];
                        $html .= HTML::hforminputtext("Payment$i",'Entrance Test Payment:',$ordata,'',false,true,false,0,0,'text',"col-sm-$size");
                        $i++;
                        if ($i >= 3) {
                            $html .= '</div><div class="row">';  
                            $i = 0;   
                        }
                     }
                     $html .= '</div>';    
                 }
                 if (trim($this->activerecord['BypassUpload']))
                    $html .= "<h4>NOTE: Uploading of credentials is optional until today.</h4>";
             }
         }
         $form .= $html;
    }
    
    
    function callback_post(&$cancel) {
        global $APP_DBCONNECTION;
        global $APP_SESSION;
        $newstatus = '';
        $postmessage = 'Applicant status updated.';
        if (isset($_POST['Approve'])) {
            $newstatus = '4';
            $postmessage = 'Applicant is approved.';
        }
        elseif (isset($_POST['ReUpload']))
            $newstatus = '3';
        elseif (isset($_POST['Deny'])) {
            $newstatus = '5';
            $postmessage = 'Applicant is denied.';
        }
        elseif (isset($_POST['OnHold'])) {
            $newstatus = '2';
            $postmessage = 'Applicant is put on-hold.';
        }
        elseif (isset($_POST['Pending']))   
            $newstatus = '1';    
        elseif (isset($_POST['SaveSemester']))   
            $newstatus = 'SaveSemester';    
        elseif (isset($_POST['SaveEmail']))   
            $newstatus = 'SaveOtherInfo';    
        elseif (isset($_POST['SaveCCC']))   
            $newstatus = 'SaveCCC';    
        elseif (isset($_POST['SaveClass2']))   
            $newstatus = 'SaveClass2';    
        elseif (isset($_POST['BypassUploadButton']))   
            $newstatus = 'BypassUpload';    
        elseif (isset($_POST['RequireUploadButton']))   
            $newstatus = 'RequireUpload';    
        elseif (isset($_POST['Verify']))  {
            $newstatus = 'Verify';    
            $postmessage = 'Student may now select examination date/time.';
        }
        elseif (isset($_POST['ResendEmail'])) {
            $this->resendemail();
            return;
        }
        elseif (isset($_POST['SaveApplicationFeeDatePaid']))   
            $newstatus = 'SaveApplicationFeeDatePaid';    

        elseif (isset($_POST['Unverify']))   
            $newstatus = 'Unverify';    
        elseif (isset($_POST['Unapprove']))
            $newstatus = 'Unapprove';

        if ($newstatus) {
            $cancel = true;
            $saved = false;
            
            $semester = $this->activerecord['Semester'];
            $sn = $this->activerecord['SN'];
            $email = $this->activerecord['Email'];
            $class2 = $this->activerecord['Class2'];
            $bypassupload = isset($_POST['BypassUpload']) ? 1 : 0;
            
            if ($newstatus == 'SaveSemester') {
                $applicationnumber = $this->activerecord['ApplicationNumber'];
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveSemester '{$this->currentuserid}', $this->primarykeyvalue, $applicationnumber, '$semester'";
            }
            elseif ($newstatus == 'SaveOtherInfo') {
                $applicationnumber = $this->activerecord['ApplicationNumber'];
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveOtherInfo '{$this->currentuserid}', $this->primarykeyvalue, $applicationnumber, '$email'";
            }
            elseif ($newstatus == 'SaveClass2') {
                $applicationnumber = $this->activerecord['ApplicationNumber'];
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveClass '{$this->currentuserid}', $this->primarykeyvalue, $applicationnumber, '$this->type', '$class2'";
            }
            elseif ($newstatus == 'SaveCCC') {
                $applicationnumber = $this->activerecord['ApplicationNumber'];
                $ccc = $this->activerecord['NewCourse'];
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveCampusCollegeCourse '{$this->currentuserid}', $this->primarykeyvalue, $applicationnumber, '$ccc'";
            }
            elseif ($newstatus == 'BypassUpload') {
                $applicationnumber = $this->activerecord['ApplicationNumber'];
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveBypassUpload '{$this->currentuserid}', $this->primarykeyvalue, $applicationnumber, 1";
            }
            elseif ($newstatus == 'RequireUpload') {
                $applicationnumber = $this->activerecord['ApplicationNumber'];
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveBypassUpload '{$this->currentuserid}', $this->primarykeyvalue, $applicationnumber, 0";
            }
            elseif ( ($newstatus == 'Verify') || ($newstatus == 'Unverify') ) {
                $verify = $newstatus == 'Verify' ? 1 : 0;
                $applicationfeedatepaid = Data::formatdate($_POST['ApplicationFeeDatePaid']);
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_VerifyApplication '{$this->currentuserid}', $this->primarykeyvalue, $verify, 0, '$applicationfeedatepaid'";
            }
            elseif ($newstatus == 'SaveApplicationFeeDatePaid') {
                $applicationfeedatepaid = Data::formatdate($_POST['ApplicationFeeDatePaid']);
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveApplicationFeeDatePaid '{$this->currentuserid}', $this->primarykeyvalue, '$applicationfeedatepaid'";
            }
            elseif ($newstatus == 'Unapprove') {
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_UnApproveApplication '{$this->currentuserid}', $this->primarykeyvalue";                
            }
            else {
                $remarks = $this->activerecord['txtRemarks'];
                $files = isset($_POST['files']) ? trim(implode(',', @$_POST['files'])) : '';
                $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveApplicationStatus '{$this->currentuserid}', $this->primarykeyvalue, '$newstatus', '$remarks', '$semester', '$sn', '', 1, 0, '$files', $bypassupload";
            }
            
            $APP_DBCONNECTION->begintransaction();
            $results = $APP_DBCONNECTION->execute($sql);
            $saved = !Tools::emptydataset($results);
            if ($saved) {
                $APP_DBCONNECTION->commit();
                if ($newstatus == 'Verify') {
                    $emailmessage = $this->sendemailexamdate($results[0]) ? 'Email sent to applicant.' : 'Unable to send email to applicant.';
                    $postmessage = "<ul><li>$postmessage</li><li>$emailmessage</li></ul>";
                }
                $redirect = "$this->currentpage/$this->opener/0/0/0/$this->opener"; 
                if ($postmessage)
                    $APP_SESSION->setApplicationMessage($postmessage, true);
                Tools::redirect($redirect); 
            }
            else {
                $APP_DBCONNECTION->rollback();
                echo HTML::alert('Error','Unable to save application ' . ($newstatus == 'SaveSemester' ? 'semester' : 'status') .'!','danger');
                $this->showdebugerror($results,$sql); 
            }
        }
    }
    
    function getbadges() {
        global $APP_DBCONNECTION;
        $badges = array('','','','','','','','','','','','');
        $colors[1] = '';
        $sql= APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicantCredentialsSummary '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type'";
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
        //echo Tools::devonly("<h1>$sql</h1>");
    } 
    
    function callback_fetchdata($name, $primarykeyvalue, $record, $cs, &$value) {
        if (($this->opener=='t3') || ($this->opener == 't7') )   {
            if (($name == 'SortDate') && $record['ReUploadDate']) {
                $date = Data::formatdate($record['ReUploadDate'],'YmdHi');
                if ($date > Data::formatdate($value,'YmdHi'))
                    $value = Data::formatdate($value,'m/d/y') . ' ' . HTML::icon('exclamation','','','Reuploaded ' . $record['ReUploadDate']) . ' *';
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
        if ($name == 'File8011')  {
            $class = trim(@$record['ApplicationFeeDatePaid']) ? 'text-primary' : '';
            $title = Data::formatdate(@$record['ApplicationFeeDatePaid']);
            if ($title)
                $title = "Date Paid: $title";
            $value = $value ? HTML::icon("check $class",'','fa',$title) : '';
        }
    }
    
    function getprintlink($ApplicationID, $class='xs bg-orange', $caption='') {
        $cs = Crypto::GenericChecksum("$ApplicationID") . '/' . mt_rand();
        return HTML::linkbutton("applicationform/$ApplicationID/$cs/0/plain",HTML::icon('print',$caption), $class,'pef','pef');
    }

    function getSemestersArray($semester) {
        global $APP_DBCONNECTION;
        global $APP_SESSION;

        $sql = APP_DB_DATABASEADMISSION .  "..Usp_OA_GetSemesters";
        $sql = "Usp_SS_GetCurrentSemesters '', 1";
        $results = $APP_DBCONNECTION->execute($sql);
        if (!Tools::emptydataset($results)) {
            foreach ($results as $record) {
                $semesters[$record['Semester']] = $record['Semester'];
            }
        }
        if ($semester == '')
            $semester = $APP_SESSION->getPageSemester();
        $semesters[$semester]= $semester;
        return $semesters;
    }

    function callback_aftershowgrid(&$html, $results) {
        global $APP_DBCONNECTION;
        if ($this->type != 'G')
            return;
        $sql = APP_DB_DATABASEADMISSION . "..Usp_OA_GetCoordinators '$this->type'";
        $results = $APP_DBCONNECTION->execute($sql);
        
        $columns['RecordID'] = 'No.';
        $columns['Programs'] = 'Programs';
        $columns['Coordinator'] = 'Coordinator';
        $columns['EmailAddress'] = 'Email Address';
        $content = HTML::datatable('Coordinators',$columns, $results,'','',false);
        $footer = 'To update this list, please send request to the I.T. Department.';
        $html .= HTML::box(HTML::icon('users','Program Coordinators'),$content,$footer);
    }

    function getclass_array($seniorhigh=false, $basiced=false, $gs=false, $semester='')
    {
        $list['F'] = "FRESHMAN";
        if ($basiced) {
            $list['T'] = "TRANSFEREE";
            $list['O'] = 'OLD STUDENT';
        }
        elseif ($gs) {
            $list['T'] = "TRANSFEREE";
        }
        elseif (!$seniorhigh) {
            $list['T'] = "TRANSFEREE";
            $list['D'] = "DEGREE HOLDER";
            $list['C'] = "CROSS REGISTRANT";
        }
        else {
            $list['T'] = "TRANSFEREE - Grade 11";
            $list['S'] = "TRANSFEREE - Grade 12";
        }
        return $list;
    }    

    function resendemail() {
        global $APP_SESSION;
        global $APP_DBCONNECTION;
        $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_VerifyApplication '{$this->currentuserid}', $this->primarykeyvalue, 1, 1";        
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
        $message = "<p><b>Good day, Mr. / Ms. [@Firstname] [@Lastname]!</b></p>
        <p>We are happy to inform you that your application has been approved.</p>
        <p>Kindly log in again on this link https://www.ue.edu.ph/onlineadmission/main.html, and enter your reference number and surname.  Click the button Set Exam Date to choose your entrance test schedule and print the Application Form.</p>
        <p>During your entrance test schedule, kindly note the following reminders:</p>
        <p><ol>
        <li>Be at the Admissions Office at least 30 minutes before your exam time.</li>
        <li>Bring Mongol pencil no. 2 and the Application Form printout.</li>
        </ol>
        </p>
        <p>Thank you and we hope to welcome you to the University of the East</p>
        <p>REFERENCE NUMBER: [@WebReferenceNumber]</p>
        <hr>
        <p>THIS IS IS A SYSTEM GENERATED MESSAGE. DO NOT REPLY</p>";
        $to = $record['Email'];
        $mail = new Email();
        $mail->DemoAddress = false;
        $bcc = '';
        $record['MessageBody'] = $message; 
        return @$mail->sendTemplate(0, $record, $to, $bcc, APP_PRODUCTION, !APP_PRODUCTION);
    }

    function getotherfields($fn) {
        $html = '';
        if ($fn == 8011) {
            $value = Data::formatdate(@$this->activerecord['ApplicationFeeDatePaid'],'Y-m-d');
            $html = '<div class="fileothers"><b>Date Paid:</b>' . HTML::text('ApplicationFeeDatePaid','',$value,'','','','','date') . '</div>';
            $html .= HTML::submitbutton('SaveApplicationFeeDatePaid','Save','default btn-sm');
        }
        return $html;
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
