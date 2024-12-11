<link  href="<?php echo APP_BASE?>plugins/bs-iconpicker/css/bootstrap-iconpicker.min.css" rel="stylesheet">
<script src='<?php echo APP_BASE?>plugins/bs-iconpicker/js/iconset/iconset-fontawesome-4.7.0.min.js'></script>
<script src='<?php echo APP_BASE?>plugins/bs-iconpicker/js/bootstrap-iconpicker.js'></script>
<script src='<?php echo APP_BASE?>plugins/jquery-menu-editor.csn.min.js'></script>
<script>
    jQuery(document).ready(function () {
        //icon picker options
        var iconPickerOptions = {searchText: 'Icon...', labelHeader: '{0}/{1}'};
        //sortable list options
        var sortableListOptions = {
            placeholderCss: {'background-color': 'cyan'}
        };

        var editor = new MenuEditor('myEditor', {listOptions: sortableListOptions, iconPicker: iconPickerOptions, labelEdit: 'Edit'});
        editor.setForm($('#frmEdit'));
        editor.setUpdateButton($('#btnUpdate'));
        
        $('#btnReload').on('click', function () {
            editor.setData(strjson);
        });

        $('#btnSave').on('click', function () {
            var str = editor.getString();
            $("#modules").val(str);
            $('#action').val('Save');
            if (confirm('Do you want to save modules?'))
                $('#form').submit();
        });

        $('#btnSaveApply').on('click', function () {
            var str = editor.getString();
            $("#modules").val(str);
            $('#action').val('SaveApply');
            if (confirm('Do you want to save and apply modules?'))
                $('#form').submit();
        });        

        $("#btnUpdate").click(function(){
            $('#timestamp').val(datestring());
            editor.update();
            $('#moduletitle').text('[Draft]');
            $('.has-warning').removeClass('has-warning');
        });

        $('#btnAdd').click(function(){
            $('#timestamp').val(datestring());
            editor.add();
            $('#moduletitle').text('[Draft]');
            $('.has-warning').removeClass('has-warning');
        });
        $('#btnReset').on('click', function () {
            if (confirm('Do you want to load current draft?'))
            editor.setData(<?php echo $modulesdraft; ?>);
            $('#moduletitle').text('[Draft]');
        });
        $('#btnCurrent').on('click', function () {
            if (confirm('Do you want to load current modules?'))
                editor.setData(<?php echo $modulescurrent; ?>);
                $('#moduletitle').text('[Current]');
        });        
        $('#text').on('blur',function() {
            if ($('#href').val() == '')
                $('#href').val($(this).val().replace(' ','').toLowerCase() );
            
        });
        $('#text').focus(function() {
            togglecrudfields();
            
        });
        $('#accessrequired').change(function() {
            togglecrudfields();
        });
        editor.setData(<?php echo $modulesdraft; ?>);
    });

    function togglecrudfields() {
        var ar = $('#accessrequired').val() == 1;
        if (ar)
            $('.crud').removeClass('hidden');
        else    
            $('.crud').addClass('hidden');
    }

    function datestring() {
        var d = new Date();
        return d.getFullYear() + "-" + 
               (d.getMonth() < 10 ? '0' : '')   + (d.getMonth() + 1) + "-" + 
               (d.getDate() < 10 ? '0' : '')    + d.getDate() + " " + 
               (d.getHours() < 10 ? '0' : '')   + d.getHours() + ":" + 
               (d.getMinutes() < 10 ? '0' : '') + d.getMinutes() + ":" + 
               (d.getSeconds() < 10 ? '0' : '') + d.getSeconds(); 


    }
</script>