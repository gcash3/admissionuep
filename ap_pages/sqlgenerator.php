<?php
include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_tools.php");

$userid = $APP_SESSION->getUserID();
$tablename = @$_POST['tablename'];
$login = @$_POST['login'];
$password = @$_POST['password'];
$connection = null;
if ($login) {
	$connection =  Data::openconnection($connect=true, $login, $password);
	if (!$connection->connected) {
		echo HTML::alert('Error','Unable to connect to server. Invalid login or password!');
	}
}
$resultcolumns = isset($_POST['resultcolumns']) ? $_POST['resultcolumns'] : array();
$conditioncolumns = isset($_POST['conditioncolumns']) ? $_POST['conditioncolumns'] : array();

$title = HTML::icon('table','Tables');
$body = '';
$html = '';
$footer = '';
$table_array = SPGenTools::gettables($userid, APP_DB_DRIVER, $connection);
if ($table_array && ($tablename == '')  )
    $tablename = @current($table_array);

$column_array = SPGenTools::getttablecolumns($userid, $tablename, APP_DB_DRIVER, true, $connection);

$footer = 'Columns: ' . count($column_array);

$body .= HTML::hforminputtext('login','SQL User', $login, 'SQL Login',true,false,false,2,4);
$body .= HTML::hformpassword('password','SQL Password', $password, 'SQL Password',true,false,false,2,4);
$body .= HTML::hformdiv('&nbsp;',HTML::submitbutton('connect', 'Connect', 'primary', false, '', true));

$body .= HTML::hformselect('tablename','Table Name', $tablename, $table_array, true, false, false);
$body .= HTML::hformselect2multiple('resultcolumns[]','Result Excluded', $resultcolumns, $column_array, false, false,false);
$body .= HTML::hformselect2multiple('conditioncolumns[]','Conditions', $conditioncolumns, $column_array, true);

$footer = HTML::submitbutton('Generate','Generate','success');

echo '<form method=post id=form class="form-horizontal">';
echo HTML::box($title . " [" . APP_DB_DRIVER . "]", $body, $footer,'',true,false,'');

$columns = array();
$tablenamenos = substr($tablename,0,strlen($tablename)-1);
$tablenamecamel  = ucfirst($tablename);
$tablenamenoscamel = ucfirst(substr($tablename,0,strlen($tablename)-1));
$primarykey = isset($conditioncolumns[0]) ? $conditioncolumns[0] : key($column_array);

$sqlgets   = '';
$sqlget    = '';
$sqlsave   = '';
$sqldelete = '';

$maxlen = 0;
$columns = array();
foreach ($column_array as $columnname => $value) {
    if (in_array($columnname, $resultcolumns) === false) {
        $columns[$columnname] = $value;
        $maxlen = max($maxlen, strlen($columnname));
    }
}

if ($_POST) {
    if (APP_DB_DRIVER == 'sqlsrv')
        include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_mssql.php");
    else
        include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_" . APP_DB_DRIVER . ".php");
    $sql       = '';
    foreach ($_POST as $name => $value) {
        if ($name == 'ExecuteGet')
            $sql = $sqlget;
        elseif ($name == 'ExecuteGets')
            $sql = $sqlgets;
        elseif ($name == 'ExecuteSave')
            $sql = $sqlsave;
        elseif ($name == 'ExecuteDelete')
            $sql = $sqldelete;
    }
    if ($sql) {
        $results = $APP_DBCONNECTION->execute($sql,false);
        if (!is_array($results)) {
            $error = str_ireplace('ERROR: ','', $results);
            echo HTML::alert('Execute', $error ? $error : 'Stored procedure created.', $error ? 'danger' : 'success');
        }
    }
        
}

echo @$htmlgets;
echo @$htmlget;
echo @$htmlsave;
echo @$htmldelete;
echo @$htmlcolumns;
echo @$htmlcolumnnames;
echo '</form>';


//-------------------------------------------------------------------------
// PHP Code using CSNCrud Class
//-------------------------------------------------------------------------
$maxlen = 0;
$columns = array();
foreach ($column_array as $columnname => $value) {
    if (in_array($columnname, $resultcolumns) === false) {
        $columns[$columnname] = $value;
        $maxlen = max($maxlen, strlen($columnname));
    }
}

