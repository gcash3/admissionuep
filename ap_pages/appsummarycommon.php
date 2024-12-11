<?php
class CRUDApplicants extends CSNCRUD  {
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
        
        $this->titles['type1'] = array('edit', 'Without Application Number');
        $this->titles['type2'] = array('thumbs-up', 'With Application Number');  
        $this->titles['type3'] = array('upload', 'Application with Updated/Uploaded Credentials');
        $this->titles['type0'] = array('list', 'All Applications');  
        
        // set page access
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

         
        // add top buttons
        $this->addtopbutton(1, 'type1','Type 1','edit','info');
        $this->addtopbutton(1, 'type2','Type 2','thumbs-up','warning'); 
        $this->addtopbutton(1, 'type3','Type 3','upload','success');        
        $this->addtopbutton(1, 'type0','All','list','success');
        // grid column definitions [columnname;property1;key2=>property2..]
        $columns0 = '[recordselector]';
        $columns1 = '[ID;Link][CampusCode;Campus][Reference;College][Course][Description][Total;;columnclass=>text-right]';
        $columnsa = '[actions]';

        // top button commands, modify parameters if necessar
        $sqlget = APP_DB_DATABASEADMISSION . "..Usp_OA_GetApplicationsSummaryHeader '$this->currentuserid', '$this->semester', '[@primarykeyvalue]', '[@searchtext]'";
        for ($t=0; $t<4; $t++)
            $this->addtopbuttoncommand("type$t", APP_DB_DATABASEADMISSION. "..Usp_OA_GetApplicationsSummary '$this->currentuserid', '$this->semester', '$this->campuscode', '$this->type', $t, '[@searchtext]'", "$columns0 $columns1 $columnsa",'Applicants Summary','ID','R', $sqlget);        

        $this->addformfield('', 0, 'ID'                   ,0  ,'number', '','','',false,false);
        $this->addformfield('', 0, 'Semester'             ,0  ,'text',   '','','',false,false);        
        $this->addformfield('', 0, 'SemesterDescription'  ,0  ,'text',   'Semester','','',false,false);
        $this->addformfield('', 0, 'College'              ,0  ,'text',   '','','',false,false);
        $this->addformfield('', 0, 'CollegeDescription'   ,0  ,'text',   'College / Department','','',false,false);
        $this->addformfield('', 0, 'Course'               ,0  ,'text',   '','','',false,false);
        $this->addformfield('', 0, 'CourseDescription'    ,0  ,'text',   'Course / Program','','',false,false);
        $this->addformfield('', 0, 'CampusCode'           ,0  ,'text',   '','','',false,false);
        $this->addformfield('', 0, 'Campus'               ,0  ,'text',   '','','',false,false);
        $this->addformfield('', 0, 'Filter'               ,0  ,'text',   '','','',false,false); 
        $this->addformfield('', 0, 'FilterDescription'    ,0  ,'text',   'Filter','','',false,false); 
       
        $this->addformlayout('', '[SemesterDescription;3][Campus;2][CollegeDescription;7]');
        $this->addformlayout('', '[CourseDescription]');
        $this->addformlayout('', '[FilterDescription]');
        $this->formclass = '';

        $this->recordselector = false;

        //$this->searchtextdivsize = 0;
        $this->searchtexttitle = 'Applied Date';
        $this->primarykeypaddinglength = 0;
    }
    
    function callback_fetchformtitle(&$title, &$icon) {
        $icon = $this->titles[$this->opener][0];
    }
    function callback_aftershowform(&$html) {
        global $APP_DBCONNECTION;
        $sql = "Usp_OA_GetApplicationsSummaryList '$this->currentuserid', '$this->semester', '$this->primarykeyvalue', '$this->searchtext'";
        $body = "";
        $title = HTML::icon("list") . ' Listing as of ' . $this->searchtext;
        $footer = '';
        $body .= '<em>Sorry. This section is not yet available!</em>';
        $html .= HTML::box($title,$body, $footer);

    }
    
    function callback_fetchgridtitle(&$title, &$icon, $recordcount) {  
        $title = $this->titles[$this->command][1];
        $icon  = HTML::icon($this->titles[$this->command][0]);
    } 
    
    function callback_fetchdatarow($primarykeyvalue, &$row) {
        if ($row['Description'] == 'Campus Total') {
            $row['rowclass'] = 'success';
            $row['Description'] = "<b>".$row['Description']."</b>";
        }
        elseif (stripos($row['Description'],'total') !== false) {
            $row['rowclass'] = 'info';
            $row['Description'] = "<b>".$row['Description']."</b>"; 
        }
    }
}

?>
