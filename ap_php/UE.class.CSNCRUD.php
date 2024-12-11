<?php
/*
CLASS        : CRUD and CRUDeventsall
DESCRIPTION  : Generic PHP Class for CRUD form (Create/Read/Update/Delete + Grid/Search)
AUTHOR       : Chito S. Nuarin
DATE         : March 12, 2020
FILE VERSION : 07/06/2022
DEPENDENCIES : $APP_SESSION,
               $APP_DBCONNECTION
               HTML Class
               Tools Class
               Data Class
               Crypto Class
CLEAN URL    : <BASE>_p0/_p1/_p2/_p3/_p4/_p5 
               _p0 = page
               _p1 = command
               _p2 = primary key value
               _p3 = checksum
               _p4 = random number
               _p5 = opener
         
EVENT SEQUENCE:
     
// Show GRID event sequence
callback_betoreshowtopbuttons(&$buttons) {} // 07/01/2021
callback_showgrid($command, &$cancel) {}
callback_fetchsqlcommand($command='getrecords', &$sql) {}
callback_fetchsqldata($command='getrecords', $sql, &$handled, &$results) {}
callback_afterexecute($command='getrecords', $sql, &$results) {} // 08/12/2021
callback_fetchcolumns($searchtext, &$columns) {}
callback_fetchdata($fieldname, $primarykeyvalue, $record, $cs, &$value) {}
callback_actionbuttons($primarykeyvalue, $record, &$actions));
callback_fetchdatarow($primarykeyvalue, &$record) {}
callback_formatprimarykey(&$primarykeyvaluepadded, &$new, $record, &$cancel) {}
callback_fetchurlextra($record, &$urlextra) {}
callback_fetchgridtitle(&$title, &$gridicon, $recordcount) {}
callback_fetchtablefooter($results, &$footercolumns) {} // 07/06/2022
callback_beforeshowgrid(&$cancel, $totalrecords) {} 
callback_fetchgridfooter(&$gridfooter, $searchtext, $totalrecords, $selectedrecords) {} 
callback_aftershowgrid(&$html, $results) {}

// Read record event sequence
callback_fetchsqlcommand($command='getrecord', &$sql) {}
callback_fetchsqldata($command='getrecord', $sql, &$handled, &$results) {}

// Update record event sequence
callback_post(&$cancel) {}
callback_validateform($command, &$alertmessage) {}
callback_fetchsqlcommand($command='Save', &$sql) {}
callback_beforesave(&$cancel) {}
callback_afterexecute($command='save', $sql, &$results) {}
callback_aftersave($command='Save', &$redirect) {}

// Delete record event sequence
callback_fetchsqlcommand($command='Delete', &$sql) {}
callback_afterexecute($command='delete', $sql, &$results) {}
callback_aftersave($command='Delete', &$redirect) {}

// Show form event sequence
callback_fetchformtitle(&$title, &$formicon) {}
callback_beforeshowfieldsall(&$form)) {}
callback_beforegeneratefield(&$field, &$html, &$cancel, $readonly) {}
callback_aftergeneratefield&$this, &$html, $readonly);                                        
callback_aftergeneraterowfields($linenumber, &$html, $readonly));
callback_aftershowfieldsall(&$form) {}
callback_fetchformfooterbuttons(&$footer) {}
callback_beforeshowform(&$html) {}
callback_aftershowform(&$html) {}

*/
interface CSNCRUDeventsall {
    function initialize();
    function callback_fetchdata($name, $primarykeyvalue, $record, $cs, &$value);
    function callback_fetchsqlcommand($commandtype, &$sql);
    function callback_fetchgridtitle(&$title, &$icon, $recordcount);
    function callback_fetchgridfooter(&$footer, $searchtext, $totalrecords, $selectedrecords);
    function callback_beforeshowgrid(&$cancel, $totalrecords);
    
    function callback_post(&$cancel);
    function callback_validateform($commandtype, &$alertmessage);
    function callback_aftersave($commandtype, $redirect) ;

    function callback_fetchformfooterbuttons(&$footer);
    function callback_fetchformtitle(&$title, &$icon);    

    function callback_beforeshowfieldsall(&$form);
    function callback_beforegeneratefield(&$field, &$html, &$cancel, $readonly);
    function callback_aftergeneratefield(&$field, &$html, $readonly);
    function callback_aftergeneraterowfields($linenumber, &$html, $readonly);
    function callback_aftershowfieldsall(&$form);
    function callback_beforeshowform(&$html);
    function callback_aftershowform(&$html);
}

class CSNCRUD {
    protected $tablename = '';
    public $command;
    protected $defaultcommand;
    public $opener;
    public $opener2;
    protected $currentuserid;
    protected $insertdatefield = 'insertdate';
    protected $formicon = '';
    public $currentpage;

    protected $topbuttons = array();
    protected $fieldsobject = array();

    protected $primarykey = '';
    protected $primarykeyvalue = '';
    protected $activerecord;
    protected $activerecords;
    protected $primarykeypaddinglength = 8;

    public $gridtitle = '';
    public $gridicon = '';
    public $gridsortable = true;
    public $gridpagelength = 10;
    
    protected $recordselector = 1; // 0-disable; 1-after search only; 2-always visible 

    public $searchtextdivsize = 6;
    public $searchtext = '';
    public $showtopbuttons = true;
    public $searchtextarray = array();
    public $searchtexttitle = 'Search';
    public $searchtextrequired = false;

    public $cancreate;
    public $canview = true;
    public $canupdate;
    public $candelete;

    public $withfileupload = false;
    public $formid = '';
    public $formclass = 'form-horizontal';
    public $showback = true;
    
    private $formtemplate = array();

    protected $createcommand = 'cr';
    protected $createstr     = 'New';
    protected $readcommand   = 'rd';
    protected $readstr       = 'View';
    protected $updatecommand = 'up';
    protected $updatestr     = 'Edit';
    
	protected $deletecommand     = 'de';
    protected $deletestr         = 'Delete';
	protected $postdeletecommand = 'Delete'; // buton name
	
	protected $savestr           = "Save";
	protected $postsavecommand   = "Save";   // button name
	protected $savecompletedstr  = "Save record completed.";

    public $actionbuttonstyle = '';
    public $showactionbuttons = true;
    public $alwaysshowcreatebutton = false;
    public $withresetcommand = true;
    public $addgridformpost = false;
    public $columnfilter = false;

    public $footercolumns = array();
	      

    // constructor
    function __construct($tablename, $primarykey, $defaultcommand, $currentuserid, $currentpage) {
        $this->tablename = $tablename;
        $this->primarykey = $primarykey;
        $this->command = @$_GET['_p1'];
        $this->defaultcommand = $defaultcommand;
        $opener  = '';
        if ($this->command == '')
            $this->command = $defaultcommand;
        
        $this->opener = @$_GET['_p5'] ? @$_GET['_p5'] : $defaultcommand; 
        $this->opener2 = @$_GET['_p6'];     
        $this->currentuserid = $currentuserid;
        $this->currentpage = $currentpage;
        $this->formid = mt_rand();
        if (method_exists($this,'initialize'))
            call_user_func_array(array($this,'initialize'), array());
    }

    // set page access (Create, Read, Update, Delete)
    protected function setpageaccess($cancreate, $canupdate, $candelete) {
        $this->cancreate = $cancreate;
        $this->canupdate = $canupdate;
        $this->candelete = $candelete;
    }    

