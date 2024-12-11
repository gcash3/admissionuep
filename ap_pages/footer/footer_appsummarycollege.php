<link rel="stylesheet" href="<?php echo APP_BASE ?>bower_components/bootstrap-daterangepicker/daterangepicker.css">
<script src="<?php echo APP_BASE ?>bower_components/moment/min/moment.min.js"></script>
<script src="<?php echo APP_BASE ?>bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<script>
$(document).ready(function () { 
    $('#searchtext').addClass('noautofocus').daterangepicker({autoclose:true});
});
</script>