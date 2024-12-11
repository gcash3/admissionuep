<link  href="plugins/cookies-message/cookies-message.min.css" rel="stylesheet" />
<script src='plugins/cookies-message/cookies-message.min.js'></script>
<script>
$(document).ready(function () {
    $.CookiesMessage({
        closeEnable: false, 
        infoUrl: "https://www.ue.edu.ph/mla/data-privacy-notice/",
        messageText: "We use cookies to ensure you get the best experience on our website, if you continue to browse you'll be acconsent with our privacy policy.",
        acceptText: "Got It",
        cookieExpire: 1
    });
    $('#band-cookies-info').prop('target','_blank');
    $('#s').on('mousedown touchstart',function(e){
        e.preventDefault();        
        $(this).focus();
        $(this).prop('type','text').next('span').removeClass('fa-key fa-eye-slash').addClass('fa-eye');
    }).on('mouseup touchend blur', function(e){
        e.preventDefault();
        $(this).prop('type','password').next('span').removeClass('fa-eye').addClass('fa-eye-slash');
    });
});
</script>
<style>
.login-page {
  background-image: url("/portals/common/img/login<?php echo mt_rand(0,2); ?>.jpg");
 }    
</style>