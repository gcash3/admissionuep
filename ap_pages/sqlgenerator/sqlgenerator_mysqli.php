<?php
// CHECK IF USER CAN CREATE STORED PROCEDURES
$cancreate = false;
$errormessage = 'User must have SUPER PRIVILEGE to create Stored Procedure';
$sql = "SELECT User FROM mysql.user where user = '". APP_DB_LOGIN . "' AND Super_priv = 'Y'";
$sql = 'show grants';
$results = $APP_DBCONNECTION->execute($sql, false);
if (!Tools::emptydataset($results)) {
    $keyword = "GRANT SUPER ON *.* TO '" . APP_DB_LOGIN . "'@'%'"; 
    foreach ($results as $fields) {
        foreach ($fields as $name=>$value) {
            if (substr($value,0,strlen($keyword)) == $keyword) {
                $cancreate = true;
            break;
            }
        }
        if ($cancreate) break;
    }
}



$tableprefix = $tablename[0] . '.';
//-------------------------------------------------------------------------
// Usp_Get[TableName] (with s) 
//-------------------------------------------------------------------------
$name = "Usp_Get" . ucfirst($tablename);
$sql = "
DROP PROCEDURE IF EXISTS `[_DBNAME_]`.`$name`;
CREATE DEFINER=`root`@`localhost` PROCEDURE `[_DBNAME_]`.`$name`
(
IN p_currentuserid INT,
IN p_filter VARCHAR(100)
)
SP:
BEGIN
SET p_filter = CONCAT(p_filter,'%');
SELECT [_COLUMNS_]
FROM $tablename $tablename[0]
LIMIT 100;
END";

$sql = str_replace('[_COLUMNS_]', $tableprefix.implode(",\n       ".$tableprefix, array_keys($columns)), $sql);
$sql = str_replace('[_DBNAME_]', APP_DB_DATABASE, $sql);
$sqlgets = $sql;
$boxtool = HTML::boxtool('CGETS', HTML::icon('copy'),'Copy Code to Clipboard','cc');
$button =  executebutton('ExecuteGets', $cancreate, $errormessage);
$htmlgets = HTML::box(HTML::icon('search',$name), "<pre id='GETS'>$sql</pre>",$button,'success',true,false,'','',$boxtool);

//-------------------------------------------------------------------------
// Usp_Get[TableName] (without s)
//-------------------------------------------------------------------------
$name = "Usp_Get" . ucfirst(substr($tablename,0,strlen($tablename)-1));
$sql = "
DROP PROCEDURE IF EXISTS `[_DBNAME_]`.`$name`;
CREATE DEFINER=`root`@`localhost` PROCEDURE `[_DBNAME_]`.`$name`
(
IN p_currentuserid INT[_PARAMETERS_]
)
SP:
BEGIN
SELECT [_COLUMNS_]
FROM $tablename $tablename[0]
[_CONDITIONS_]
LIMIT 1;
END";

$maxlen  = 0;
$maxlen2 = 12;
foreach ($conditioncolumns as $key => $value) {
    if (isset($column_array[$value])) {
        $maxlen = max($maxlen, strlen($value));
        $maxlen2 = max($maxlen2, strlen($column_array[$value]));
    }
}
$conditions = '';
$parameters = '';
foreach ($conditioncolumns as $key => $value) {
    if (isset($column_array[$value])) {
         $parameters .= ",\nIN p_" .  $column_array[$value];
         $conditions .= ($conditions ? " AND\n      " : "") . $tableprefix . str_pad($value,$maxlen) . " = p_" . str_pad($value,$maxlen);
    }
}
if ($conditions)
    $conditions = "WHERE $conditions";

$sql = str_replace('[_COLUMNS_]',    $tableprefix.implode(",\n       ".$tableprefix, array_keys($columns)), $sql);
$sql = str_replace('[_PARAMETERS_]', $parameters, $sql);
$sql = str_replace('[_CONDITIONS_]', $conditions, $sql);
$sql = str_replace('[_DBNAME_]', APP_DB_DATABASE, $sql);
$sqlget = $sql;