    // run/execute to generate HTML
    public function run() {
        global $APP_DBCONNECTION;
        global $APP_SESSION;
        global $APP_DEBUGCONTENT;

        $this->activerecord = array();

        if ($this->showform() && ($this->command != $this->createcommand)) {
            if (isset($_GET['_p2'])) {                
                $this->primarykeyvalue = @base64_decode(@$_GET['_p2']);
                if ($this->primarykeypaddinglength)
                    $this->primarykeyvalue = 0 + (int) $this->primarykeyvalue;
                $cs = @$_GET['_p3'];
                if (!Crypto::ValidateGenericChecksum($this->primarykeyvalue, $cs)) {
                    echo HTML::alert('Error', 'Link is invalid. Please reload page and try again.');
                    $this->command = $this->opener;
                }
                else {
                    $primarykey = $this->topbuttons[$this->opener]->primarykey;        
                    $searchtext = $APP_SESSION->getsessionvalue("searchtext_{$primarykey}_".$this->tablename, '');
                    $this->searchtext = $searchtext;
                    $sql = str_replace('[@primarykeyvalue]', $this->primarykeyvalue, $this->topbuttons[$this->opener]->sqlgetrecord);
                    Data::replaceparameters($sql, array('searchtext' => $searchtext));  
                    if (method_exists($this, 'callback_fetchsqlcommand')) {
                        call_user_func_array(array($this, 'callback_fetchsqlcommand'), array('getrecord', &$sql));
						$sql = str_replace('[@primarykeyvalue]', $this->primarykeyvalue, $sql);
                        Data::replaceparameters($sql, array('searchtext' => $searchtext));  
                    }
                    if ($sql) {
                        $handled = false;
                        $results = false;
                        if (method_exists($this, 'callback_fetchsqldata')) 
                            call_user_func_array(array($this, 'callback_fetchsqldata'), array('getrecord', $sql, &$handled, &$results));
                        if (!$handled)
                            $results = $APP_DBCONNECTION->execute($sql);
                        if (method_exists($this, 'callback_afterexecute')) 
                            call_user_func_array(array($this, 'callback_afterexecute'), array('getrecord', $sql, &$results));
                        if (!Tools::emptydataset($results)) {
                            $this->activerecord = $results[0];
                            $this->activerecords = $results;
                        }
                        else {
                            echo HTML::alert('Error', 'Unable to retrieve '. $this->topbuttons[$this->opener]->tablename . ' record!');
                            $this->command = $this->opener;
                            $this->showdebugerror($results, $sql);
                        }
                    }                    
                }
            }
        }        
        
        if (count($_POST)) {
            foreach ($_POST as $key => $value) {
                if (substr($key,-2,2) == '[]')
                    $key = substr($key, 0, strlen($key)-2);
				if (!is_array($value))
					$value = utf8_decode($value);
                $this->activerecord[$key] = $value;
            }
            foreach ($this->fieldsobject as $key => $field) {
                $name = $field->name;
                if (($field->isboolean() || ($field->type =='checkbox')) && !isset($_POST[$name]) && !$field->invisible)
                    $this->activerecord[$name] = 0;
            }
        }        
        

        if (count($this->activerecord)) {
            $fields = array();
            $fieldsquoted = array();
            foreach ($this->fieldsobject as $key => &$field) {
                if ($field->partof($this->opener) && $field->memberof($this->command)) {
                    $name = $field->name;
                    $value = $field->value;
                    $valuequoted = $field->formatvalue($value,'','',true);
                    if (isset($this->activerecord[$name])) {
                        if ($field->type == 'checkbox') {
                            $field->checked = trim($this->activerecord[$name]);
                        }
                        else {
                            $value = $field->formatvalue(Data::sanitize($this->activerecord[$name]));
                            $valuequoted = $field->formatvalue(Data::sanitize($this->activerecord[$name]),'','',true);
                            $this->activerecord[$name] = $value;
                        }
                    }
                    $fields[$name] = $value;
                    $fieldsquoted[$name] = $valuequoted;
                    $field->setvalue($value, $valuequoted);
                    
                }
            }
            unset($field);
        }
        

        // PROCESS FORM DATA ($_POST)
        if (count($_POST) ) {
            $primarykey = $this->topbuttons[$this->opener]->primarykey;
            // TODO: GET PRIMARY KEY VALUE FROM fieldsobject
            $this->primarykeyvalue = @$this->activerecord[$primarykey]; //fieldsobject[$primarykey]->value;
            $cancel = false;
            if (method_exists($this, 'callback_post')) {
                call_user_func_array(array($this,'callback_post'),array(&$cancel));
            }
            if (!$cancel) {
                $alertmessage  = '';
                $alertclass    = 'danger';
				// SAVE ROUTINE
                if (isset($_POST[$this->postsavecommand])) { 
                    $validaccess = ($this->cancreate || ($this->canupdate && $this->primarykeyvalue));
                    if ($validaccess) {
                        if (method_exists($this,'callback_validateform')) 
                            call_user_func_array(array($this,'callback_validateform'), array($this->postsavecommand, &$alertmessage));
                        if ($alertmessage == '') {
                            $sql = '';
                            if (method_exists($this, 'callback_fetchsqlcommand')) {
                                call_user_func_array(array($this, 'callback_fetchsqlcommand'), array($this->postsavecommand, &$sql));
                            }
                            if ($sql == '') {
                                $sql = str_replace('[@primarykeyvalue]', $this->primarykeyvalue, $this->topbuttons[$this->opener]->sqlsaverecord);
                                $fieldsobject = array();
                                for ($i=1; ; $i++) {
                                    $found = false;
                                    foreach ($this->fieldsobject as $key => $field) {
                                        $name = $field->name;
                                        if (($field->saveorder == $i) && $field->partof($this->opener)) {                                
                                            $fieldsobject[] = $field;
                                            $found = true;
                                        }
                                    }
                                    if (!$found) break;
                                }
                                foreach ($fieldsobject as $field) {
                                    if ($field->type == 'checkbox') {
                                        if ($field->checked) 
                                            $sql .= ", $field->valuequoted";    
                                        else
                                            $sql .= ", ''";    
                                    }
                                    else
                                        $sql .= ", [@$field->name]";
                                }
                                unset($fieldsobject);
								Data::replaceparameters($sql, $this->getfieldsproperties('valuequoted','saveorder'));
                            }
							else {
								$sql = str_replace('[@primarykeyvalue]', $this->primarykeyvalue, $sql);
                                Data::replaceparameters($sql, $this->getfieldsproperties('valuequoted'));
							}
                            
                            $cancel = false;
                            if (method_exists($this, 'callback_beforesave')) {
                                call_user_func_array(array($this,'callback_beforesave'),array(&$cancel));
                            }
                            if (!$cancel) {
                                $APP_DBCONNECTION->begintransaction();
                                $results = $APP_DBCONNECTION->execute($sql);
                                if (method_exists($this, 'callback_afterexecute')) 
                                    call_user_func_array(array($this, 'callback_afterexecute'), array('save', $sql, &$results));
                                if (!Tools::emptydataset($results)) {
								    $APP_DBCONNECTION->commit();
                                    $this->primarykeyvalue = @$results[0][$this->primarykey];
                                    $modified = (isset($results[0]['modified']) ? @$results[0]['modified'] : 1);
                                    $class = (isset($results[0]['modified']) ? (@$results[0]['modified'] ? 'success' : 'warning') : 'success');
                                    $APP_SESSION->setApplicationMessage($modified  ? $this->savecompletedstr : 'No changes made to the current record.',false,$class);
                                    $redirect = "$this->currentpage/$this->opener/0/0/0/$this->opener";
                                    if (method_exists($this,'callback_aftersave')) {
                                        call_user_func_array(array($this,'callback_aftersave'), array('save', &$redirect));
                                    }
                                    if ($redirect) {
                                        Tools::redirect($redirect);                
                                    }
                                }
                                else {
								    $APP_DBCONNECTION->rollback();
                                    $alertmessage = 'Unable to save data!';
                                    $this->showdebugerror($results,$sql);    
                                }
                            }
                        }
                    }
                    else {
                        $APP_SESSION->setApplicationMessage('Insufficient privilege to perform requested operation!',false,'danger');
						Tools::redirect("$this->currentpage/$this->opener/0/0/0/$this->opener");    
                        return;
                    }
                    
                }
                elseif (isset($_POST[$this->postdeletecommand])) {
                    if ($this->candelete) {						
                        $sql = str_replace('[@primarykeyvalue]', $this->primarykeyvalue, $this->topbuttons[$this->opener]->sqldeleterecord);
                        if (method_exists($this, 'callback_fetchsqlcommand')) {
                            call_user_func_array(array($this, 'callback_fetchsqlcommand'), array($this->postdeletecommand, &$sql));
							$sql = str_replace('[@primarykeyvalue]', $this->primarykeyvalue, $sql);
                        }
                        Data::replaceparameters($sql, $this->getfieldsproperties('valuequoted'));
                        $results = $APP_DBCONNECTION->execute($sql);
                        if (method_exists($this, 'callback_afterexecute')) 
                            call_user_func_array(array($this, 'callback_afterexecute'), array('delete', $sql, &$results));
                        if (!is_array($results) || count($results) == 0) {
                            if (!is_array($results))
                                $alertmessage = $APP_DBCONNECTION->errormessage;
                            else
                                $alertclass = 'Unable to delete record!';
                            $this->showdebugerror($results,$sql);    
                        }
                        else {
                            $APP_SESSION->setApplicationMessage('Record deleted.');
                            $opener = isset($this->topbuttons['deleted']) ? 'deleted' : $this->opener;
                            $redirect = "$this->currentpage/$this->opener/0/0/0/$this->opener";
                            if (method_exists($this,'callback_aftersave')) {
                                call_user_func_array(array($this,'callback_aftersave'), array('delete', &$redirect));
                            }
                            if ($redirect) {
                                Tools::redirect($redirect);                
                            }
                        }    
                    } 
                    else {
                        $APP_SESSION->setApplicationMessage('Insufficient privilege to perform requested operation!',false,'danger');
                        Tools::redirect("$this->currentpage/$this->opener/0/0/0/$this->opener");    
                        return;
                    }
                }

                if ($alertmessage != '') {
                    echo HTML::alert('Attention:', $alertmessage, $alertclass);
                }                   
            }
        }

        // GENERATE FORM
        if ($this->showform() || ($this->command == $this->createcommand)) {
            $this->generateform();      
        }
        
        // GENERATE GRID (DATATABLE)
        if ($this->showgrid()) {
            $this->generategrid();
        }
    }

