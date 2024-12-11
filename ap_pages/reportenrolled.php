<?php
class ReportPage extends CSNCRUD  {
    private $semester = '';
    private $campuscode = '';
    private $titles = array();
    private $columns = '';
    private $sql = '';
    
    
    function initialize() {
        global $APP_SESSION;
        $this->semester = $APP_SESSION->getPageSemester();
        $this->campuscode = $APP_SESSION->getCampusCode();
        
        $this->titles['T'] = array('table', 'College Freshmen Enrollment');
        $this->titles['S'] = array('table', 'SHS - Grade 11 Enrollment');  
        
        // set page access
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

         
        // add top buttons
        $this->addtopbutton(1, 'T','Tertiary','university','danger');
        $this->addtopbutton(1, 'S','SHS','child','info'); 
        // grid column definitions [columnname;property1;key2=>property2..]
        $columns0 = '';
        $columns1 = '[Semester][Campus;Campus][Course][AppliedThisDate;Applied This Date_@ASOF_][AppliedTotal;Applied Total][ApprovedThisDate;Approved This Date][ApprovedTotal;Approved Total][PEFThisDate;Issued PEF This Date_@ASOF_][PEFTotal;Issued PEF Total][EnrolledThisDate;Enrollment This Date][EnrolledTotal;Enrollment Total][LastYearFinalTotalEnrolled;(Final Data) Last Year Enrolled]';
        $columnsa = '';

        $this->columns = $columns1;

        // top button commands, modify parameters if necessar
        $this->addtopbuttoncommand("T", "Usp_AP_ADMS_GetEnrolled '$this->currentuserid', '$this->semester', '$this->campuscode', '[ABCDEFLN]', 'F', '[@searchtext]'", "$columns0 $columns1 $columnsa",'','',' ');        
        $this->addtopbuttoncommand("S", "Usp_AP_ADMS_GetEnrolled '$this->currentuserid', '$this->semester', '$this->campuscode', 'S', 'F', '[@searchtext]'", "$columns0 $columns1 $columnsa",'','',' ');                

        $this->recordselector = false;

        //$this->searchtextdivsize = 0;
        $this->searchtexttitle = 'As of Date';
        $this->searchtextdivsize = 3;
        $this->primarykeypaddinglength = 0;
    }
    
    
    function callback_fetchgridtitle(&$title, &$icon, $recordcount) {  
        $title = $this->titles[$this->command][1];
        $icon  = HTML::icon($this->titles[$this->command][0]);
    } 
    
    function callback_fetchdatarow($primarykeyvalue, &$row) {
        if ($row['RecordType'] == '0')
            $row['rowclass'] = 'info';
        elseif (($row['RecordType'] == '2') && ($row['Summary'] == 0) )
            $row['rowclass'] = 'warning bold';
        elseif ($row['RecordType'] == '3')
            $row['rowclass'] = 'danger bold';
    }

    function callback_aftershowgrid(&$html, $results) {
        global $APP_DBCONNECTION;

        $asof = Data::formatdate(@$results[0]['CutOff']);
        if ($asof) 
            $asof = " ($asof)";
        $lastasof = Data::formatdate(@$results[0]['LastCutOff']);
        if ($lastasof) 
            $lastasof = " ($lastasof)";
        
        $summary = $APP_DBCONNECTION->execute("$this->sql, 1");
        $table = '';
        $columns = $this->stringtoarray(true, $this->columns);
        $table = HTML::datatable('Summary', $columns, $summary);
        $html .= HTML::box('Summary Report on Freshmen Enrollment', $table, '', 'success');

                
        $html = str_replace(array('_@ASOF_','_@LASTASOF_'), array($asof,$lastasof), $html);
        
    }

    function callback_fetchsqlcommand($command, &$sql) {
        if ($command != 'getrecords')    
            return;
        $this->sql = $sql;
    }
    
    




}

if ($APP_SESSION->getPageSemester() == '') {
    echo  HTML::alert('','Please select semester first!','danger',false);
    return;
}
$system = new ReportPage('Applicants','ID','T', $APP_SESSION->getuserid(), $APP_CURRENTPAGE);
$system->run();


?>
