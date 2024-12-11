<?php
ob_start();
date_default_timezone_set('Asia/Manila');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("X-Frame-Options: SAMEORIGIN");
session_start();

define('APP_ADMINPORTAL', true);                         // admission portal

include_once('UE.class.SessionProvider.php');
include_once('UE.class.CSNdataproviderInterface.php');   

// chose one only in the production
include_once('UE.class.CSNmssql.php');                   // mssql_* database functions
//include_once('class.CSNsqlsrv.php');                   // sqlsvr_* database functions
define('APP_DB_DRIVER', 'mssql');

include_once('UE.class.Data.php');                       // static and dyanamic data (array)
include_once('UE.class.Tools.php');                      // general purpose functions
include_once('UE.class.HTML.php');                       // bootstrap html generating functions
include_once('UE.class.Crypto.php');                     // checksum functions
include_once('UE.class.CSNCRUD.php');


define('APP_DEMO', stripos($_SERVER['REQUEST_URI'],'demo') !== false);
define('APP_PRODUCTION', (stripos($_SERVER['REQUEST_URI'],'__dev') === false) && !APP_DEMO);
define('APP_VERSION', '1.0 ' . (APP_PRODUCTION ? '' : ' (dev)') );


define('APP_TITLE',  'AdmissionPortal');
define('APP_TITLE2', 'Admission Portal');
if (strpos($_SERVER['REQUEST_URI'],'DEMO/admissionportal') !== false)
    define('APP_DIR', 'DEMO/admissionportal');
else
    define('APP_DIR', APP_PRODUCTION ? 'admissionportal' : 'admissionportal__dev');
define('APP_BASE', substr(dirname($_SERVER['REQUEST_URI']),0, stripos($_SERVER['REQUEST_URI'],APP_DIR)) . APP_DIR . '/');

define('APP_MODULENAME', 'ADMSPORTAL');

$APP_SESSION = new SessionProvider(APP_TITLE);

define('APP_UPLOADDIR', 'ap_fileuploads/');
define('APP_CONFIGDIR', 'ap_config/');    


// database settings
$test = '';     //'Test';
define('APP_DB_LOGIN', 'admpt');
define('APP_DB_PASSWORD', 'ULPeAh3MBa1K5igJMoNhsuCppW0BYB');
define('APP_DB_DATABASE', '[UE Database' . (APP_PRODUCTION  && (!$APP_SESSION->getDemo()) ? '' : $test) . ']');
define('APP_DB_DATABASEPAYROLL', 'Payroll' . (APP_PRODUCTION  && (!$APP_SESSION->getDemo()) ? '' : $test));
define('APP_DB_DATABASEPMIS', 'PMIS' . (APP_PRODUCTION  && (!$APP_SESSION->getDemo()) ? '' : $test));
define('APP_DB_DATABASEADMISSION', 'OnlineAdmission' . (APP_PRODUCTION  && (!$APP_SESSION->getDemo()) ? '' : $test));
define('APP_DB_SERVERNAME', 'dcmlaonls1');

// debuging settings
define('DEBUG_CHECKACCESS', true);
define('DEBUG_WITH_DEMO', true);
define('DEBUG_CHECKPASSWORD', false || APP_PRODUCTION);


// prevent RFI attack
@stream_wrapper_unregister ('http');
@stream_wrapper_unregister ('https');
@stream_wrapper_unregister ('ftp');

ini_set("mssql.textlimit", 500000);
ini_set("mssql.textsize", 500000);
ini_set('mssql.charset','CP1252'); 
ini_set('default_charset', 'utf-8');

define('APP_UPLOAD_ENABLE', is_writable(APP_UPLOADDIR));

define('APP_EMAIL_SEND',        APP_PRODUCTION && !$APP_SESSION->getDemo());             // send real email
define('APP_EMAIL_PREVIEW',     APP_PRODUCTION == false);                                // preview email message
define('APP_EMAIL_TESTACCOUNT', APP_PRODUCTION == false);                                // use test email address  (user.name.test@ue.edu.ph)

$_POST   = unserialize(str_replace("'","`", serialize($_POST)));
$_GET    = unserialize(str_replace("'","`", serialize($_GET)));
$_COOKIE = unserialize(str_replace("'","`", serialize($_COOKIE)));
?>
