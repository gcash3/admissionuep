<?php
class CRUDSystem extends CSNCRUD implements CSNCRUDeventsall {
	private $columns1 = '[SN][Name][College][Course][LedgerBalance;Balance;money;columnclass=>text-right][LedgerStatus;Status]';
	private $columns2 = '[RecordNumber;RecNo][SN][Name][Course][Date][Balance;;money;columnclass=>text-right][Reference]';
	private $columns3 = '[Semester][Date_Trans;Date;date;m-d-Y][Date_Post;Post;date;m-d-Y][UserID;Post By][Reference][Item][Debit;;money;columnclass=>text-right][Credit;;money;columnclass=>text-right][Balance;;money;columnclass=>text-right]';
	private $columnsa = '[actions]';
	
    function initialize() {
        global $APP_SESSION;
        $this->setpageaccess($APP_SESSION->getCanCreate(), $APP_SESSION->getCanUpdate(), $APP_SESSION->getCanDelete());       

        $this->addtopbutton(1, 'current'     , 'Current'  ,'list'  ,'success');
        $this->addtopbutton(1, 'archive'     , 'Archive'  ,'archive' ,'danger');

        $columns1 = $this->columns1;
        $columns2 = $this->columns2;
        $columnsa = $this->columnsa;

        $this->addtopbuttoncommand('current' ,    "Usp_AP_SearchStudents '$this->currentuserid', '[@searchtext]'", "$columns1 $columnsa",'Ledger' ,'SN','R',"Usp_AP_GetStudentInfo '$this->currentuserid', '[@primarykeyvalue]'");        
        $this->addtopbuttoncommand('archive' ,    "Usp_SS_SearchLedgerArchive '[@searchtext]'", "$columns2 $columnsa",'Archive','RecordNumber','R',"Usp_AP_GetStudentInfo '$this->currentuserid', '[@primarykeyvalue]', 1");        

        $this->addformfield('current', 0 , 'SN'                   ,0  ,'number'    );
        $this->addformfield('current', 0 , 'Lastname'             ,'' ,'text'      );
        $this->addformfield('current', 0 , 'Firstname'            ,'' ,'text'      );
        $this->addformfield('current', 0 , 'Middlename'           ,'' ,'text'      );
        $this->addformfield('current', 0 , 'College'              ,'' ,'text'      );
        $this->addformfield('current', 0 , 'Course'               ,'' ,'text'      );
        $this->addformfield('current', 0 , 'LedgerStatus'         ,'' ,'text'      , 'Ledger Status');
        $this->addformfield('current', 0 , 'LastSemester'         ,'' ,'text'      , 'Last Semester');
		$this->addformfield('current', 0,  'LedgerBalance'        ,0  ,'text'      ,'Ledger Balance','','money');
		
		$this->addformfield('archive', 0 , 'RecordNumber'         ,0  ,'number');
		$this->addformfield('archive', 0 , 'SN'                   ,'' ,'text'      , 'Student No.');
        $this->addformfield('archive', 0 , 'Name'                 ,'' ,'text');
        $this->addformfield('archive', 0 , 'Date'                 ,'' ,'text');
		$this->addformfield('archive', 0 , 'Course'               ,'' ,'text');
		$this->addformfield('archive', 0 , 'Balance'              ,'' ,'text'      , 'Balance', '', 'money');
		$this->addformfield('archive', 0 , 'Reference'            ,'' ,'text');
		
        $this->addformlayout('current', '[Lastname][Firstname][Middlename]');
        $this->addformlayout('current', '[College][Course][LastSemester][LedgerStatus][LedgerBalance]');

		$this->addformlayout('archive', '[SN][Name][Course]');
		$this->addformlayout('archive', '[Date][Balance][Reference]');
		
        $this->formclass = '';
        $this->recordselector = false;
		$this->primarykeypaddinglength = 0;
    }

    function callback_fetchdata($name, $primarykeyvalue, $record, $cs, &$value) {}
    function callback_fetchsqlcommand($commandtype, &$sql) {}
    function callback_fetchgridtitle(&$title, &$icon, $recordcount) {}
    function callback_fetchformtitle(&$title, &$icon) {}
    function callback_fetchformfooterbuttons(&$footer)  {}
    function callback_fetchgridfooter(&$footer, $searchtext, $totalrecords, $selectedrecords) {}

