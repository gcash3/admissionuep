<?php
    $loginpage = $APP_SESSION->getLoginPage();
    $APP_SESSION->session_destroy();
    header("location: $loginpage.php");
?>