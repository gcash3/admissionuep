<?php
$google_oauth_version = 'v3';    
// PRODUCTION CONFIG
if (APP_PRODUCTION) {
    $google_oauth_client_id = '171286175436-8j14o3m1sepkccaa3mm737nercudoluq.apps.googleusercontent.com';
    $google_oauth_client_secret = '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';
    $google_oauth_redirect_uri = 'https://www.ue.edu.ph/admissionportal/go_login.php';
    return;
}

// DEV CONFIG 
$google_oauth_client_id = '171286175436-i9rsc2gs318mvsjbqn6ulteg69bib33h.apps.googleusercontent.com';
$google_oauth_client_secret = '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'; 
$google_oauth_redirect_uri = 'http://localhost:8022/admissionportal__dev/go_login.php';

function google_login_button() {
    global $google_oauth_client_id;
    if ($google_oauth_client_id)
    return '<p>&ndash; OR &ndash;</p><a href="go_login.php" class="btn btn-danger btn-sm" title="For UE gmail account holder only. You will be redirected to google."><i class="fa fa-google"></i> Sign in with UE Gmail account</a>';
}
return;