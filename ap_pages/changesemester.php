<?php

class CRUDValidateRegistration extends CSNCRUD {
    private $columns2 = '[Semester][Description]';
    private $semester = '';
            
    function initialize() {
        global $APP_SESSION;
        $this->semester = $APP_SESSION->getpagesemester();
        $this->searchtextdivsize = 0;
        $this->searchtextrequired = true;
        $this->showtopbuttons = false;
        $this->addtopbutton(1, 'changesemester', 'Change Sem;Change Semester','exchange' ,'warning', $this->opener);
        $this->addtopbuttoncommand('changesemester' , "Usp_SS_GetCurrentSemesters '', 1", "$this->columns2",'Semester','Semester','R',"Usp_AP_GetSemester '$this->currentuserid', '[@primarykeyvalue]'");        
        $this->recordselector = false;
        $this->primarykeypaddinglength = 0;    
        $this->showactionbuttons = false;
    }

   
    function callback_beforeshowform(&$html) {
        global $APP_SESSION;
        $APP_SESSION->setpagesemester($this->primarykeyvalue);
        if (isset($_GET['_p7']))
            Tools::redirect($_GET['_p7']);
        else
            Tools::redirect('dashboard');    
    }

    function callback_fetchurlextra($record, &$urlextra) {
        if (trim($this->opener2))
            $urlextra = $this->opener2;
    }    
    

}

$system = new CRUDValidateRegistration('ChangeSem','SN','changesemester', $APP_SESSION->getuserid(), $APP_CURRENTPAGE);
$system->run();

?>