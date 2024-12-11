<script>
$(document).ready(function () { 
    $('select').change(function() {
        $('form').submit();
    });
    $('.fa-search,.search').click(function() {
        $('#GoogleCSV').click();
    });
    $('#GoogleCSV').change(function() {
        $('#UploadGoogleCSV').prop('disabled',false).focus();
    });
    var selected = {};
    $('select option:selected').each(function() {
        var v = $(this).val();
        if (selected[v] == undefined)
            selected[v] = 0;
        selected[v]++;
    });
    $('select option:selected').each(function() {
        var v = $(this).val();
        if (selected[v] > 1) {
            $(this).parent().parent().addClass('has-warning');
            $('#UploadScores').prop('disabled',true);
        }
    });
    
});
</script>
<style>
.fa-search, #GoogleCSV, .search {
    cursor: pointer;
}
.form-horizontal .control-label {
    text-align: lefts;
}
</style>