    function callback_beforeshowgrid(&$cancel, $totalrecords) {}
    function callback_beforeshowfieldsall(&$form) {}
    function callback_beforegeneratefield(&$field, &$html, &$cancel, $readonly) {}
    function callback_aftergeneratefield(&$field, &$html, $readonly) {}
    function callback_aftergeneraterowfields($linenumber, &$html, $readonly) {}
    function callback_aftershowfieldsall(&$form) {}
    function callback_beforeshowform(&$html) {
		if ($this->opener == 'current') {
			$sn = $this->primarykeyvalue;
			$pic = "<img src='" . Tools::pictureurl($sn) . "' class='profile-user-img img-responsive img-circle' alt='$sn'>";
			$html = "<div class='row'><div class='col-lg-2'>$pic</div><div class='col-lg-10'>$html</div></div>";
		}		
		
	}
    function callback_post(&$cancel) {}    
    function callback_validateform($commandtype, &$alertmessage) {}
    function callback_aftersave($commandtype, $redirect) {}
	
	function callback_aftershowform(&$html) {
		global $APP_DBCONNECTION;
		if ($this->opener == 'current') {
			$body = "";
			$title = HTML::icon("table") . ' Ledger as of ' . date('m/d/Y h:i');
			$footer = '';
			
			$sql = "Usp_SS_GetLedger [@SN]";
			Data::replaceparameters($sql, $this->getfieldsproperties('valuequoted'));
			$results = $APP_DBCONNECTION->execute($sql);
			if (!is_array($results)) {
				$body .= HTML::alert('Error','Unable to retrieve records!');
				$this->showdebugerror($results,$sql);
			}
			else {
				$balance = 0;
				for ($i=0; $i<count($results);$i++) {
					$balance += round($results[$i]['Debit'],2) - round($results[$i]['Credit'],2);
					$results[$i]['Balance'] = $balance;
				}
				$columns = $this->stringtoarray(true, $this->columns3);
				$csvtitle = 'Ledger_[@SN]_asof_' . date('Ymd');
				Data::replaceparameters($csvtitle, $this->getfieldsproperties('value'));
				$grid = HTML::datatable("ledger1", $columns, $results,'danger',"data-title='$csvtitle' data-pagelength='100'");
				$footer .= $this->gobackbutton();
   				$body .= $grid;
			}
			$balance = round($balance,2);
			$html .= HTML::box($title,$body, $footer, ($balance<=0?'success':'danger'));
		}
		elseif ($this->opener == 'archive') {
			$body = "";
			$title = HTML::icon("table") . 'Matching Student Number ';
			$footer = '';
			
			$sql = "Usp_AP_SearchStudents '$this->currentuserid', [@SN]";
			Data::replaceparameters($sql, $this->getfieldsproperties('valuequoted'));
			$results = $APP_DBCONNECTION->execute($sql);
			if (!is_array($results)) {
				$html .= HTML::alert('Error','Unable to retrieve records!');
				$this->showdebugerror($results,$sql);
			}
			else {
				if ($results) {
					for ($i=0; $i<count($results); $i++) {
						$sn = $results[$i]['SN'];
						$cs = Crypto::GenericChecksum($sn) . '/' . mt_rand();
						$link = "$this->currentpage/$this->readcommand/" . base64_encode($sn) . "/$cs/current";
						$results[$i]['SN'] = HTML::link($link, $sn, 'View Ledger');
					}
					$columns = $this->stringtoarray(true, $this->columns1);
					$csvtitle = 'MatchingSN_[@SN]_asof_' . date('Ymd');
					Data::replaceparameters($csvtitle, $this->getfieldsproperties('value'));
					$grid = HTML::datatable("MatchingSN", $columns, $results,'danger',"data-title='$csvtitle'");
					$footer .= $this->gobackbutton();
					$body .= $grid;
					$html .= HTML::box($title,$body, $footer, 'warning');
				}					
			}		
		}
		
	}  

}

$system = new CRUDSystem('Ledger','SN','current', $APP_SESSION->getuserid(), $APP_CURRENTPAGE);
$system->run();

?>