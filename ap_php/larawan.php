<?php
/*----------------------------------------------------------
  File        : larawan.php
  Author      : CSN-10/20/2017
  Description : Load image from the database
  Usage       : larawan.php?p=base64(sn)/checksum

  Rewrite Rule: ^selfie/(.*)$ ap_php/larawan.php?p=$1 [QSA,L]
  Clean URL   : selfie/base64(sn)/checksum
 ----------------------------------------------------------*/
require_once('UE.config.php'); 

$_GETURL = explode('/',$_GET['p']);
$id = base64_decode(@$_GETURL[0]) + 0; // numbers only

if (/*APP_PRODUCTION && */$APP_SESSION->isLogin() && Crypto::validateimagechecksum($id, @$_GETURL[1])) {
    if ($id <> "") {
        $db = Data::openconnection();
        if ($db->connected) {
            $image = $db->execute("select PictureImage from PictureDatabase.dbo.Picture where PictureID = '$id' AND PictureImage IS NOT NULL");
            if (is_array($image) && count($image)) {
                if (cropimage($image[0]["PictureImage"]))
                    return;
                header('Content-Type: image/bmp');
                echo $image[0]["PictureImage"];
                return;
            }
        }
    }
    else {
    }
}
header("Content-type: image/jpg");
echo @file_get_contents("../ap_img/blank.jpg");
return;

function cropimage($bmp, $w=80, $h=80) {
    @include_once('ImageCreateFromBMP.php');
    if (is_callable('ImageCreateFromBMP') == false) 
        return;
    $image = @ImageCreateFromBMP('data://text/plain;base64,' . base64_encode($bmp));
    if ($image === false)
        $image = @imagecreatefromstring($bmp);
    if ($image == false) 
        return;
    $image = @imagescale($image, $w, $h);
    if ($image === false)
        return;
    header('Content-Type: image/jpeg');
    @imagejpeg($image);
    @imagedestroy($image);
    return true;
}
?>
