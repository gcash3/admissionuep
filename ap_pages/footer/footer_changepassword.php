<script>
$(document).ready(function () {
    $("#newpassword").attr("pattern", '(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])\\S{6,}');
    $("#confirmpassword").attr("pattern", '(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])\\S{6,}');
    $('#newpassword,#confirmpassword').keyup(function () {
        pattern = new RegExp($(this).attr('pattern'));
        value1 = $('#newpassword').val();
        value2 = $('#confirmpassword').val();
        if (value1.match(pattern) && value2.match(pattern)) {
            $('#remarks').hide();
            $(this).parent().removeClass('has-warning');
            
        }
        else {
            $('#remarks').show();
            $(this).parent().addClass('has-warning');
        }
    });
    $('#Logout').attr('formnovalidate','formnovalidate'); 
});

document.onkeydown = function(event) {
    if (event.getModifierState("CapsLock")) 
        $('#capslock').show();
    else   
        $('#capslock').hide();    
}
</script>