    // show debug error
    protected function showdebugerror($results, $sql) {
        global $APP_DEBUGCONTENT;
        if (APP_DEMO)
            return;
        if (!is_array($results))
            $APP_DEBUGCONTENT .= $results;
        else 
            $APP_DEBUGCONTENT .= print_r($results,true);
        $APP_DEBUGCONTENT .= "<br>SQL: $sql";
    }

    // top buttons to display
    protected function addtopbutton($showgrid, $name, $title, $icon, $class, $opener=null, $badge='') {
		if (!is_array($title))
			$title = explode(";",$title);		
        $topbutton = new CSNCRUDTopButton($showgrid, $name, $title[0], $icon, $class, $opener, $badge);
		$topbutton->gridtitle = @$title[1] ? $title[1] : $title[0];
        $this->topbuttons[$name] = $topbutton;
    }

    protected function addtopbuttoncommand($name, $sqlgetrecords, $columns, $tablename='', $primarykey='', $formcommands='', $sqlgetrecord='', $sqlsaverecord='', $sqldeleterecord='', $class='') {      
        if ($tablename == '')
            $tablename = $this->tablename;
        if ($primarykey == '')
            $primarykey = $this->primarykey;
        if (!is_array($columns)) {
            $columns = $this->stringtoarray(true, $columns);
        }
        if ($formcommands == '')
            $formcommands = 'CRUD';
        if (isset($columns['recordselector']))
            $columns['recordselector'] = array('<input class="recordselectortoggle" type=checkbox onclick="$(\'.recordselector:checkbox\').not(this).prop(\'checked\', this.checked);">','columnclass' => 'no-sort');
        if (isset($columns['actions'])) {
            $columns['actions']['columnclass'] = 'no-sort';
        }

        if (!isset($this->topbuttons[$name]))
            $topbutton = new CSNCRUDTopButton(true, $name);
        else
            $topbutton = $this->topbuttons[$name];
        if ($class == '')
            $class = $topbutton->class;

        if ($sqlgetrecord == '')
            $sqlgetrecord = "Usp_Get$tablename $this->currentuserid, [@primarykeyvalue]";
        if ($sqldeleterecord == '')
            $sqldeleterecord = "Usp_Delete$tablename $this->currentuserid, [@primarykeyvalue]";
        if ($sqlsaverecord == '') {
            $sqlsaverecord = "Usp_Save$tablename $this->currentuserid";
        }
        
        if (!is_array($formcommands))
            $formcommands = $this->crudstringtoarray($formcommands);
        
        $topbutton->formcommands    = $formcommands;
        $topbutton->sqlsaverecord   = $sqlsaverecord;
        $topbutton->sqlgetrecords   = $sqlgetrecords;
        $topbutton->sqlgetrecord    = $sqlgetrecord;
        $topbutton->sqldeleterecord = $sqldeleterecord;
        $topbutton->columns = $columns;
        $topbutton->gridclass = $class;
        $topbutton->tablename = $tablename;
        $topbutton->primarykey = $primarykey;

        $this->topbuttons[$name] = $topbutton;        
    }


    // form fields for add/edit/delete form
    protected function addformfield($topcommand, $saveorder, $name, $initialvalue='', $type='', $label='', $placeholder='', $format='', $required=true, $readonly=false, $disabled=false, $arrayoptions=array(), $class='') {
        if ((($name == 'sex') || ($name == 'gender')) && ($type == '')) {
            $type = 'select';
            $format = '';
            $arrayoptions = array('M'=>'Male', 'F'=>'Female');
        }
        if (!is_array($arrayoptions))
            $arrayoptions = array();
        if ($type == '')
            $type = 'text';
        if (($label=='') && ($type != 'html')) {
            $label = preg_replace('/([a-z0-9])([A-Z])/','$1 $2', ucfirst($name)); //ucfirst($name);

            if (substr($label,-2) == 'id')
                $label = substr($label,0,strlen($label)-2) . ' ID';
        }
        if (substr(trim($label),0,1) == '+')
            $label = ucfirst($name) . " " .substr(trim($label),1);
        if (substr(trim($placeholder),0,1) == '+')
            $placeholder = 'Input ' . $label . " " .substr(trim($placeholder),1) . '...';
        if (($placeholder =='') && ($type != 'html'))
            $placeholder = 'Input ' . $label . '...';    
    
        $field = new CSNCRUDfield($topcommand, $name, $initialvalue, $type, $format, $saveorder); 
        $field->label        = $label . (($type!='html') && ($type != 'checkbox') ? ":" : '');
        $field->placeholder  = $placeholder;
        $field->required     = $required;
        $field->readonly     = $readonly;
        $field->disabled     = $disabled;
        $field->class        = $class;        
        $field->arrayoptions = $arrayoptions;
        $this->fieldsobject[] = $field;
    }

