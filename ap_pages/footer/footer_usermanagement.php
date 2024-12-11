<!-- usermanagement footer -->
<script>
$(document).ready(function () {
    $('#grantall').click(function(){
        $('input:checkbox').prop('checked', true);    
    });
    $('#denyall').click(function(){
        $('input:checkbox').prop('checked', false);    
    });
    $('#grantallreadonly').click(function(){
        $('input:checkbox').prop('checked', false);    
        $('.accessitem__R').prop('checked', true);    
    });
    $('#saveaccess').click(function(){
        Confirm('Save', 'Do you want to update access rights?', function(result) {
            if (result == true) {
                $('#command').val('saveaccess');
                $('#accessrights').submit();
            }
        });
    });
    $('#deleteaccess').click(function(){
        Confirm('Delete', 'Do you want to delete all access rights?', function(result) {
            if (result == true) {
                $('#command').val('deleteaccess');
                $('#accessrights').submit();
            }
        }, 'danger');
    });
    
});
</script>
