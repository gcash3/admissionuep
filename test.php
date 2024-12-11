<!DOCTYPE html>
<html>
<head>
<title>Untitled</title>
<link rel="stylesheet" type="text/css" href="/employeeportal__dev/bower_components/select2/dist/css/select2.min.css">  
<script src="/employeeportal__dev/bower_components/jquery/dist/jquery.min.js"></script>
<script src="/employeeportal__dev/bower_components/select2/dist/js/select2.full.min.js"></script>
</head>
<body>
<div class="container">
<form>
<select class="js-data-example-ajax" class="form-control" style="width:100%" multiple></select>
</form>
</div>
<script>
$(document).ready(function () {
    $('.js-data-example-ajax').select2({
      ajax: {
        url: 'getmedicines',
        type: "post",
        dataType: 'json',
        data: function(params) {
            var query = {
                q: params.term,
                ajax: 1
            }
            return query;
        },
        processResults : function (data) {
            return {
                results : data
            }
        }        
      }
    });    
});  
</script>
</body>
</html>