    //  form layout for multiple fields per line
    protected function addformlayout($topcommand, $columns, $class='') {
        if (!is_array($columns)) {
            $columns = $this->stringtoarray(true, $columns);
        }
        $array = array();
        $zerosize = 0;
        foreach ($columns as $key => $value) {
            $size = 0 + (int) $value[0];
            if ($size == 0)
                $zerosize++;
            if (($size < 1) || ($size > 12))
                $size = 1;
            $array[$key] = $size;
        }
        if ($array) {
            $size = count($array);
            if (($zerosize == $size) && ($size < 12)) {
                $size = floor(12 / $size);
                if ($size) {
                    foreach ($array as $key => &$value)
                        $value = $size;
                    unset($value);
                }
            }
            $this->formtemplate[] = array($topcommand, $array, $class);
        }
    }

    // set fields property values (command delimited or index array)
    protected function setfieldsproperties($topcommand, $fieldnames, $properties, $values, $singlevalueonly=false) {
        if (!is_array($fieldnames))
            $fieldnames = explode(',',$fieldnames); 
        if (!is_array($properties))
            $properties = explode(',', $properties);
        if (!$singlevalueonly) {
            if (!is_array($values))
                $values = explode(',', $values);
        }
        foreach ($fieldnames as $fieldname) {
            $fieldname = trim($fieldname);
            if ($fieldname) {
                foreach ($this->fieldsobject as $key => $fieldobject) {
                    if ((($fieldname == $fieldobject->name) || ($fieldname == '%')) && $fieldobject->partof($topcommand)) {
                        foreach ($properties as $index=>$property) {
                            $property = trim($property);
                            if (property_exists($this->fieldsobject[$key], $property)) {
                                $this->fieldsobject[$key]->$property = ($singlevalueonly ? $values : $values[$index]);    
                            }
                            else
                                echo "<pre>Invalid CSNCRUDfield property: $property</pre>";
                        }
                    }
                }
            }
        }
    }

    protected function getfieldsproperties($propertyname, $filterproperty='') {
        $array = array();
        foreach ($this->fieldsobject as $key => $field) {
            $name = $field->name;
            if (property_exists($field, $propertyname)) {
                if (($filterproperty == '') || ($filterproperty && @$field->$filterproperty)) {
                    if ($field->partof($this->opener) && $field->memberof($this->command)) {
                        $array[$name] = $field->$propertyname;
						if (($propertyname == 'valuequoted') && ($field->format == 'money')) {
							$array[$name] = str_replace(",",'',$field->$propertyname);
						}
                    }
                }
            }
        }
        return $array;
    }

    private function crudstringtoarray($s) {
        if (!is_array($s)) {
            if ($s == '')
                $s = 'crud';
            $default['c'] = $this->createcommand;
            $default['v'] = $this->readcommand;
            $default['r'] = $this->readcommand;
            $default['e'] = $this->updatecommand;
            $default['u'] = $this->updatecommand;
            $default['d'] = $this->deletecommand;
            $arraycommands = array();
            foreach ($default as $key => $value) {
                if (stripos($s, $key) !== false) {
                    $arraycommands[] = $value;
                }
            }
            return array_unique($arraycommands);                
        }
        return $s;
    }

    protected function showgrid() {
        $cancel = false;
        if (method_exists($this, 'callback_showgrid')) {
            call_user_func_array(array($this,'callback_showgrid'), array($this->command, &$cancel));
        }
        if (!$cancel)
            return isset($this->topbuttons[$this->command]) && $this->topbuttons[$this->command]->showgrid;
        else
            return false;
    }

    protected function showform() {
        return @in_array($this->command, $this->topbuttons[$this->opener]->formcommands);
    }

    public function stringtoarray($firstaskey, $string) {
        if (preg_match_all('/\[([a-z0-9 \-\:,;_\.\=\>\(\)\@\$\\/]+)\]/i',$string, $matches))
            $fields = $matches[1];
        else
            $fields = array($string);     
        $columns = array();
        foreach ($fields as $field) {
            $field = trim($field);
            if ($field) {
                $elements = explode(';', $field);
                if (count($elements) == 1)
                    $elements[1] = ucfirst(trim($elements[0]));
                for ($i=0; $i<count($elements); $i++)
                    $elements[$i] = trim($elements[$i]);                    
                $key = $elements[0];
                if ($elements[1] == '')
                    $elements[1] = preg_replace('/([a-z0-9])([A-Z])/','$1 $2', $elements[0]);//$elements[1] = ucfirst($elements[0]);
                if ((@$elements[2] == 'date') && !isset($elements[3]))
                    $elements[3] = 'm/d/Y';
                $c = count($elements);
                for ($i=2;$i<$c;$i++) {
                    $eg = stripos($elements[$i],'=>');
                    if ($eg !== false) {
                        $elements[trim(substr($elements[$i],0,$eg))] = trim(substr($elements[$i],$eg+2));
                        unset($elements[$i]);
                    }
                }
                if ((@$elements[2] == 'money') && !isset($elements['columnclass']))   
                     $elements['columnclass'] = 'text-right';            
                $columns[$key] = array_slice($elements,$firstaskey ? 1 : 0);
                
            }
        }
        return $columns;
    }

    protected function actionbutton($enabled, $command, $primarykeyvalue, $cs, $opener, $icon = '', $title = '', $class='', $id='', $target = '') {
        $link = "$this->currentpage/$command/" . Tools::base64_encodeNOEQ($primarykeyvalue) . "/$cs/$opener";
        if ($icon === '')
            $icon = 'play';
        if ($class === '')
            $class = 'xs btn-primary';
        if ($id === '')
            $id = mt_rand();
        if (!$enabled) {
            $link = '#';
            $class .= ' disabled';
            $title .= '(not allowed)';
        }
        if ($this->actionbuttonstyle == 'button') 
            return HTML::linkbutton($link,  HTML::icon($icon,'','',$title), $class,  $id, $target, $title) . ' ';
        else
            return HTML::link($link,  HTML::icon($icon,'','',$title), $title, $class="text-black",  $id, $target) . ' ';

    }

