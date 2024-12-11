<?php
$system = new CRUDApplicants('Applicant','WebReferenceNumber','c', $APP_SESSION->getuserid(), $APP_CURRENTPAGE);
$system->run();

class CRUDApplicants extends CSNCRUD  {
    private $semester = '';
    private $campuscode = '';
    private $canprint = false;
    
    function __construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage)  {
        parent::__construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage);
    }
    
    function initialize() {
        global $APP_SESSION;
        $this->semester = $APP_SESSION->getPageSemester();
        $this->campuscode = $APP_SESSION->getCampusCode();
        $this->canprint = $APP_SESSION->getCanPrint();

        // set page access
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

        $badges = $this->getbadges();
         
        // add top buttons
        $this->addtopbutton(1, 'c','College','folder','primary',null, $badges[1]);
        $this->addtopbutton(1, 's','SHS','folder','info',null, $badges[2]);
               
        // grid column definitions [columnname;property1;key2=>property2..]
        $columns0   = '[recordselector]';
        $columnsA   = '[WebReferenceNumber;Reference No][DateApplied;Applied;date;m/d/y]';
        $columnsB   = '[Name][College][Course][Birthdate;Birth Date;date;m/d/Y]';
        
        $columnsa   = '[actions]';
    

        // top button commands, modify parameters if necessar
        $sqlget = APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplication '[@primarykeyvalue]'";
        $sqlsave = '';
        $cols = "$columns0 $columnsA $columnsB $columnsa";

        $sql = APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicantsListing";
        $this->addtopbuttoncommand("c", "$sql '$this->currentuserid', '$this->semester', '$this->campuscode', 'c', '[@searchtext]'", $cols,'','','R', $sqlget);        
        $this->addtopbuttoncommand("s", "$sql '$this->currentuserid', '$this->semester', '$this->campuscode', 's', '[@searchtext]'", $cols,'','','R', $sqlget);        
 
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
        $this->addformfield('', 0, 'Semester');    
        $this->addformfield('', 0, 'CampusCodeDescription','','','Campus');    
        $this->addformfield('', 0, 'UserName');
        $this->addformfield('', 0, 'Age');  
        $this->addformfield('', 0, 'Sex'); 
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
  
        $this->addformlayout('', '[WebReferenceNumber;3][LastName;3][FirstName;3][MiddleName;3]');
        $this->addformlayout('', '[ApplicationNumber;2][UserName;2][Email;4][BirthDate;2][Age;1][Sex;1]');
        $this->addformlayout('', '[Address1;8][ZipCode1;2][Mobile;2]');
        $this->addformlayout('', '[Address2;8][ZipCode2;2]');
        $this->addformlayout('', '[LastSchoolAttended;8][LastSchoolID;2]'); 
        $this->addformlayout('', '[Semester;2][CampusCodeDescription;2][CourseDescription;8]');
        $this->formclass = '';

        $this->recordselector = false;
        
        $this->primarykeypaddinglength = 0;
    }
    
    function callback_fetchformfooterbuttons(&$footer) {
        global $APP_SESSION;
        if ($APP_SESSION->getCanPrint())
            $footer .= $this->getprintlink($this->activerecord['WebReferenceNumber'],'btn btn-success','Print');
        
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
                $html .= '</div>';
                if ($i>5) {
                    $i=0;
                    $html .= '</div><div class="row">';
                }
             }
             $html .= '</div>';

         }
         $form .= $html;
    }
    
    
    function callback_post(&$cancel) {
    }
    
    function getbadges() {
        global $APP_DBCONNECTION;
        $badges = array('','','','','','','','','');
        return $badges;
    }
    
    
    function callback_fetchsqlcommand($commandtype, &$sql) {
        //echo Tools::devonly("<h1>$sql</h1>");
    } 
    
 
    function getprintlink($ApplicationID, $class='xs bg-orange', $caption='', $button=true) {
        $cs = Crypto::GenericChecksum("$ApplicationID") . '/' . mt_rand();
        if ($button) 
            return HTML::linkbutton("applicationform/$ApplicationID/$cs/0/plain",HTML::icon('print',$caption), $class,'pef','pef');
        else
            return HTML::link("applicationform/$ApplicationID/$cs/0/plain",HTML::icon('print',$caption),'Print/Download Application Form', $class,'pef','pef');
        
    }

    function callback_actionbuttons($primarykeyvalue, $record, &$actions) {
        if ($this->canprint)
            $actions .= $this->getprintlink($primarykeyvalue,'','',false);
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