$boxtool = HTML::boxtool('CGET', HTML::icon('copy'),'Copy Code to Clipboard','cc');
$button = executebutton('ExecuteGet', $cancreate, $errormessage);
$htmlget = HTML::box(HTML::icon('folder-o',$name), "<pre id='GET'>$sql</pre>",$button,'success',true,false,'','',$boxtool);

//-------------------------------------------------------------------------
// Usp_Delete[TableName]
//-------------------------------------------------------------------------
$name = "Usp_Delete" . ucfirst(substr($tablename,0,strlen($tablename)-1));
$sql = "
DROP PROCEDURE IF EXISTS `[_DBNAME_]`.`$name`;
CREATE DEFINER=`root`@`localhost` PROCEDURE `[_DBNAME_]`.`$name`
(
IN p_currentuserid INT[_PARAMETERS_]
)
SP:
BEGIN
DECLARE v_deleted bit;
SET v_deleted = 0;

DELETE FROM $tablename
[_CONDITIONS_]
LIMIT 1;

SET v_deleted = ROW_COUNT();

SELECT v_deleted as deleted
WHERE v_deleted = 1;
END";

$conditions = '';
foreach ($conditioncolumns as $key => $value) {
    if (isset($column_array[$value])) {
           $conditions .= ($conditions ? " AND\n      " : "")  . str_pad($value,$maxlen) . " = p_" . str_pad($value,$maxlen);
    }
}
if ($conditions)
    $conditions = "WHERE $conditions";
$sql = str_replace('[_COLUMNS_]',    $tableprefix.implode(",\n       ".$tableprefix, array_keys($columns)), $sql);
$sql = str_replace('[_PARAMETERS_]', $parameters, $sql);
$sql = str_replace('[_CONDITIONS_]', $conditions, $sql);
$sql = str_replace('[_DBNAME_]', APP_DB_DATABASE, $sql);
$sqldelete = $sql;

$boxtool = HTML::boxtool('CDEL', HTML::icon('copy'),'Copy Code to Clipboard','cc');
$button = executebutton('ExecuteDelete', $cancreate, $errormessage);
$htmldelete = HTML::box(HTML::icon('remove',$name), "<pre id='DEL'>$sql</pre>",$button,'danger',true,false,'','',$boxtool);


//-------------------------------------------------------------------------
// Usp_Save[TableName]
//-------------------------------------------------------------------------
$name = "Usp_Save" . ucfirst(substr($tablename,0,strlen($tablename)-1));
$sql = "
DROP PROCEDURE IF EXISTS `[_DBNAME_]`.`$name`;
CREATE DEFINER=`root`@`localhost` PROCEDURE `[_DBNAME_]`.`$name`
(
IN p_currentuserid INT[_PARAMETERS_]
)
SP:
BEGIN
DECLARE v_modified bit;
SET v_modified = 0;

IF EXISTS
    (
    SELECT * FROM $tablename
    [_CONDITIONS_]
    ) THEN
    UPDATE $tablename
    SET    [_UPDATECOLUMNS_]
    [_CONDITIONS_]
    LIMIT 1;
    SET v_modified = ROW_COUNT();
ELSE
    INSERT INTO $tablename
          (
          [_INSERTFIELDS_]
          )
    VALUES(
          [_INSERTVALUES_]
          );
    SET p_[_PRIMARYKEY_] = LAST_INSERT_ID();
    SET v_modified   = 1;
END IF;

SELECT [_PRIMARYKEY_],
       v_modified as modified
FROM $tablename p
WHERE [_PRIMARYKEY_] = p_[_PRIMARYKEY_];
END";