    protected function generategrid() {
        global $APP_DBCONNECTION;
        global $APP_SESSION;
        
        $this->generatetopbuttons();

        $sql = @$this->topbuttons[$this->command]->sqlgetrecords;
        $sql = str_replace('[@opener]',$this->opener, $sql);
        if (method_exists($this, 'callback_fetchsqlcommand')) {
            call_user_func_array(array($this, 'callback_fetchsqlcommand'), array('getrecords', &$sql));
        }
        $searchtext = '';
        $primarykey = $this->topbuttons[$this->command]->primarykey;        
        $tablename = $this->topbuttons[$this->opener]->tablename;
        if ($this->searchtextdivsize) {
            if ($_POST && isset($_POST['searchtext']))
                $searchtext = $_POST['searchtext'];
            else {
                $searchtext = $APP_SESSION->getsessionvalue("searchtext_{$primarykey}_".$tablename, '');
            }       
            if (is_array($this->searchtextarray) && $this->searchtextarray) {    
                if (array_key_exists($searchtext, $this->searchtextarray) === false)
                    $searchtext = '';
            }
        }
        else
            $searchtext = $this->searchtext;
		$searchtext = str_replace(array('%','_','[',']'),'', $searchtext);
        Data::replaceparameters($sql, array('searchtext' => $searchtext));

        if (method_exists($this, 'callback_fetchsqlcommand')) {
            call_user_func_array(array($this, 'callback_fetchsqlcommand'), array('getrecords', &$sql));
			Data::replaceparameters($sql, array('searchtext' => $searchtext));
        }
        if ($sql) {
            $searchdiv = '';
            if ($this->searchtextdivsize) {
                if ($this->searchtextarray)
                    $searchdiv = HTML::searchboxselect('searchtext', $searchtext,$this->searchtextarray,$this->searchtexttitle,"searchtext_div col-lg-".$this->searchtextdivsize, $this->searchtexttitle);
                else
                    $searchdiv = HTML::searchbox('searchtext', $searchtext,'','','',"searchtext_div col-lg-".$this->searchtextdivsize, $this->searchtexttitle);
                $APP_SESSION->setsessionvalue("searchtext_{$primarykey}_".$tablename, $searchtext);  

                if (method_exists($this, 'callback_beforeshowsearchdiv')) 
                    call_user_func_array(array($this, 'callback_beforeshowsearchdiv'), array(&$searchdiv, '</div></form><br>'));
            }

            if ($this->columnfilter) {
                $columnfiltersource = array();
                $columnfiltervalues = array();
                foreach ($this->topbuttons[$this->command]->columns as $key=>$col) {
                    if (!in_array($key, array('recordselector','actions'))) {
                            $columnfiltersource[$key] = $col[0];
                    }
                    if (@in_array($key, @$_POST["columnfilter_{$primarykey}"]) || !@$col['hidden']) {
                        $columnfiltervalues[] = $key;    
                    }
                }
                if (@$_POST["columnfilter_{$primarykey}"])
                    $columnfiltervalues = @$_POST["columnfilter_{$primarykey}"];
                $columnfilterdiv = HTML::selectbox("columnfilter_{$primarykey}", $columnfiltervalues, $columnfiltersource, true, 'columnfilter no-warning hidden','','col-lg-6 hidden','Columns') . '</div></form><br>';
                $searchdiv = str_ireplace('</div></form><br>', $columnfilterdiv, $searchdiv);
            }

            $this->searchtext = $searchtext;
            $handled = false;
            $results = false;
            if (method_exists($this, 'callback_fetchsqldata')) 
                call_user_func_array(array($this, 'callback_fetchsqldata'), array('getrecords', $sql, &$handled, &$results));
            if (!$handled)
                $results = $APP_DBCONNECTION->execute($sql);
            if (method_exists($this, 'callback_afterexecute')) 
                call_user_func_array(array($this, 'callback_afterexecute'), array('getrecords', $sql, &$results));
            $totalrecords = 0;
            $selectedrecords = 0;
            $this->activerecords = $results;
			//echo '<pre>', print_r($results,true),'<pre>';
            if (!is_array($results)) {
                $grid = HTML::alert('Alert','Unable to retrieve records!');
                $this->showdebugerror($results, $sql);
            }
            else {
                if ($this->gridtitle)
                    $tabletitle = $this->gridtitle;
                else
					$tabletitle = $this->topbuttons[$this->command]->gridtitle;
                $tableattributes = "data-title='$tabletitle' data-pagelength='$this->gridpagelength'";
                $actions = '';
                $columns = $this->topbuttons[$this->command]->columns;
				if (method_exists($this, 'callback_fetchcolumns')) {
					call_user_func_array(array($this,'callback_fetchcolumns'), array($searchtext, &$columns));
				}
                $totalrecords  = count($results);
                if (!$this->topbuttons[$this->command]->gridsortable) {
                    foreach ($columns as $key => &$column) {
                        $column['column-class'] = 'no-sort';
                    }
                    unset($column);
                }
                
                if (!is_array($columns))
                    $columns = array();
                if (($searchtext == '') && ($this->recordselector < 2) || $totalrecords==0 || ($this->recordselector == 0))
                    unset($columns['recordselector']);
                $primarykey = $this->topbuttons[$this->command]->primarykey;

                foreach ($columns as $key=>$column) {
                    if (!in_array($key, array('recordselector','actions'))) {
                        if ((!@$_POST["columnfilter_{$primarykey}"] && @$column['hidden']) || (@$_POST["columnfilter_{$primarykey}"] && !in_array($key, @$_POST["columnfilter_{$primarykey}"]))  )
                            unset($columns[$key]);
                    }
                }
                if (!is_array($this->footercolumns))
                    $this->footercolumns = $this->stringtoarray(true, $this->footercolumns);
                $footercolumns = array();
                $tabletotals = array();

                for ($i=0; $i<count($results); $i++) {
                    $primarykeyvalue = @$results[$i][$primarykey];
                    $cs = Crypto::GenericChecksum($primarykeyvalue) . '/' . mt_rand();
                    $actions  = '';                    
                    
                    if (method_exists($this, 'callback_fetchdata')) {
                        foreach ($columns as $fieldname => $coldefs) {
                            $value = @$results[$i][$fieldname];
                            $checked = isset($_POST['recordselector']) && in_array($primarykeyvalue, @$_POST['recordselector']) ? 'checked' : '';
                            if ($checked)
                                ++$selectedrecords;
                            if ($fieldname == 'recordselector')  {
                                $value = "<input name='recordselector[]' $checked type=checkbox value='$primarykeyvalue' class='recordselector'>";
                            }
                            call_user_func_array(array($this, 'callback_fetchdata'), array($fieldname, $primarykeyvalue, $results[$i], $cs, &$value));
                            $results[$i][$fieldname] = $value;  
                        }
                    }
                    $actions = '';
                    if ($primarykeyvalue) {
                        if ($this->canview && in_array($this->readcommand, $this->topbuttons[$this->command]->formcommands))
                            $actions = $this->actionbutton(true, $this->readcommand, $primarykeyvalue, $cs, $this->opener, 'folder-open-o', $this->readstr, 'xs btn-primary');
                        if ($this->canupdate && in_array($this->updatecommand, $this->topbuttons[$this->command]->formcommands))
                            $actions .= $this->actionbutton(true, $this->updatecommand, $primarykeyvalue, $cs, $this->opener, 'pencil', $this->updatestr,'xs btn-success');
                        if ($this->candelete && in_array($this->deletecommand, $this->topbuttons[$this->command]->formcommands))
                            $actions .= $this->actionbutton(true, $this->deletecommand, $primarykeyvalue, $cs, $this->opener, 'remove', $this->deletestr,'xs btn-danger');
                        if (method_exists($this, 'callback_actionbuttons')) {
                            call_user_func_array(array($this, 'callback_actionbuttons'), array($primarykeyvalue, $results[$i], &$actions));
                        } 
                    }
                    
                    if (method_exists($this, 'callback_fetchdatarow')) {
                        call_user_func_array(array($this, 'callback_fetchdatarow'), array($primarykeyvalue, &$results[$i]));
                    } 

                    @$results[$i]['actions'] = $actions . @$results[$i]['actions'];
                    $new = '';
                    if ($this->insertdatefield && isset($results[$i][$this->insertdatefield]))
                        $new = (substr(@$results[$i][$this->insertdatefield],0,10) == date('Y-m-d')) ? ' <sup class="text-yellow">new</sup>' : '';
                    $primarykeyvaluepadded = $results[$i][$primarykey];
                    if ($this->primarykeypaddinglength) 
                        $primarykeyvaluepadded = Data::padleft($primarykeyvaluepadded,$this->primarykeypaddinglength);
                    if ($this->canview && in_array($this->readcommand, $this->topbuttons[$this->command]->formcommands)) {
                        $cancel = false;
                        if (method_exists($this, 'callback_formatprimarykey')) {
                            call_user_func_array(array($this, 'callback_formatprimarykey'), array(&$primarykeyvaluepadded, &$new, $results[$i], &$cancel));
                        } 
                        $urlextra = '';
                        if (method_exists($this, 'callback_fetchurlextra')) {
                            call_user_func_array(array($this, 'callback_fetchurlextra'), array($results[$i], &$urlextra));
                            if ($urlextra)
                                $urlextra = "/$urlextra";
                        } 
                        if (!$cancel)
                            $results[$i][$primarykey] = HTML::link("$this->currentpage/$this->readcommand/" . Tools::base64_encodeNOEQ($primarykeyvalue) . "/$cs/$this->command/$this->opener$urlextra",$primarykeyvaluepadded . $new,'View');
                        else
                            $results[$i][$primarykey] = $primarykeyvaluepadded;
                    }

                    foreach ($this->footercolumns as $fieldname => $label) {
                        if (@$label[1] == 'sum')
                            @$tabletotals[$fieldname] += @$results[$i][$fieldname];
                        elseif (@$label[1] == 'count')
                            @$tabletotals[$fieldname] += 1;
                    }
                }
                if ($actions && !isset($columns['actions']) && $this->showactionbuttons)
                    $columns['actions'] = 'Actions';
    
                // Construct Table Footer
                // 07/06/2022
                $footercolumns = array();
                if ($this->footercolumns) {
                    foreach ($columns as $fieldname => $label) {
                        $footercolumns[$fieldname] = '';
                        if (isset($this->footercolumns[$fieldname])) {
                            $footercolumns[$fieldname] = $this->footercolumns[$fieldname];
                            if (@$this->footercolumns[$fieldname][1] == 'sum')
                                $footercolumns[$fieldname][0] = @$tabletotals[$fieldname] + 0;
                            elseif (@$this->footercolumns[$fieldname][1] == 'count')
                                $footercolumns[$fieldname][0] = @$tabletotals[$fieldname] + 0;
                        }
                    }
                }
                
                if (method_exists($this, 'callback_fetchtablefooter')) {
                    call_user_func_array(array($this, 'callback_fetchtablefooter'), array($results, &$footercolumns));
                }
                // ------

                $grid = HTML::datatable("{$this->command}_table", $columns, $results, '',$tableattributes, true, $footercolumns);
                
                if (count($results)==0)
                    if ($this->searchtextrequired && ($searchtext == ''))
                        $grid .= '<br><b>Please input search text!</b>';
                    else
                        $grid .= '<br><b>No records found!</b>';
            }
            $grid = $searchdiv . $grid;
            
            if ($this->gridtitle)
                $title = $this->gridtitle;
            else
                $title = HTML::icon($this->topbuttons[$this->command]->icon, $this->topbuttons[$this->command]->gridtitle);

            $gridicon = $this->gridicon;
            if (method_exists($this,'callback_fetchgridtitle'))
                call_user_func_array(array($this,'callback_fetchgridtitle'), array(&$title, &$gridicon, count($results)));
            if ($gridicon)
                $title = "$gridicon $title";

            $cancel = false;
            if (method_exists($this, 'callback_beforeshowgrid'))
                call_user_func_array(array($this, 'callback_beforeshowgrid'), array(&$cancel, $totalrecords)); 
            
            $gridfooter = '';
            if (method_exists($this,'callback_fetchgridfooter'))
                call_user_func_array(array($this, 'callback_fetchgridfooter'), array(&$gridfooter, $searchtext, $totalrecords, $selectedrecords)); 
            if (!$cancel) {
                $html = '';
                if ($this->addgridformpost)
                    echo '<form method=post>';
                $boxclass = @$this->topbuttons[$this->command]->gridclass;
                $boxtools = @$this->topbuttons[$this->command]->boxtools;
                $boxtools .= HTML::boxtool("print_{$this->command}_table",HTML::icon('print'),'','printpage');
                $html = HTML::box($title, $grid, $gridfooter,$boxclass,true,false,'table-responsive','',$boxtools);                
                if (method_exists($this, 'callback_aftershowgrid'))
                    call_user_func_array(array($this, 'callback_aftershowgrid'), array(&$html, $results));
                echo $html;
                if ($this->addgridformpost)  
                    echo '</form>';
            }
        }
    }

