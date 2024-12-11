<?php
/*--------------------------------------------------------------------------------------------------------
  Project     : UE Admin Portal
  File        : main.php
  Author      : CSN-10/02/2017
  Description : Main entry point
  Ugly Usage  : main.php?page=module&p1=v1&p2=v2&p3=v3/pn=vn

  Rewrite Rule: RewriteRule ^(.*)$ main.php?page=$1 [QSA,L]
  Clean URL   : module/p1/p2/p3/pn
  
  AJAX call POST data requirement: {ajax=1} or URL contains plain (module/p1/p2/p3/pn/plain)
  
  Important
  ----------------
  If using clean URL, relative path will not work.
  Prepend APP_BASE in all href attribute including ajax call. 
  
  Global variables
  ----------------
  $APP_SESSION            : object SessionProvider, session handler   (class.SessionProvider.php)
  $APP_MODULES            : array, modules
  $APP_DEVMODULES         : array, developmment modules
  $APP_DBCONNECTION       : object CSNmssql, database connection
  $APP_DEBUGCONTENT       : string, debug information
  $APP_CURRENTPAGE        : string, requested page in URl
  $APP_CURRENTPAGEDETAILS : array, requested page details
  $APP_AJAXOUTPUT         : boolean, ajax output only
  $APP_COMMAND            : string, command in URL (page/command/)
  
  Constants
  ----------------
  APP_DIR                 : Directory of the application
  APP_BASE                : Root directory of the application
  See config.php for other constants
--------------------------------------------------------------------------------------------------------*/
$APP_DEVMODULES     = array('clearanceviewer','clearance','dtr');
$APP_OFFLINEMODULES = array();

require_once('ap_php/UE.config.php');
require_once('ap_php/UE.modules.php');


$APP_DEBUGCONTENT = '';
$APP_AJAXOUTPUT   = isset($_POST['ajax']);
$APP_CURRENTPAGE  = @$_GET['page'];

// transfrom clean URL to parameters
Tools::transformURL();
$APP_COMMAND = @$_GET['_p1'] . '';

// check if session is active
if (!$APP_SESSION->isLogin() && !@in_array('nl', $_GET)) {
    if ($APP_AJAXOUTPUT) {
        if ($APP_CURRENTPAGE != 'downloader')
            echo HTML::windowclose();
    } 
    else {
        header('Location: ' . APP_BASE . 'login.php');
    }
    return;
}



// check session and cookie
if (@$_COOKIE['adminportal'] != sha1("@@@;chitonian;" . $APP_SESSION->getUsername() . ";programming;255;@@@")) {
    if ($APP_AJAXOUTPUT)
        echo HTML::windowclose();
    else
        header('Location: ' . APP_BASE . 'login.php');
    return;
}


// open database connection
$APP_DBCONNECTION = Data::openconnection(true);

// output header
if (!$APP_AJAXOUTPUT)
    require_once('ap_template/header.php');


// set default page
if ($APP_CURRENTPAGE == '')
    $APP_CURRENTPAGE = 'dashboard';

if (!$APP_AJAXOUTPUT) {
    if ($APP_CURRENTPAGE != 'signout') {
        if ($APP_SESSION->getMustChangePassword() ) {
            $APP_CURRENTPAGE = 'changepassword';
        }
    }
 
    $APP_CURRENTPAGEDETAILS = getmenudetails($APP_CURRENTPAGE, $APP_MODULES);
    if ($APP_CURRENTPAGEDETAILS == null) {
        $APP_CURRENTPAGE = 'error404';
    }
    if (array_key_exists($APP_CURRENTPAGE, $APP_MODULES)) {
        if (!$APP_SESSION->getModuleAccess($APP_CURRENTPAGE) && DEBUG_CHECKACCESS)
            if ($APP_CURRENTPAGEDETAILS[1])
                $APP_CURRENTPAGE = 'error/forbidden';
    }
    require_once('ap_template/navigation.php');
}
if ($APP_DBCONNECTION->connected) {
    if ($APP_AJAXOUTPUT) {
        ob_clean();
        if (file_exists("ap_ajax/$APP_CURRENTPAGE.php")) {
            include_once("ap_ajax/$APP_CURRENTPAGE.php");
        }
        else {
            echo "Sorry: Requested page is not yet available: $APP_CURRENTPAGE";
        }
    }
    else {
        if (!file_exists("ap_pages/$APP_CURRENTPAGE.php")) {
            echo "<section class='content'>";
            include_once("ap_pages/error/404.php");
        }
        else {
            if ($APP_CURRENTPAGEDETAILS)
                echo HTML::contentheader("<i class='$APP_CURRENTPAGEDETAILS[3]'></i> $APP_CURRENTPAGEDETAILS[7]",'', HTML::pagetree());
            echo "<section class='content'>";
            echo "<div class='loader alert alert-warning overlay hidden'><i class='fa fa-refresh fa-spin'></i> Loading page. Please wait...</div>";
            $applicationmessage = $APP_SESSION->getApplicationMessage(true, $class);
            if ($applicationmessage != '') 
                echo HTML::alert('Attention', $applicationmessage, $class);
            $applicationmessage = $APP_SESSION->getEmailPreview(true);
            if ($applicationmessage != '') 
                echo HTML::box('Email Preview', $applicationmessage,null, 'primary', true, true);            
            if (in_array($APP_CURRENTPAGE, $APP_OFFLINEMODULES))
                include_once("ap_pages/error/offline.php");
            else
                include_once("ap_pages/$APP_CURRENTPAGE.php");
        }
        if (($APP_DEBUGCONTENT != '') && !APP_PRODUCTION) {
            if (is_callable($APP_DEBUGCONTENT))
                $APP_DEBUGCONTENT = print_r($APP_DEBUGCONTENT(), true);
            echo "<pre class='no-print'>$APP_DEBUGCONTENT</pre>";
        }
        echo '</section>';
    }
}
else {
    echo "<section class='content'>";
    include_once("ap_pages/error/connection.php");    
    echo '</section>';
}
if (!$APP_AJAXOUTPUT) {
    require_once('ap_template/footer.php');
}
?>