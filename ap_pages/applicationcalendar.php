<?php
class CRUDSystem extends CSNCRUD  {
    private $semesters_array = array();
    private $semester = '';
    private $campuscode = '';

    function initialize() {
        global $APP_SESSION;

        $this->semester = $APP_SESSION->getPageSemester();
        $this->campuscode = $APP_SESSION->getCampusCode();

        $this->semesters_array = Data::getoptionsarray("Usp_SS_GetCurrentSemesters","Semester","Description");
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       
        
        $this->addtopbutton(1, 'ac','Start/End;Application Date Configuration','calendar','success');
        $this->topbuttons['ac']->gridsortable = false;

        $d = 'date;Y-m-d';
        $columns1 = "[ID;Group ID][Campus][Type][StartDate;Start of Application;$d][EndDate;End of Application;$d][Status]";
        $columnsa = '[actions]';

        $sqlprefix = APP_DB_DATABASEADMISSION . "..Usp_AP_GetApplicationCalendar '$this->currentuserid', '$this->semester', '$this->campuscode'";
        $this->addtopbuttoncommand('ac', $sqlprefix, "$columns1 $columnsa",'Enrollment Date','ID','RU', "$sqlprefix, '[@primarykeyvalue]'",APP_DB_DATABASEADMISSION."..Usp_AP_SaveApplicationCalendar '$this->currentuserid'"  );        

        $this->addformfield('ac', 1, 'ID','' ,'text', 'Group ID','','',false,true);
		$this->addformfield('ac', 0, 'Semester','', 'select','','','',true,true,false,$this->semesters_array);
        $this->addformfield('ac', 0, 'Campus','' ,'', '','','',false,true);
        $this->addformfield('ac', 0, 'Type','' ,'', 'Level','','',false,true);

        $this->addformfield('ac', 2, 'StartDate','','date','Start Date');
        $this->addformfield('ac', 3, 'EndDate','','date','Last Day');
 
		$this->addformlayout('ac', '[Semester;5][ID;2][Campus;2][Type;3]');
        $this->addformlayout('ac', '[StartDate;2][EndDate;2]');
        $this->formclass = '';

        $this->recordselector = false;
		$this->searchtextdivsize = 0;

        $this->primarykeypaddinglength = 0;
        $this->addgridformpost = true;
        $this->gridsortable = false;
    }

    function callback_fetchdata($fieldname, $primarykeyvalue, $record, $cs, &$value) {
    }

    function callback_beforeshowfieldsall(&$form) {
        if (stripos($this->activerecord['Status'],'closed') !== false)  {
            $form .= HTML::alert('Attention', 'Application for this group is already closed!','warning');
        }
    }

    function callback_fetchdatarow($primarykeyvalue, &$record) {
        if (substr($record['Status'],0,1) == 'O')
            $record['rowclass'] = 'text-success';
        elseif (stripos($record['Status'],'closed') !== false)
            $record['rowclass'] = 'text-danger';

        if ($record['ReadOnly'])
            @$record['rowclass'] .= ' text-muted';
    }

    function callback_actionbuttons($primarykeyvalue, $record, &$actions) {
        if ($record['ReadOnly']) {
            $actions = HTML::link("changecampus/changecampus/0/0/0/changecampus/$this->currentpage",'<small>Switch Campus</small>');
        }
    }
    

    

    function callback_fetchsqlcommand($command, &$sql) {
        //echo "<h1>$command $sql</h1>";
    }

    function callback_fetchgridfooter(&$gridfooter, $searchtext, $totalrecords, $selectedrecords) {
        if ($this->command == 'ac') {
            if (($totalrecords == 0) && $this->cancreate) {
                $gridfooter .= HTML::submitbutton('Initialize','Initialize Default Groups','danger');
                return;
            }
            $gridfooter .= HTML::icon('lightbulb-o','Inform ITD to create or edit enrollment Group ID.');
            return;
        }

    }


    function callback_validateform($command, &$alertmessage) {
    }   

    function callback_post(&$cancel) {
        global $APP_DBCONNECTION;
        global $APP_CURRENTPAGE;
        global $APP_SESSION;
        if (isset($_POST['Initialize'])) {
            $cancel = true;
            $sql = APP_DB_DATABASEADMISSION . "..Usp_AP_InitializeApplicationCalendar '$this->currentuserid', '$this->semester'";
            $results = $APP_DBCONNECTION->execute($sql);
            if (Tools::emptydataset($results)) {
                echo HTML::alert('Error','Unable to initialize enrollment groups!');
                echo Tools::devonly("<pre>sql: $sql<br>" . print_r($results,true) . '</pre>');
            }
            else {
                $APP_SESSION->setApplicationMessage('Application calendar initialized. Please edit dates.');
                Tools::redirect($APP_CURRENTPAGE . '/' . $this->command);
                die();
            }
            return;
        }
    }
    
	
    

}

$system = new CRUDSystem('Enrollment Calendar','ID','ac', $APP_SESSION->getuserid(), $APP_CURRENTPAGE);
$system->run();

?>