    private function commandtitle($commandstr='') {
        if ($commandstr == '')
            $commandstr = $this->command;
        if ($commandstr == $this->createcommand)
            return $this->createstr;
        elseif ($commandstr == $this->readcommand)
            return $this->readstr;
        elseif ($commandstr == $this->updatecommand)
            return $this->updatestr;
        elseif ($commandstr == $this->deletecommand)
            return $this->deletestr;
        return $commandstr;        
    }

    public function gobackbutton() {
        return HTML::linkbutton("$this->currentpage/$this->opener/0/0/0/$this->opener",HTML::icon('arrow-left','Go Back'));
    }

    private function generateform() {
        $form = '';
        $footer = '';
        $primarykeyvaluepadded = $this->primarykeyvalue;
        if ($this->primarykeypaddinglength)
            $primarykeyvaluepadded = Data::padleft($primarykeyvaluepadded,$this->primarykeypaddinglength);
        $readonly = ($this->command != $this->createcommand) && ($this->command != $this->updatecommand);
        $title = ucfirst($this->commandtitle($this->command)) . ' ' . $this->topbuttons[$this->opener]->tablename . ($this->command != $this->createcommand ? " [" . $primarykeyvaluepadded . "]" : '');
        $formicon = $this->formicon;
        if ($formicon == '')
            $formicon = $readonly ? 'folder' : 'edit';
        if (method_exists($this,'callback_fetchformtitle'))
            call_user_func_array(array($this,'callback_fetchformtitle'), array(&$title, &$formicon));

        if (method_exists($this,'callback_beforeshowfieldsall'))
            call_user_func_array(array($this,'callback_beforeshowfieldsall'), array(&$form));
        
        $callback_beforegeneratefield = method_exists($this,'callback_beforegeneratefield') ? $this : null;
        $callback_afteregeneratefield = method_exists($this,'callback_aftergeneratefield') ? $this : null;

        $formclass = $this->formclass != '' && $this->formclass != 'form-horizontal' ? 'form-horizontal' : $this->formclass;

        $formrows = array();
        if ($this->formtemplate) {
            foreach ($this->formtemplate as $line) {
                if (is_array($line[0])) {
                    if (in_array($this->opener, $line[0]))
                        $formrows[] = array($line[1], $line[2]);
                }
                else {
                    if (($line[0] == $this->opener) || ($line[0] == ''))
                        $formrows[] = array($line[1], $line[2]);
                }
            }
        }

        $formclass = $this->formclass;
        if (count($formrows) && (stripos($this->formclass,'form-horizontal') === false) && (stripos($this->formclass,'form-inline') === false)) {
            $formclass = str_ireplace(array('form-inline', 'form-horizontal'),'', $formclass);
            $linenumber = 0;
            foreach ($formrows as $row) {
                $line = $row[0];
                $lineclass = $row[1];
                $items = 0;
                $html = '';
                $htmlfields = 0;
                foreach ($line as $name => $fieldsize) {
                    $key = $this->getfieldindex($name);
                    if ($key >= 0) 
                        $field = $this->fieldsobject[$key];
                    else {
                        if ($key == -1) {
                            $field = new CSNCRUDfield(0,'',"<pre>Invalid field: <b>$name</b></pre>",'html','',0);
                            $field->label = 'Form Layout Error!';
                        }
                    }                
                    if (($key >= -1) || (($key >= 0) && ($field->partof($this->opener) && $field->memberof($this->command)))) {
                        $field->fieldsize = $fieldsize;
                        $html .= $field->generatehtml('template', $readonly,$callback_beforegeneratefield, $callback_afteregeneratefield);
                        ++$items;
                        if ($field->type == 'html')
                            $htmlfields++;
                    }
                }
                if ($items) {
                    //if ($items != $htmlfields)
                        $html = "<div class='form-group row $lineclass' id='form_row_$linenumber'>".$html . '</div>';
                    if (method_exists($this, 'callback_aftergeneraterowfields')) {
                        call_user_func_array(array($this, 'callback_aftergeneraterowfields'), array($linenumber, &$html, $readonly));
                    }
                    $form .= $html;
                }
                $linenumber++;
            }
        }
        else {
            foreach ($this->fieldsobject as $key => $field) {
                $name = $field->name;
                if ($field->partof($this->opener) && $field->memberof($this->command)) {
                    $html = $field->generatehtml($formclass, $readonly,$callback_beforegeneratefield, $callback_afteregeneratefield);
                    $form .= $html;
                }
            }
        }
        if (method_exists($this,'callback_aftershowfieldsall'))
            call_user_func_array(array($this,'callback_aftershowfieldsall'), array(&$form));

        $footer = '';

        if ($this->showback)
            $footer = $this->gobackbutton();
                
        if (!$readonly) {
            $footer .= HTML::submitbutton($this->postsavecommand,HTML::icon('floppy-o',$this->savestr), 'success');
            if ($this->withresetcommand)
                $footer .= HTML::button('Reset','Reset','default','reset');
        }
        else {
            if (($this->command != $this->createcommand ) && isset($this->topbuttons[$this->createcommand]) && $this->cancreate &&
                in_array($this->createcommand , $this->topbuttons[$this->opener]->formcommands)) 
                    $footer .= HTML::linkbutton("$this->currentpage/$this->createcommand/0/0/0/$this->opener", $this->topbuttons[$this->createcommand]->iconandtitle, $this->topbuttons[$this->createcommand]->class,  'bnew', '', $this->topbuttons[$this->createcommand]->title);
            $cs = Crypto::GenericChecksum($this->primarykeyvalue) . '/' . mt_rand();

            if (in_array($this->updatecommand, $this->topbuttons[$this->opener]->formcommands) && $this->canupdate) {
                $footer .= HTML::linkbutton("$this->currentpage/$this->updatecommand/".Tools::base64_encodeNOEQ($this->primarykeyvalue)."/$cs/$this->opener",  HTML::icon('pencil',$this->updatestr), 'info',  'bed', '', $this->updatestr);
            }

        }
        if (($this->command == $this->deletecommand) && in_array($this->deletecommand, $this->topbuttons[$this->opener]->formcommands) && $this->candelete)
            $footer .= HTML::submitbutton($this->postdeletecommand,$this->deletestr, 'danger');
        
        if (method_exists($this, 'callback_fetchformfooterbuttons')) {
            call_user_func_array(array($this,'callback_fetchformfooterbuttons'), array(&$footer));
        }

        $boxtools = '';
        $body = $form;
        if ($formicon)
            $title = HTML::icon($formicon, $title);
        $fileupload = $this->withfileupload ? 'enctype="multipart/form-data"' : '';

        $formclass = $formclass ? " class='$formclass'" : '';

        $html = "<form id='$this->formid' method=post autocomplete=off $fileupload $formclass>";
        $html .= HTML::box("$title", $body, $footer, 'primary',true,false,'','',$boxtools);
        $html .= '</form>';
        if (method_exists($this,'callback_beforeshowform'))
            call_user_func_array(array($this,'callback_beforeshowform'), array(&$html));
        echo $html;
        $html = '';
        if (method_exists($this,'callback_aftershowform'))
            call_user_func_array(array($this,'callback_aftershowform'), array(&$html));
        echo $html;


    }    