$maxlen  = 0;
foreach ($conditioncolumns as $key => $value) {
    if (isset($column_array[$value])) {
        $maxlen = max($maxlen, strlen($value));
    }
}
$maxlen2  = 0;
foreach ($column_array as $key => $value) {
    if (!isset($resultcolumns[$value])) {
        $maxlen2 = max($maxlen2, strlen($key));
    }
}
$parameters = '';
$columns    = '';
$updatecolumns = '';
$insertfields = '';
$insertvalues = '';
foreach ($column_array as $key => $value) {
    if (!isset($resultcolumns[$value])) {
        if (!in_array($key, $resultcolumns))
            $parameters    .= ",\nIN p_" .  $column_array[$key];
        $columns       .= ($columns ? ",\n           " : '')  . str_pad($key,$maxlen2) . " = p_" .  $key;
        if (!in_array($key, $conditioncolumns) && !in_array($key, $resultcolumns)) 
            $updatecolumns .= ($updatecolumns ? ",\n           " : '')  . str_pad($key,$maxlen2) . " = p_" .  $key;
        if (!in_array($key, $resultcolumns)) { 
            $insertfields  .= ($insertfields ? ",\n          " : "") . $key;
            $insertvalues  .= ($insertvalues ? ",\n          " : "") . "p_$key";
        }
    }
}
if ($conditions)
    $conditions = "WHERE $conditions";
$conditions = '';
foreach ($conditioncolumns as $key => $value) {
    if (isset($column_array[$value])) {
         $conditions .= ($conditions ? " AND\n          " : "")  . str_pad($value,$maxlen) . " = p_" . str_pad($value,$maxlen);
    }
}
if ($conditions)
    $conditions = "WHERE $conditions";

reset($column_array);
$primarykey = key($column_array);

$sql = str_replace('[_COLUMNS_]',    $columns, $sql);
$sql = str_replace('[_UPDATECOLUMNS_]', $updatecolumns, $sql);
$sql = str_replace('[_PARAMETERS_]', $parameters, $sql);
$sql = str_replace('[_CONDITIONS_]', $conditions, $sql);
$sql = str_replace('[_INSERTFIELDS_]',$insertfields, $sql);
$sql = str_replace('[_INSERTVALUES_]', $insertvalues, $sql);
$sql = str_replace('[_PRIMARYKEY_]', $primarykey, $sql);
$sql = str_replace('[_DBNAME_]', APP_DB_DATABASE, $sql);
$sqlsave = $sql;

$boxtool = HTML::boxtool('CSAVE', HTML::icon('copy'),'Copy Code to Clipboard','cc');
$button = executebutton('ExecuteSave', $cancreate, $errormessage);
$htmlsave = HTML::box(HTML::icon('floppy-o',$name), "<pre id='SAVE'>$sql</pre>",$button,'warning',true,false,'','',$boxtool);


//-------------------------------------------------------------------------
// COLUMN DEFINITON
//-------------------------------------------------------------------------
$name = 'Column Definition';
$sql = implode(",\n", $column_array);
$boxtool  = HTML::boxtool('ICOLS1', HTML::icon('indent'),'Indent','ci');
$boxtool .= HTML::boxtool('OCOLS1', HTML::icon('outdent'),'Outdent','co');
$boxtool .= HTML::boxtool('CCOLS1', HTML::icon('copy'),'Copy Code to Clipboard','cc');
$htmlcolumns = HTML::box(HTML::icon('list',$name), "<pre id='COLS1'>$sql</pre>",'','info',true,false,'','',$boxtool);

//-------------------------------------------------------------------------
// COLUMN NAMES
//-------------------------------------------------------------------------
$name = 'Column Names';
$sql = implode(",\n", array_keys($column_array));
$boxtool  = HTML::boxtool('ICOLS2', HTML::icon('indent'),'Indent','ci');
$boxtool .= HTML::boxtool('OCOLS2', HTML::icon('outdent'),'Outdent','co');
$boxtool .= HTML::boxtool('CCOLS2', HTML::icon('copy'),'Copy Code to Clipboard','cc');

$htmlcolumnnames =  HTML::box(HTML::icon('list',$name), "<pre id='COLS2'>$sql</pre>",'','info',true,false,'','',$boxtool);

?>