<script>
$(document).ready(function () { 
    $('.sql').click(function() { 
        var sql = $(this).data('sql');
        if (sql !== '')
            alert('SQL : ' + sql);
    });
});
</script>

<style>
.sql {
    cursor: pointer;
}
</style>