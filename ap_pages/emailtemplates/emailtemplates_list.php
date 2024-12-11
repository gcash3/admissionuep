<?php
//---------------------------------------------------------------
// File        : emailtemplate_list.php
// Description : Email Template - List
// Author      : CSN
// Date        : 10/05/2018
// --------------------------------------------------------------
$buttons = '<div class="buttons">';
$i=0;
foreach ($titles as $key => $title) {
    $active = $command == $key ? ' active' : '';
    $from = isset($title[2]) ? $title[2] : $key;
    $buttons .= HTML::linkbutton("$APP_CURRENTPAGE/$key/0/0/0/$from","$title[0]",$title[1].$active,"btn_$key");
    $i++;
}
$buttons .= '</div>&nbsp;';

if ($command != 'new')
    echo "$buttons";

$withrownumber = true;

// list documents 
$columns['TemplateIDLink']     = 'ID';
$columns['Description']        = 'Description';
$columns['Subject']            = 'Subject';
$columns['CreatedBy']          = array('Created by', array('Tools','pictureurl'), "<img src='@1' class='img-circle img-xs' title='@0'> @0");
$columns['CreatedDate']        = array('Date','date','m/d/y h:i');
$columns['ModifiedBy']         = array('Modified by', array('Tools','pictureurl'), "<img src='@1' class='img-circle img-xs' title='@0'> @0");
$columns['ModifiedDate']       = array('Date','date','m/d/y h:i');
$columns['Revision']           = 'Revision';

$boxtitle = '';
$results = null;
$sql = '';
$tableattributes = '';

if ($command == 'current') {
    $sql = "Usp_AP_GetEmailTemplates '$UserEmployeeCode', 0, '" . APP_MODULENAME . "'";
}
elseif ($command == 'deleted') {
    $sql = "Usp_AP_GetEmailTemplates '$UserEmployeeCode', 1, '" . APP_MODULENAME . "'";

    $columns['DeletedBy']   = array('Deleted by', array('Tools','pictureurl'), "<img src='@1' class='img-circle img-xs' title='@0'> @0");
    $columns['DeletedDate'] = array('Date','date','m/d/y h:i');
}
elseif ($command == 'all') {
    $sql = "Usp_AP_GetEmailTemplates '$UserEmployeeCode', 2, '" . APP_MODULENAME . "'";

    $columns['DeletedBy']   = array('Deleted by', array('Tools','pictureurl'), "<img src='@1' class='img-circle img-xs' title='@0'> @0");
    $columns['DeletedDate'] = array('Date','date','m/d/y h:i');
}

if (($results == null) && $sql) {
    $results = $APP_DBCONNECTION->execute($sql);
    if (!is_array($results)) {
        $grid = HTML::alert('Alert','No records found!!' . $sql);
    }
    else {
        $actions = '';
        
            for ($i=0; $i<count($results); $i++) {
                $TemplateID = $results[$i]['TemplateID'];
                $cs = Crypto::DTchecksum($TemplateID) . '/' . mt_rand();
                $actions  = '';
                
                if ($command == 'current') {
                    if ($APP_SESSION->getCanUpdate())
                        $actions .= HTML::linkbutton("$APP_CURRENTPAGE/edit/$TemplateID/$cs/$command",  HTML::icon('pencil'), 'xs bg-blue',  'bed', '', 'Edit Email Template') . '&nbsp;';
                    if ($APP_SESSION->getCanDelete())
                        $actions .= HTML::linkbutton("$APP_CURRENTPAGE/delete/$TemplateID/$cs/$command",HTML::icon('trash'),  'xs bg-red',   'bdr', '', 'Delete Email Template') . '&nbsp;';
                }
                $actions .= HTML::linkbutton("$APP_CURRENTPAGE/preview/$TemplateID/$cs/$command",HTML::icon('eye'),  'xs bg-green',   'bdr', '', 'Preview Email Template') . '&nbsp;';
                

                $viewcommand = 'view';
            
                $results[$i]['Actions'] = $actions;
                $results[$i]['TemplateIDLink'] = HTML::link("$APP_CURRENTPAGE/$viewcommand/$TemplateID/$cs/$command",Data::padleft($results[$i]['TemplateID'],6),'View Template');
            }
            if ($actions)
                $columns['Actions'] = 'Actions';

        $grid = HTML::datatable("{$command}_table", $columns, $results, '',$tableattributes);
        
        if (count($results)==0)
            $grid .= '<br><b>No records found!</b>';
    }

    echo HTML::box($titles[$command][0], $grid, '',$titles[$command][1],true,false,'table-responsive');
}


 
?>
