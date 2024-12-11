<?php
/*
$APP_MODULES structure:

key = pagename / code
0   = visible in the menu | hidden
1   = access required
2   = caption
3   = icon
4   = sub menu items
5   = tool tip
6   = required access (c;r;u;d;p)
7   = page title
8   = target window
*/
$dashboard    = $APP_SESSION->getDashboard() != 'dashboard' ? 'Home' : 'Dashboard';
$dasboardicon = $APP_SESSION->getDashboard() != 'dashboard' ? 'home' : 'dashboard';
Tools::addmodule($APP_MODULES, 'dashboard', true,  false,  $dashboard, "fa fa-$dasboardicon");
$demo = APP_DEMO ? '.demo' : '';
$modules = file_get_contents(APP_CONFIGDIR . "modules$demo.json");
if ($modules == '')
    $modules = file_get_contents(APP_CONFIGDIR . "modules.json");
if ($modules == '')
    $modules = '[]';
$modules = json_decode($modules);
$withmoduleseditor = false;
if (!is_array($modules))
    $modules = array();
foreach ($modules as $module) {
    $submodules = null;
    if (@$module->children) {
        foreach ($module->children as $child) {
            $withmoduleseditor = $withmoduleseditor ||  ($child->href == 'moduleseditor');
            $devonly = @$child->devonly == true;
            $requiredaccess = 'R';   
            $requiredaccess .= @$child->accesscreate == 1 ? ';C' : '';
            $requiredaccess .= @$child->accessupdate == 1 ? ';U' : '';
            $requiredaccess .= @$child->accessdelete == 1 ? ';D' : '';
            $requiredaccess .= @$child->accessprint == 1  ? ';P' : '';
            $requiredaccess .= @$child->accesscustom  ? (';' .@$child->accesscustom) : '';
            $validdate = true;
            if (@$child->datefrom) {
                if (date('Ymd') < Data::formatdate($child->datefrom,'Ymd'))
                    $validdate = false;
            }
            if (@$child->dateto) {
                if (date('Ymd') > Data::formatdate($child->dateto,'Ymd'))
                    $validdate = false;
            }         
            $newlabel = ((ceil(time() - strtotime(@$child->timestamp)) / 86400) < 30) && !is_array(null) ? ' <sup class="text-yellow">New</sup>' : '';
            if ($validdate && ((!APP_PRODUCTION && $devonly) || !$devonly))  {
                Tools::addmodule($submodules, 
                                $child->href, 
                                $child->hiddenmenu==false, 
                                $child->accessrequired == true,
                                $child->text . $newlabel,
                                $child->icon,
                                null,
                                $child->title,
                                $requiredaccess,
                                @$child->pagetitle,
                                @$child->target);       
            }                
        }
    }    

    $devonly = @$module->devonly == true;
    $requiredaccess = 'R';
    $requiredaccess .= @$module->accesscreate == 1 ? ';C' : '';
    $requiredaccess .= @$module->accessupdate == 1 ? ';U' : '';
    $requiredaccess .= @$module->accessdelete == 1 ? ';D' : '';
    $requiredaccess .= @$module->accessprint == 1  ? ';P' : '';
    $requiredaccess .= @$module->accesscustom  ? (';' .@$module->accesscustom) : '';    
    $validdate = true;
    if (@$module->datefrom) {
        if (date('Ymd') < Data::formatdate($module->datefrom,'Ymd'))
            $validdate = false;
    }
    if (@$module->dateto) {
        if (date('Ymd') > Data::formatdate($module->dateto,'Ymd'))
            $validdate = false;
    }     
    $newlabel = ((ceil(time() - strtotime(@$module->timestamp)) / 86400) < 30) && !is_array($submodules) ? ' <sup class="text-yellow">New</sup>' : '';
    $withmoduleseditor = $withmoduleseditor ||  ($module->href == 'moduleseditor');
    if ($validdate && ((!APP_PRODUCTION && $devonly) || !$devonly)) {
        Tools::addmodule($APP_MODULES, 
                        $module->href, 
                        $module->hiddenmenu==false, 
                        $module->accessrequired == true,
                        $module->text . $newlabel,
                        $module->icon,
                        $submodules,
                        $module->title,
                        $requiredaccess,
                        @$module->pagetitle,
                        @$module->target); 
    }                    
}
unset($modules);
unset($submodules);

if (!APP_PRODUCTION && !$withmoduleseditor) {
    Tools::addmodule($APP_MODULES, 'moduleseditor', true,  false,  'Modules Editor','fa fa-th-large');
}
Tools::addmodule($APP_MODULES, 'about', true, false, 'About', 'fa fa-coffee');
Tools::addmodule($APP_MODULES, 'signout', true, false, 'Sign Out', 'fa fa-sign-out');


function getparentmenu($currentpage, $subkey, $modules) {
    $parentkey = '';
    foreach ($modules as $key => $module) {
        if (isset($module[4]) && is_array($module[4])) {
            $parentkey = getparentmenu($currentpage, $key, $module[4]);
            if ($parentkey != '')
                return $parentkey;
        }
        if ($key == $currentpage)
            return $subkey;
    }
}

function getmenudetails($requestedpage, $modules) {
    $menu = null;
    foreach ($modules as $key => $module) {
        if (isset($module[4]) && is_array($module[4])) {
            $menu = getmenudetails($requestedpage, $module[4]);
            if ($menu != null)
                return $menu;
        }
        if ($key == $requestedpage)
            return $module;
    }
    return $menu;
}

?>