    protected function generatetopbuttons() {
        $buttons = '<div class="buttons appbuttons">';
        $i=0;
        foreach ($this->topbuttons as $name => $topbutton) {
            $active = $this->command == $name ? ' active' : '';
            $buttonclass = $active ? $topbutton->buttonclass : 'default';
            $from = $topbutton->opener !== null ? $topbutton->opener : $name;
            $disabled = '';
            if ($name == $this->createcommand) {
                 if (!$this->alwaysshowcreatebutton && is_array($this->topbuttons[$this->opener]->formcommands))
                    $disabled = in_array($name, $this->topbuttons[$this->opener]->formcommands) ? '' : 'disabled';
            }
            $link = "$this->currentpage/$name/0/0/0/$from";
            if ($disabled)
                $link = '';
            $buttons .= HTML::linkbutton($link,"$topbutton->iconandtitle$topbutton->badge",$buttonclass.$active." btn-app $disabled appbutton","btn_$name");
            $i++;
        }        
        $buttons .= '</div>&nbsp;';       
        // new event: 07/01/2021-csn
        if (method_exists($this,'callback_betoreshowtopbuttons'))
            call_user_func_array(array($this,'callback_betoreshowtopbuttons'), array(&$buttons));

        if (($this->command != $this->createcommand) && $this->showtopbuttons)
            echo "$buttons";
    }

    protected function getfieldindex($name) {
        $key = -1;
        foreach ($this->fieldsobject as $index => $field) {
            if ($field->name == $name) {
                if ($field->partof($this->opener) && $field->memberof($this->command)) 
                    return $index;
                if (!$field->partof($this->opener))
                    $key =  -2;
                elseif (!$field->memberof($this->command)) 
                    $key = -3;
            }                    
        }
        return $key;
    }
}

class CSNCRUDfield {
    public $name;
    public $type = 'text';
    public $initialvalue = '';
    public $value = '';
    public $valuequoted = "''";
    public $format = '';
    public $readonly = false;
    public $required = true;
    public $disabled = false;
    public $label = '';
    public $labelsize = 2;
    public $fieldsize = 10;
    public $arrayoptions = array();
    public $class = '';
    public $placeholder = '';
    public $saveorder = 1;
    public $invisible = false;
    public $topcommands = '';
    public $formcommands = '';
    public $checked = false;
    
