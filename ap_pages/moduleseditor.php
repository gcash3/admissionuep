<?php
$title = '[Draft]';
$path = APP_CONFIGDIR;
$fndraft = $path . 'modulesdraft.json';
$fncurrent = $path . 'modules.json';

$modulesdraft = @file_get_contents($fndraft);
$modulescurrent = @file_get_contents($fncurrent);

$backupcheck = @$_POST['createbackup'] ? 'checked' : '';

if (count($_POST) == 0) {
    if ($modulesdraft == '') {
        $modulesdraft = $modulescurrent;
        $title = '[Current]';
    }
    if ($modulesdraft == '')
        $modulesdraft = '[]';
    if ($modulescurrent == '')
        $modulescurrent = '[]'; 
    $backupcheck = 'checked';  
}
else {
    $action = @$_POST['action'];
    $modules = @$_POST['modules'];
    $backupcheck = @$_POST['createbackup'] ? 'checked' : '';
    if (APP_PRODUCTION) {
        echo HTML::alert('','Operation not allowed in a PRODUCTION server!');
    }
    elseif ($modules) {
        if ($action == 'Save') {
            if ($modules == $modulesdraft) {
                echo HTML::alert('File Error!','No changes made!','warning');
            }
            else {
                if (@file_put_contents($fndraft, jsontidy($modules)) == 0) {
                    echo HTML::alert('File Error!','Unable to save Draft modules. Please check file permission.');
                }
                else {
                    $APP_SESSION->setApplicationMessage('Draft modules saved.');
                    Tools::redirect($APP_CURRENTPAGE);
                    return;
                }
            }
        }
        elseif ($action == 'SaveApply') {
            if ($modules == $modulescurrent) {
                echo HTML::alert('File Error!','No changes made!','warning');
            }
            else {
                if (@file_put_contents($fncurrent, jsontidy($modules)) == 0) {
                    echo HTML::alert('File Error!','Unable to save and apply modules. Please check file permission');
                }
                else {
                    if ($backupcheck) {
                        $count = count(glob($path.'modules*.bak.json'))+1;
                        if ($count < 99999)
                            $bak = str_pad($count,5,"0",STR_PAD_LEFT);
                        copy($fncurrent, $path . "modules$bak.bak.json");
                    }
                    unlink($fndraft);
                    $APP_SESSION->setApplicationMessage('Modules saved. Please logout and login again to update modules.');
                    Tools::redirect($APP_CURRENTPAGE);
                    return;
                }
            }
        }        
    }
    $modulesdraft = $modules;   
}

function jsontidy($s) {  
    $s = str_replace('[{',"[\n{", $s);    
    $s = str_replace("}]","}\n]",$s);   
    $s = str_replace('},',"},\n", $s);
    $s = str_replace('"children":[',"\n\"children\":\n[",$s);
    return $s;
}
?>



    <div class="row">
        <form method="post" id="form" name="form">          
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border"><i class="fa fa-list"></i> <b>Modules <span id="moduletitle"><?php echo $title; ?></span></b>
                </div>
                <div class="box-body" id="cont">
                    <ul id="myEditor" class="sortableLists list-group">
                    </ul>
                    <div class="checkbox">
                        <label><input type=checkbox name="createbackup" <?php echo @$backupcheck ?>> Backup Current Modules</label>
                    </div>                   
                </div>
                <div class="box-footer">
                    <button id="btnSave" type="button" class="btn btn-success" title="Save as Draft"><i class="glyphicon glyphicon-ok"></i> Save</button> 
                    <button id="btnSaveApply" type="button" class="btn btn-warning" title="Save and Apply"><i class="fa fa-bolt"></i> Apply</button> 
                    <button id="btnReset" type="button" class="btn btn-danger"><i class="fa fa-undo"></i> Reset</button>
                    <button id="btnCurrent" type="button" class="btn btn-info" title="Use Current Modules">Current</button>
                    <input type=hidden name="modules" id="modules">
                    <input type=hidden name="action" id="action">
                </div>
            </div>
        </div>
        </form>


        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><i class="fa fa-pencil"></i> <b> Item Properties</b></div>
                <div class="box-body">
                    <form id="frmEdit" autocomplete="off">
                        <div class="form-group row">
                            <div class="col-sm-6">
                                <b>Text:</b>
                                <div class="input-group">
                                    <input type="text" class="form-control item-menu" name="text" id="text" placeholder="Text">
                                    <span class="input-group-btn">
                                        <button type="button" id="myEditor_icon" class="btn btn-default" data-iconset="fontawesome"></button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <b>Page/href:</b>
                                <input type="text" class="form-control item-menu" id="href" name="href" placeholder="Name">
                            </div>                            
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <b>Page Title:</b>
                                <input type="text" class="form-control item-menu" id="pagetitle" name="pagetitle" placeholder="Page title">
                            </div>
                        </div>                        
                        <input type="hidden" name="icon" class="item-menu">
                        <div class="form-group row">
                            <div class="col-sm-8">
                                <b>Title/Tooltip:</b>
                                <input type="text" class="form-control item-menu" id="title" name="title" placeholder="Title/tooltip">
                            </div>  
                            <div class="col-sm-4">
                                <b>Target:</b>
                                <select name="target" id="target" class="form-control item-menu">
                                    <option value="_self">Self</option>
                                    <option value="_blank">Blank</option>
                                    <option value="_top">Top</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-3">
                                <b>Hidden Item:</b>
                                <select name="hiddenmenu" id="hiddenmenu" class="form-control item-menu">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <b>Dev Only:</b>
                                <select name="devonly" id="devonly" class="form-control item-menu">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <b>Access Required:</b>
                                <select name="accessrequired" id="accessrequired" class="form-control item-menu">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row crud">
                            <div class="col-sm-3">
                                <b>Create:</b><br>
                                <select name="accesscreate" id="accesscreate" class="form-control item-menu">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <b>Read:</b><br>
                                <select name="accessread" id="accessread" class="form-control item-menu">
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <b>Update:</b>
                                <select name="accessupdate" id="accessupdate" class="form-control item-menu">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <b>Delete:</b>
                                <select name="accessdelete" id="accessdelete" class="form-control item-menu">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div> 
                        </div>      
                        <div class="form-group row crud">
                            <div class="col-sm-3">
                                <b>Print:</b>
                                <select name="accessprint" id="accessprint" class="form-control item-menu">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-sm-9">
                                <b>Custom Access:</b>
                                <input type=text name="accesscustom" id="accesscustom" class="form-control item-menu" title="Format: key:description;... Ex L:Cancel;T:Post">
                            </div>
                        </div> 
                        <div class="form-group row">
                            <div class="col-sm-6">
                                <b>Enable From Date:</b>
                                <input type=date name="datefrom" id="datefrom" class="form-control item-menu">
                            </div>
                            <div class="col-sm-6">
                                <b>To Date:</b>
                                <input type=date name="dateto" id="dateto" class="form-control item-menu">
                            </div>                            
                        </div>                                
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <b>Timestamp:</b>
                                <input type=text name="timestamp" id="timestamp" class="form-control item-menu" readonly>
                            </div>
                        </div>                                                                               
                    </form>
                </div>
                <div class="box-footer">
                    <button type="button" id="btnUpdate" class="btn btn-primary" disabled><i class="fa fa-refresh"></i> Update</button>
                    <button type="button" id="btnAdd" class="btn btn-success"><i class="fa fa-plus"></i> Add</button>
                </div>
            </div>
        </div>

    </div>