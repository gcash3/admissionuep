<?php
// ----------------------------------------------------------------
// This file is used to convert hashed password to plain text
// To prevent plaintext password submitted in $_POST
//
// Author: CSN-06/19/2022
// Requires ap_js/lpl.min.js
//
// Steps:
// 1. Include this file in the login page (before <html>)
// 2. Call createlogintoken() before </form>
// 3. Include ap_js/lpl.js (before </html>)
// ----------------------------------------------------------------
define('FIELDNAME_PASSWORD','s');
define('FIELDNAME_PASSWORDHASHED','s2');
define('FIELDNAME_TOKEN', 'tk');
define('FIELDNAME_ANTIBOT', 'ab');

// check if password submitted is hashed 
if (isset($_POST[FIELDNAME_TOKEN]) && isset($_POST[FIELDNAME_PASSWORD]) && 
    isset($_POST[FIELDNAME_PASSWORDHASHED]) && isset($_POST[FIELDNAME_ANTIBOT]) ) {
    decodepasssword();
}

// create random hidden token
function createlogintoken() {
    $token = hash('sha256', "CSN" . mt_rand() .  mt_rand() .  mt_rand() . $_SERVER['HTTP_USER_AGENT']);
    echo "<input type='hidden' value='$token' name='" . FIELDNAME_TOKEN . "' id='" . FIELDNAME_TOKEN . "'>";
}

// decode hashed password using brute force
// each character is converted to hash 256 (<cs>:<token>:<character>:<antibot>:<useragent>:<pos>:<n>), semicolon separated
// loop ascii character 32-127
function decodepasssword() {
    $s2 = explode(';', $_POST[FIELDNAME_PASSWORDHASHED]);
    $t = $_POST[FIELDNAME_TOKEN];
    $s = '';
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $ab = $_POST[FIELDNAME_ANTIBOT];
    $i = 0;
    foreach ($s2 as $hashedchar) {
        if (trim($hashedchar)) {
            $valid = false;
            for ($a=32; $a<127; $a++) {
                $p = chr($a);
                if ($hashedchar == hash('sha256', "cs:$t:$p:$ab:$ua:$i:n")) {
                    $s .= $p;
                    $valid = true;
                    break;
                }
            }
            if (!$valid)
                break;
        }
        $i++;
    }
    $_POST[FIELDNAME_PASSWORD] = $s;
}
?>