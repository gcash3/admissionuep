<?php
/*------------------------------------------------------------
 * Filename    : snippetscreator.php
 * Description : VSCode Snippet Generator
 * Author      : Chito S. Nuarin
 * Date        : 10/13/2020
 ------------------------------------------------------------*/

$list[] = 'callback_actionbuttons($primarykeyvalue, $record, &$actions) {}';
$list[] = 'callback_aftergeneratefield(&caller, &$html, $readonly) {}';
$list[] = 'callback_aftergeneraterowfields($linenumber, &$html, $readonly)) {}';
$list[] = 'callback_aftersave($command, &$redirect) {}';
$list[] = 'callback_aftershowfieldsall(&$form) {}';
$list[] = 'callback_aftershowform(&$html) {}';
$list[] = 'callback_aftershowgrid(&$html, $results) {}';
$list[] = 'callback_beforegeneratefield(&$field, &$html, &$cancel, $readonly) {}';
$list[] = 'callback_beforesave(&$cancel) {}';
$list[] = 'callback_beforeshowfieldsall(&$form)) {}';
$list[] = 'callback_beforeshowform(&$html) {}';
$list[] = 'callback_beforeshowgrid(&$cancel, $totalrecords) {}';
$list[] = 'callback_fetchcolumns($searchtext, &$columns) {}';
$list[] = 'callback_fetchdata($fieldname, $primarykeyvalue, $record, $cs, &$value) {}';
$list[] = 'callback_fetchdatarow($primarykeyvalue, &$record) {}';
$list[] = 'callback_fetchformfooterbuttons(&$footer) {}';
$list[] = 'callback_fetchformtitle(&$title, &$formicon) {}';
$list[] = 'callback_fetchgridfooter(&$gridfooter, $searchtext, $totalrecords, $selectedrecords) {}';
$list[] = 'callback_fetchgridtitle(&$title, &$gridicon, $recordcount) {}';
$list[] = 'callback_fetchsqlcommand($command, &$sql) {}';
$list[] = 'callback_fetchsqldata($command, $sql, &$handled, &$results) {}';
$list[] = 'callback_fetchurlextra($record, &$urlextra) {}';
$list[] = 'callback_formatprimarykey(&$primarykeyvaluepadded, &$new, $record, &$cancel) {}';

$prefixdescription = "CSNCRUD Event";
$prefixname = "CSNCRUD";

$content = '';
foreach ($list as $key => $value) {
    $title = substr($value, 0, stripos($value,'('));
    $value = str_replace('$','\\\\$',$value);
    $value = str_replace('{}','{\n\t$0\n}\n',$value);
    $content .= "\"$prefixname $title\" : {\n";
    $content .= "    \"prefix\": \"csn.$title\",\n";
    $content .= "    \"body\": \"function $value\",\n";
    $content .= "    \"description\": \"$prefixdescription $title\"\n";
    $content .= "},\n";   
}
$content = '<pre>' . $content . '</pre>';
echo HTML::box('Box Title',$content,'Boox Footer','success',true,true,'');
?>