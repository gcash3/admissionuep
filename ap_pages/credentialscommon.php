<?php
$applicationnumber = '';
$webreferencenumber = '';
class CRUDcredentials extends CSNCRUD  {
    private $semester = '';
    private $campuscode = '';
    public $type = 'C';
    private $titles = array();
    
    
    function __construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage, $type)  {
        $this->type = $type;
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
        $this->titles['t5'] = array('book', 'Applications with Pre-Enrollment Form (PEF)', 'bg-orange');  
        $this->titles['t6'] = array('thumbs-down', 'Denied Applications', 'bg-teal'); 
        $this->titles['t7'] = array('star', 'Enrolled', 'bg-red'); 
        $this->titles['t7'] = array('star', 'Enrolled', 'bg-red'); 
        $this->titles['t8'] = array('star', 'Data Mismatched (UE Student)', 'bg-blue'); 
        $this->titles['t9'] = array('uploaded', 'Applications with Reuploaded Credentials', 'bg-red');         
        
        // set page access
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

        $badges = $this->getbadges();
         
        // add top buttons
        $this->addtopbutton(1, 't1','Pending','inbox','primary',null, $badges[1]);
        $this->addtopbutton(1, 't2','On-Hold','question','info',null, $badges[2]);
        $this->addtopbutton(1, 't3','For Reupload','upload','warning',null, $badges[3]);
        $this->addtopbutton(1, 't4','Approved','thumbs-up','success',null, $badges[4]);     
        $this->addtopbutton(1, 't5','With PEF','book','success',null, $badges[5]);
        $this->addtopbutton(1, 't6','Denied','thumbs-down','danger',null, $badges[6]);  
        $this->addtopbutton(1, 't7','Enrolled','star','success',null, $badges[7]);
        $this->addtopbutton(1, 't8','Mismatched','exclamation-triangle','warning',null, $badges[8]);
        $this->addtopbutton(1, 't9','Uploaded','cloud-upload','warning',null, $badges[9]);
        
        // grid column definitions [columnname;property1;key2=>property2..]
        $columns0   = '[recordselector]';
        $columnsA   = '[ApplicationNumber;AppNo]';
        $columnsB   = '[Name][College][Course][Class][WebReferenceNumber;RefNo][Birthdate;Birth Date;date;m/d/Y][SN]';
        $columns[1] = '[SortDate;Uploaded;date;m/d/y h:m]';
        $columns[2] = '[SortDate;On-Hold;date;m/d/y h:m]';
        $columns[3] = '[SortDate;Date;date;m/d/y h:m]';
        $columns[4] = '[SortDate;Processed;date;m/d/y h:m]';
        $columns[5] = '[SortDate;PEF Date;date;m/d/y][PrintedValid_Until;Validity;date;m/d/y]';
        $columns[6] = '[SortDate;Denied;date;m/d/y h:m]';
        $columns[7] = '[SortDate;Validated;date;m/d/y h:m]'; 
        $columns[8] = '[SN]'; 
        $columns[9] = '[SortDate;Uploaded;date;m/d/y h:m]';
        $columnsa   = '[actions]';
        if ($this->opener == 't8')
            $columnsB   = '[Name][College][Course][Birthdate;Birth Date;date;m/d/Y][DataMismatched;Mismatched]';
        

        // top button commands, modify parameters if necessar
        $sqlsave = '';
        for ($t=1; $t<10; $t++) {
            $cols = "$columns0 $columnsA " . $columns[$t] . " $columnsB $columnsa";
            if ($t == 8) {
                $sqlget  = APP_DB_DATABASEADMISSION . "..Usp_OA_GetDataMismatched '[@primarykeyvalue]'";
                $sqlsave = APP_DB_DATABASEADMISSION . "..Usp_OA_UpdateDataMismatched '$this->currentuserid', '" . APP_MODULENAME . "'";
            }
            else {
                $sqlget = APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicationbyApplicationNumber '[@primarykeyvalue]', '', '', '', 1";
            }
            $this->addtopbuttoncommand("t$t", APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicantCredentials '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '$t', '[@searchtext]'", $cols,'','','R' . ($this->opener=='t8'?';U':''), $sqlget, $sqlsave);        
        }
        $this->addformfield('', 1, 'ApplicationNumber'    ,0  ,'number', 'Application No.','','',false,true);
        $this->addformfield('', 2, 'SN','','','UE Student No.','','',false,true); 
        $this->addformfield('', 3, 'LastName');      
        $this->addformfield('', 4, 'FirstName');
        $this->addformfield('', 5, 'MiddleName','','','','','',false);
        $this->addformfield('', 6, 'BirthDate','','date','','','');         
        $this->addformfield('', 0, 'WebReferenceNumber','','','Ref. No.','','',false,true);  
        $this->addformfield('', 0, 'Email','','email','','','',false,true); 

        
        if ($this->opener == 't8') {
            $this->addformfield('', 0, 'Father','','','','','',false,true);
            $this->addformfield('', 0, 'Mother','','','','','',false,true); 
            $this->addformfield('', 0, 'CurrentLastName','','','Lastname','','',false,true);      
            $this->addformfield('', 0, 'CurrentFirstName','','','Firstname','','',false,true);  
            $this->addformfield('', 0, 'CurrentMiddleName','','','Middlename','','',false,true);  
            $this->addformfield('', 0, 'CurrentFather','','','Father','','',false,true);
            $this->addformfield('', 0, 'CurrentMother','','','Mother','','',false,true);  
            $this->addformfield('', 0, 'CurrentBirthDate','','date','Birthdate','','',false,true);   
        }
       
        $this->addformfield('', 0, 'CourseDescription','','','Course/Program');  
        $this->addformfield('', 0, 'College'); 
        $this->addformfield('', 0, 'CampusCode');     
        $this->addformfield('', 0, 'Semester');    
        $this->addformfield('', 0, 'CampusCodeDescription','','','Campus');    
        $this->addformfield('', 0, 'ClassDescription','','','Class');
        $this->addformfield('', 0, 'Age');  
        $this->addformfield('', 0, 'Sex'); 
        $this->addformfield('', 0, 'Address1','','','Present Address');   
        $this->addformfield('', 0, 'ZipCode1','','','ZIP Code'); 
        $this->addformfield('', 0, 'PreferredSchedule','','text','Schedule');
        $this->addformfield('', 0, 'NSTP');
        $this->addformfield('', 0, 'PaymentMode','','','Payment Mode');
        $this->addformfield('', 0, 'ZeroDownPayment','','','Zero Down Payment');
        $this->addformfield('', 0, 'Strand','','','G12 Strand'); 
        $this->addformfield('', 0, 'G12GWA1','','','G12 GWA1');
        $this->addformfield('', 0, 'G12GWA2', '', '','G12 GWA2'); 
        $this->addformfield('', 0, 'HS_English','','','G10 English');
        $this->addformfield('', 0, 'HS_Math','','','G10 Math'); 
        $this->addformfield('', 0, 'HS_Science','','','G10 Science');  
        $this->addformfield('', 0, 'HS_Filipino','','','G10 Filipino'); 
        $this->addformfield('', 0, 'HS_AP','','','G10 AP'); 
        $this->addformfield('', 0, 'HS_GWA','','','G10 GWA'); 

        $this->addformfield('', 0, 'LRN'); 
        $this->addformfield('', 0, 'ESCSchoolNumber','','','ESC School No.');  
        $this->addformfield('', 0, 'ESCNumber','','','ESC No.'); 
        $this->addformfield('', 0, 'QVRNumber','','','QVR No.'); 

        $this->addformfield('', 0, 'BenefitType','','','Honor Student, Rank');
        $this->addformfield('', 0, 'GraduateNos','','','No. of Graduates');
        $this->addformfield('', 0, 'Mobile','','','Mobile No.'); 
        $this->addformfield('', 0, 'hr','<hr>','html');  

        $this->addformfield('', 0, 'LastSchoolAttended','','','Last School');
        $this->addformfield('', 0, 'LastSchoolID','','','School ID');

        $this->addformfield('', 0, 'TRANSTOTFAILGRADE', 0, 'text', 'Total Failing Grade');
        $this->addformfield('', 0, 'TRANSINCOMPLETE', 0, 'text', 'Incomplete');
        $this->addformfield('', 0, 'TRANSDROPPED', 0, 'text', 'Dropped');	
        $this->addformfield('', 0, 'TRANSCREDITEDUNITS', 0, 'text', 'Credited Units','','money');	    
        $this->addformfield('', 0, 'TRANSGWAGPA', 0, 'text', 'GWA/GPA','','money');	            

        if ($this->opener == 't8') {
            $this->addformfield('', 0, 'h1','<h3>Current UE Record:</h3>','html');
            $this->addformfield('', 0, 'h2','<h3>Application Record:</h3>','html');
            $this->addformlayout('', '[ApplicationNumber;2][SN;2][WebReferenceNumber;2][Email;6]');
            $this->addformlayout('', '[h1;0]'); 
            $this->addformlayout('', '[CurrentMother;6][CurrentFather;6]');
            $this->addformlayout('', '[CurrentLastName;3][CurrentFirstName;3][CurrentMiddleName;3][CurrentBirthDate;3]');                                                                                                 
            $this->addformlayout('', '[h2;0]');
            $this->addformlayout('', '[[Mother;6][Father;6]'); 
            $this->addformlayout('', '[LastName;3][FirstName;3][MiddleName;3][BirthDate;3]');
            
        }
        else {
            $this->addformlayout('', '[ApplicationNumber;3][LastName;3][FirstName;3][MiddleName;3]');
            $this->addformlayout('', '[WebReferenceNumber;2][ClassDescription;2][Email;4][BirthDate;2][Age;1][Sex;1]');
            $this->addformlayout('', '[Address1;8][ZipCode1;2][Mobile;2]');
            $this->addformlayout('', '[hr;0]'); 
            if ($this->type == 'C')
                $this->addformlayout('', '[Strand;2][G12GWA1;2][G12GWA2;2][BenefitType;2][GraduateNos;2]');
            elseif ($this->type == 'S') {
                $this->addformlayout('', '[HS_English][HS_Math][HS_Science][HS_Filipino][HS_AP][HS_GWA]');
                $this->addformlayout('', '[LRN][ESCSchoolNumber][ESCNumber][QVRNumber]');
                $this->addformlayout('', '[BenefitType;2][GraduateNos;2]'); 
            }
            $this->addformlayout('', '[LastSchoolAttended;8][LastSchoolID;2]');
            $this->addformlayout('', '[hr;0]');
            $this->addformlayout('', '[Semester;2][CampusCodeDescription;2][CourseDescription;8]');
            $this->addformlayout('', '[SN][PreferredSchedule][PaymentMode][ZeroDownPayment]' . ($this->type=='C' ? '[NSTP]' :'')); 

            if ($this->opener == 't7') {
                $this->addformlayout('', '[hr;0]'); 
                $this->addformlayout('', '[TRANSTOTFAILGRADE;2][TRANSINCOMPLETE;2][TRANSDROPPED;2][TRANSCREDITEDUNITS;2][TRANSGWAGPA;2]');
            }
        }    
        $this->formclass = '';

        $this->recordselector = false;
        
        $this->primarykeypaddinglength = 0;
    }
    
    function callback_fetchformtitle(&$title, &$icon) {
        $icon = $this->titles[$this->opener][0];
    }
    function callback_aftershowform(&$html) {
        global $APP_SESSION;
        global $applicationnumber;
        global $webreferencenumber;
        $applicationnumber = $this->primarykeyvalue;
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
        $title = $this->titles[$this->command][1] . ($recordcount > 100 ? '' :  ' <small>first 100 only</small>'); 
        $icon  = HTML::icon($this->titles[$this->command][0]);
    } 
    
    
    function callback_fetchformfooterbuttons(&$footer) {
        if ($this->opener == 't8') {
            if ($this->command == $this->updatecommand)
                $footer.= HTML::link('#','Paste UE Record','Copy and paste UE record to current application','btn btn-warning','pasteuerecord') . ' '; 
        }
        else {
            if ($this->canupdate) {
                if (stripos('t1,t2,t3,t9',$this->opener) !== false) {
                    $footer .= HTML::submitbutton('Approve','Approve', 'success');
                    $footer .= HTML::submitbutton('ReUpload','For Reupload', 'warning');  
                    $footer .= HTML::submitbutton('Deny','Deny', 'danger');
                }
                if (stripos('t4,t1,t6',$this->opener) !== false) {
                    if ($this->opener <> 't1')
                        $footer .= HTML::submitbutton('Pending','Set Status to Pending', 'warning'); 
                }  
                if (stripos('t1,t3,t4,t6',$this->opener) !== false)  {
                    $footer .= HTML::submitbutton('OnHold','Put On-Hold', 'info'); 
                }                
            }
            else {
                $footer .= '<em>Current user has insufficient rights to update status</em>';
            }
            if ($this->opener == 't5') {
                $footer .= $this->getprintlink($this->activerecord['ApplicationNumber'],'btn btn-success','View PEF');
                //$footer.= HTML::link('#','View PEF','View PEF','btn btn-success','viewpef','pef');
            }
        }
        $link = "?to=[@To]&subject=[@Subject]&body=[@Body]";
        $to = $this->activerecord['Email'];
        $body = '[NAME: [@LastName], [@FirstName] [@MiddleName], APPLICATION NO.: [@ApplicationNumber]';
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
         if ($this->opener == 't8') {
            if ($this->command == $this->updatecommand) {
                $form .= HTML::alert('','Note: This operation will update both UE current record and application. Check data before saving.','info',false);
            }
            return;   
         }
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
                $html .= '</div>';
                if ($i>5) {
                    $i=0;
                    $html .= '</div><div class="row">';
                }
             }
             $html .= '</div>';

         }
         $t = substr($this->opener,1,1);
         $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicantCredentials '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', '$t', '$this->primarykeyvalue'";                                                               
         $results = $APP_DBCONNECTION->execute($sql);
         $status = '';
         if (!Tools::emptydataset($results)) {
             $status = $results[0]['StatusRemarks'];
         }
         
         if (stripos('t5,t7',$this->opener) === false) {
             $html .= '<hr><div class="row">';
             $html .= HTML::hforminputtext('txtStatus','Current Status:',$status,'Status',false,true,false,0,0,'text','col-sm-10');
             $html .= '</div>';
             $html .= '<div class="row">';
             $html .= HTML::hforminputtext('txtRemarks','Remarks/Message to Student:',@$this->activerecord['txtRemarks'],'Remarks to student',true,!$this->canupdate,false,0,0,'text','col-sm-10');
             $html .= '</div>';
             
         }
         else {
             $html .= '<hr><div class="row">';
             $html .= HTML::hforminputtext('txtStatus','Current Status:',$status,'Status',false,true,false,0,0,'text','col-sm-6');
             $html .= '</div>';

         }
         $form .= $html;
    }
    
    
    function callback_post(&$cancel) {
        global $APP_DBCONNECTION;
        global $APP_SESSION;
        $newstatus = '';

        if (isset($_POST['SaveTDC'])) {
            $this->saveTDC();
            return;
        }
        if (isset($_POST['Approve']))
            $newstatus = '4';
        elseif (isset($_POST['ReUpload']))
            $newstatus = '3';
        elseif (isset($_POST['Deny'])) 
            $newstatus = '6';
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
                
            $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_SaveCredentialStatus '{$this->currentuserid}', $this->primarykeyvalue, '$newstatus', '$remarks', '$wrn', '$semester', '$sn'";
            $results = $APP_DBCONNECTION->execute($sql);
            $saved = !Tools::emptydataset($results);
            if ($saved) {
                $redirect = "$this->currentpage/t$newstatus/0/0/0/t$newstatus"; 
                Tools::redirect($redirect); 
            }
            else {
                $APP_SESSION->setApplicationMessage('Unable to save application status!',false,'danger');
                $this->showdebugerror($results,$sql); 
            }
        }
    }
    
    function getbadges() {
        global $APP_DBCONNECTION;
        $badges = array('','','','','','','','','','');
        $colors[1] = '';
        $sql= APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicantCredentialsSummary '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type'";
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
        return $badges;
    }
    
    
    function callback_fetchsqlcommand($commandtype, &$sql) {
    } 
    
    function callback_fetchdata($name, $primarykeyvalue, $record, $cs, &$value) {
        if ($name == 'PrintedValid_Until') {
            if (date('Ymd') > Data::formatdate($value,'Ymd'))
                $value = Data::formatdate($value,'m/d/y') . ' ' . HTML::icon('exclamation','','','Expired PEF');
        }
        elseif ($this->opener=='t3')   {
            if (($name == 'SortDate') && $record['ReUploadDate']) {
                $date = Data::formatdate($record['ReUploadDate'],'Ymd hi');
                if ($date > Data::formatdate($value,'Ymd hi'))
                    $value = Data::formatdate($value,'m/d/y h:s') . ' ' . HTML::icon('exclamation','','','Reuploaded ' . $record['ReUploadDate']);
            }
        }
        if (($name == 'Name') && @$record['UEStudent'])  {
            $value .= ' <sup class="text-danger" title="UE Student">UE</sup>';
        }
    }
    
    function getprintlink($ApplicationID, $class='xs bg-orange', $caption='') {
        $cs = Crypto::GenericChecksum("$ApplicationID") . '/' . mt_rand();
        return HTML::linkbutton("pef/$ApplicationID/$cs/0/plain",HTML::icon('print',$caption), $class,'pef','pef');
    }

    
    function callback_beforegeneratefield(&$field, &$html, &$cancel, $readonly) {
        if ($field->name == 'ZeroDownPayment')
            $field->value = $field->value ? 'Yes' : 'No';
        elseif ($field->name == 'PaymentMode') 
            $field->value = stripos('FC', $field->value) !== false ? 'Full Payment' : (substr($field->value,1) .  ' Installments');
        elseif ($field->name == 'BenefitCode')
            $field->value = 'xxx' . $field->value;
        elseif ($field->name == 'BenefitType') {
            $list = array('Rank 1', 'Rank 2', 'Top 3-10', 'Valedictorian', 'Salutatorian', 'Not Appplicable');
            $value = @$list[stripos("12TVSN", $field->value)];
            if ($value)
                $field->value = $value;
        }
    }
    
    


    // TDC Related functions
    function callback_afterexecute($command, $sql, &$results) {
        global $APP_DBCONNECTION;
        if ( ($this->opener == 't7') && ($this->command == $this->readcommand) ) {
            if (@$results[0]['Class'] == 'T') {
                $sql = "Usp_ADMSGetTDCReference " . @$results[0]['ApplicationNumber'];
                $resultsTDC = $APP_DBCONNECTION->execute($sql);
                if (!Tools::emptydataset($resultsTDC)) {
                    $results[0]['TRANSTOTFAILGRADE'] = round($resultsTDC[0]['TRANSTOTFAILGRADE'] + 0, 0);
                    $results[0]['TRANSINCOMPLETE'] = round($resultsTDC[0]['TRANSINCOMPLETE'] + 0, 0);
                    $results[0]['TRANSDROPPED'] = round($resultsTDC[0]['TRANSDROPPED'] + 0, 0);
                    $results[0]['TRANSCREDITEDUNITS'] = round($resultsTDC[0]['TRANSCREDITEDUNITS'] + 0, 2);
                    $results[0]['TRANSGWAGPA'] = round($resultsTDC[0]['TRANSGWAGPA'] + 0, 2);
                }
            }
        }
    } 

    function callback_aftergeneraterowfields($linenumber, &$html, $readonly) {
        if (($linenumber == 10) && ($this->activerecord['Class'] == 'T') ) {
            $button = "<div class='col-lg-2 TDC'>";
            if ($this->canupdate) {
                $button .= HTML::button('EditTDC',HTML::icon('pencil'),'danger btn-sm');
                $button .= HTML::submitbutton('SaveTDC',HTML::icon('save'),'success btn-sm tdcsave hidden');
                $button .= HTML::button('DeleteTDC',HTML::icon('remove','','','Set values to zero'),'danger btn-sm tdcsave hidden');
                $button .= HTML::resetbutton('ResetTDC',HTML::icon('undo','','','Undo all changes'),'default btn-sm tdcsave hidden');
                $button .= HTML::button('CancelTDC',HTML::icon('stop','','','Cancel'),'default btn-sm tdcsave hidden');
            }
            else {
                $button .= HTML::icon('exclamation','Insufficient Access to edit these fields.');
            }
            $button .= '</div>';
            $html = substr($html, 0, strlen($html)-6) . $button . '</div>';
        }        
    }
    

    function saveTDC() {
        global $APP_SESSION;
        global $APP_DBCONNECTION;

        $class = $this->activerecord['Class'];
        $totfail = $this->activerecord['TRANSTOTFAILGRADE'] + 0;
        $incomplete = $this->activerecord['TRANSINCOMPLETE'] + 0;
        $drop  = $this->activerecord['TRANSDROPPED'] + 0;
        $credit = round($this->activerecord['TRANSCREDITEDUNITS'],2);
        $gwagpa = round($this->activerecord['TRANSGWAGPA'] + 0,2);
        $earned = '';
        $yrgrad = '';
        $crosscourse = '';
        $applno = $this->activerecord['ApplicationNumber']; 

        $sql = "Usp_ADMSUpdateTDCReference $class, $totfail, $incomplete, $drop, $credit, $gwagpa, '$earned', '$yrgrad', '$crosscourse', $applno";
        $results = $APP_DBCONNECTION->execute($sql);
        
        if (!Tools::emptydataset($results)) {
            $APP_SESSION->setApplicationMessage('TDC values recorded!');
        }
        else {
            $APP_SESSION->setApplicationMessage('Unable to update TDC values!' . $results,false,'danger');
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        die(0);
    }

    function callback_beforeshowsearchdiv(&$searchdiv, $tag) {
        if ($this->opener == 't7') {
            
            $buttons = "<div class='col-lg-4'>";
            $buttons .= "<span class='badge bg-red classfilter' data-filter='Freshmen: '>Freshmen</span> ";
            $buttons .= "<span class='badge bg-blue classfilter' data-filter='Transferee: '>Transferee</span> ";
            $buttons .= "<span class='badge bg-orange classfilter' data-filter='Degreeholder: '>Degree Holder</span> ";
            $buttons .= "<span class='badge bg-green classfilter' data-filter=''>All</span> ";
            $buttons .= '</div>';
            $searchdiv = str_replace($tag, $buttons, $searchdiv) . $tag;
        }
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