    function __construct($topcommands, $name, $initialvalue, $type, $format, $saveorder) {
        if (!is_array($topcommands) && $topcommands)
            $topcommand = explode(',',$topcommands);                   
        $this->topcommands = $topcommands;
        $this->name = $name;
        $this->saveorder = $saveorder;
        if ($type == '')
            $type = 'text';
        if (($type=='date') && ($format == ''))
            $format = 'Y-m-d';
        if (($type == 'select') && !is_array($format))
            $format = array($format);
        if ($type == 'html') {
            $this->readonly = true;
            $this->required = false;
            $this->format = '';
            $this->saveorder = 0;
        }
        $this->type = $type;
        $this->format = $format;            
        if ($this->type == 'select') {
            $this->arrayoptions = $format;
            $this->format = '';
        }
        $this->initialvalue = $this->formatvalue($initialvalue);
        $this->setvalue($this->initialvalue, $this->formatvalue($this->initialvalue,'','',true));
    }

    public function isboolean($type='') {
        if ($type=='')
            $type = $this->type;
        return preg_match('/(bit|boolean|_heckbox|_adio)/i',$type) > 0;
    }

    public function isnumeric($type='') {
        if ($type == '')
            $type == $this->type;
        return (preg_match('/(bit|boolean|double|money|int|integer)/i', $type) > 0) || $this->isboolean($type);
    }

    public function istext($type='') {
        if ($type == '')
            $type == $this->type;
        return !$this->isboolean($type) && !$this->isnumeric($type);        
    }

    public function isdate($type='') {
        if ($type == '')
            $type == $this->type;
        return $type == 'date';
    }

    public function ishtmlinput($type='') {
        if ($type == '')
            $type = $this->type;        
        if ($type == 'textarea')
            return false;
        return (preg_match('/(color|date|datetime\-local|email|month|number|password|text|time|url|week)/i', $type) > 0);
    }

    public function formatvalue($value, $type='', $format='', $quoted=false) {
        $quote = '';
        if ($quoted)
            $quote = "'";
        if ($type == '')
            $type = $this->type;
        if ($format == '')
            $format = $this->format;

        if ($this->isnumeric($type) || ($type=='number')) {
            $value += 0;
        }
        if ($this->isdate(($type))) {
            if ($value )               
                $value = $quote.Data::formatdate($value, $format).$quote;
            else 
                $value = $quote.$quote;
        }
        // elseif ($this->isboolean($type)) {
        //     $value = ($value) ? 1 : 0;
        // }
        else {
            $value = $quote.str_replace("'","`",$value).$quote;
        }
        
        return $value;
    }

    public function setvalue($value, $quotedvalue) {
        $this->value = $value;
        $this->valuequoted = $quotedvalue;
    }

    public function generatehtml($formclass, $readonly, $callback_beforegeneratefield=null, $callback_aftergeneratefield=null) {
        $html = '';
        $cancel = false;
        if ($callback_beforegeneratefield != null) {
            call_user_func_array(array($callback_beforegeneratefield, 'callback_beforegeneratefield'),array(&$this, &$html, &$cancel, $readonly));
        }
        if (!$cancel) {
            if (!$this->invisible) {
                $fieldsize = $this->fieldsize;
                $labelsize = $this->labelsize;
                $fieldhtml = '';
                $placeholder = $readonly || $this->readonly || $this->disabled ? '' : $this->placeholder;
                if (($formclass == '') || ($formclass == 'form-inline') || ($formclass == 'template')) {
                    $fieldsize = 0;
                    $labelsize = 0;
                }                        
                if ($this->type == 'html') {
                    $fieldhtml = HTML::hformdiv($this->label,$this->initialvalue,$labelsize,$fieldsize,$this->class,$this->name);
                }
                elseif ($this->ishtmlinput()) {
                    $value = $this->value;
                    $type = $this->type;
                    if (($this->format == 'money') && ($readonly || $this->readonly || $this->disabled)) {
                        $value = @number_format(str_replace(',','',$value),2);
					
					}
                    elseif (($this->type == 'date') && $this->format && ($readonly || $this->readonly || $this->disabled)) {
                        $value = Data::formatdate($value,$this->format);
                        $type = 'text';
                    }

                    $fieldhtml =  HTML::hforminputtext($this->name, $this->label, $value, $placeholder, 
                                    $this->required, $this->readonly || $readonly, $this->disabled, 
                                    $labelsize, $fieldsize, $type, $this->class);                
                }
                elseif ($this->type == 'select') {
                    $fieldhtml = HTML::hformselect($this->name, $this->label, $this->value, $this->arrayoptions,
                                    $this->required, $this->readonly || $readonly, $this->disabled,
                                    $labelsize, $fieldsize, $this->class);
                }
                elseif ($this->type == 'checkbox') {
                    $fieldhtml = HTML::hformradio($this->name, $this->label, $this->checked, 
                                    $this->readonly || $readonly, $this->disabled, 
                                    $labelsize, $fieldsize, '', $this->value);
                }
                elseif ($this->type == 'textarea') {
                    $fieldhtml = HTML::hformtextarea($this->name, $this->label, $this->value, $placeholder, 
                                    $this->required, $this->readonly || $readonly, $this->disabled,
                                    $labelsize, $fieldsize, $this->type, $this->class);
                }
                elseif ($this->type == 'hidden') {
                    $fieldhtml = HTML::hidden($this->name, $this->value);
                }
                else {
                    $fieldhtml = HTML::hformdiv("Error:", "<pre><code>Unsupported type = [<b>$this->type</b>]; value = <b>".htmlentities($this->value)."</b></code></pre>", $this->labelsize,12-$this->labelsize, $this->class, $this->name);
                }
                if ($formclass == 'template') {
                    $fieldhtml = str_replace('form-group ',"col-lg-$this->fieldsize ",$fieldhtml);
                    if ($this->type == 'checkbox') {
                        $fieldhtml = str_replace('form-group',"col-lg-$this->fieldsize ",$fieldhtml);
                    }
                }
                $html .= $fieldhtml;
                
            }
            if ($callback_aftergeneratefield != null) {
                call_user_func_array(array($callback_aftergeneratefield, 'callback_aftergeneratefield'),array(&$this, &$html, $readonly));
            }
        }
        return $html;
    }   
    
    // part of top button
    function partof($topcommand) {
        if (is_array($this->topcommands))
            return in_array($topcommand, $this->topcommands);
        if ($this->topcommands == '')
            return true;
        return $topcommand == $this->topcommands;
    }

    // member of form
    function memberof($formcommand) {
        if (is_array($this->formcommands))
            return in_array($formcommand, $this->formcommands);
        if ($this->formcommands == '')
            return true;
        return $formcommand == $this->formcommands;
    }    
}


class CSNCRUDTopButton {
    public $name;
    public $title;
    public $iconandtitle;
    public $buttonlabel;
    public $icon;
    public $opener;
    public $badge;
	public $gridtitle;

    public $tablename;
    public $primarykey;
    
    public $formcommands;
    public $sqlsaverecord;
    public $sqlgetrecords;
    public $sqlgetrecord;
    public $sqldeleterecord;

    public $columns = array();
    public $class;
    public $buttonclass;
    public $gridclass;
    public $gridsortable = true;

    public $showgrid = true;
    public $boxtools = '';
    
    function __construct($showgrid, $name, $title='', $icon='', $class='', $opener='', $badge=null) {
        $this->name = $name;
        $this->title = $title;
        $this->icon = $icon;
        $this->buttonclass = $class;
        $this->gridclass = $class;
        $this->class = $class;
        $this->opener = $opener;
        $this->badge = $badge;
        $this->iconandtitle = HTML::icon($icon,$title);
        $this->showgrid = $showgrid;
    }  
    
  
}
?>