$maxlen+=5;
$columns = '';
$formfields = "        \$this->addformfield('', 1 , ".str_pad("'$primarykey'",$maxlen) . ",0  ,'number'    );\n";
$i=1;
$c=1;
foreach ($column_array as $columnname => $value) {
    if (in_array($columnname, $resultcolumns) === false)  {
        $columns .= "[$columnname]";
        if ($i>1) {
            $initialvalue = "''";
            $type = 'text      ';
            $datatype = substr($value,strrpos($value,' ')+1);
            if ($columnname == 'password') {
                $type = "'password'  ";
            }
            elseif ($columnname == 'email') {
                $type = "'email'     ";
            }
            elseif (stripos($datatype,'CHAR') !== false) {
                $type = "'text'      ";
            }
            elseif ($datatype == 'DATE') {
                $type = "'date'      ";
            }
            elseif ($datatype == 'TIMESTAMP') {
                $type = "'text'      ";
            }
            elseif ($datatype == 'DATETIME') {
                $type = "'text'      ";
            }
            else {
                $type = "'number'    ";
                $initialvalue = "0 ";
            }
            $c++;
            $formfields .= "        \$this->addformfield('', " . str_pad($c,2) .", ".str_pad("'$columnname'",$maxlen).",$initialvalue ,$type);\n";
        }
    }
    $i++;
}

$code = "
&lt;?php
class CRUD$tablenamecamel extends CSNCRUD implements CSNCRUDeventsall {
    function initialize() {
        global \$APP_SESSION;
        // set page access
        \$this->setpageaccess(\$APP_SESSION->getCanCreate(), \$APP_SESSION->getCanUpdate(), \$APP_SESSION->getCanDelete());       
        
        // add top buttons
        \$this->addtopbutton(1, 'list','List','list','success');
        \$this->addtopbutton(1, 'archive','Archive','archive','danger');     
        if (\$APP_SESSION->getCanCreate()) 
            \$this->addtopbutton(0, \$this->createcommand,'New','plus','warning', \$this->opener);

        // grid column definitions [columnname;property1;key2=>property2..]
        \$columns0 = '[recordselector]';
        \$columns1 = '$columns';
        \$columns2 = '[actions]';

        // top button commands, modify parameters if necessary
        \$this->addtopbuttoncommand('list', \"Usp_Get$tablenamecamel '\$this->currentuserid', '[@searchtext]'\", \"\$columns0 \$columns1 \$columns2\",'','');        

        // form fields (variables)
$formfields                     

        // set field properties by batch
        \$this->setfieldsproperties('', '$primarykey','readonly',true);

        // form layout template (multiple fields per row)        
        \$this->addformlayout('', '');
    }

    // required callbacks for CSNCRUDeventsall interface. Remove interface and functions if not needed
    function callback_fetchdata(\$name, \$primarykeyvalue, \$record, \$cs, &\$value) {}
    function callback_fetchsqlcommand(\$commandtype, &\$sql) {}
    function callback_fetchgridtitle(&\$title, &\$icon, \$recordcount) {}
    function callback_fetchformtitle(&\$title, &\$icon) {}
    function callback_fetchformfooterbuttons(&\$footer)  {}
    function callback_fetchgridfooter(&\$footer, \$searchtext, \$totalrecords, \$selectedrecords) {}

    function callback_beforeshowgrid(&\$cancel, \$totalrecords) {}
    function callback_beforeshowfieldsall(&\$form) {}
    function callback_beforegeneratefield(&\$field, &\$html, &\$cancel, \$readonly) {}
    function callback_aftergeneratefield(&\$field, &\$html, \$readonly) {}
    function callback_aftergeneraterowfields(\$linenumber, &\$html, \$readonly) {}
    function callback_aftershowfieldsall(&\$form) {}
    function callback_beforeshowform(&\$html) {}
    function callback_aftershowform(&\$html) {}  

    function callback_post(&\$cancel) {}    

}

\$$tablename = new CRUD$tablenamecamel('$tablenamenoscamel','$primarykey','list', \$APP_SESSION->getuserid(), \$APP_CURRENTPAGE);
\${$tablename}->run();

// Code generated by CSN SQL Generator for PHP
// " . date('m/d/Y h:i:s') . "
";

$code .= "?&gt;
";

$name = 'PHP Code Using CSNCrud Framework';
$boxtool  = HTML::boxtool('IPHPL', HTML::icon('indent'),'Indent','ci');
$boxtool .= HTML::boxtool('OPHPL', HTML::icon('outdent'),'Outdent','co');
$boxtool .= HTML::boxtool('CPHPL', HTML::icon('copy'),'Copy Code to Clipboard','cc');

echo HTML::box(HTML::icon('code',$name), "<pre id='PHPL'>$code</pre>",'','info',true,false,'','',$boxtool);


function executebutton($name, $cancreate, $message) {
    if ($cancreate)
        return HTML::submitbutton($name, HTML::icon('play','Execute'),'danger execute');
    else
        return $message;
}

?>