  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer no-print">
    <div class="pull-right hidden-xs">
      <b>Version</b> <?php echo APP_VERSION, (!APP_PRODUCTION? ' '. APP_DB_DATABASE : '') ?>
    </div>
    <strong>Copyright &copy; <?php echo date('Y') ?> University of the East</strong> All rights reserved.
  </footer>


</div>
<!-- ./wrapper -->

<script src="<?php echo APP_BASE ?>bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo APP_BASE ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?php echo APP_BASE ?>plugins/DataTables/datatables.min.js"></script>
<script src="<?php echo APP_BASE ?>bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo APP_BASE ?>bower_components/fastclick/lib/fastclick.js"></script>
<script src="<?php echo APP_BASE ?>bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo APP_BASE ?>bower_components/select2/dist/js/select2.full.min.js"></script>
<script src="<?php echo APP_BASE ?>dist/js/adminlte.min.js"></script>
<script src="<?php echo APP_BASE ?>dist/js/bootbox.min.js"></script>
<script src="<?php echo APP_BASE ?>ap_js/adminportal.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo APP_BASE ?>ap_css/adminportal.css?<?php echo mt_rand()?>">  
<script>
  $(document).ready(function () {
    $('.sidebar-menu').tree()
    $('.datepicker').datepicker({
      autoclose: true
    })
    $('#sidebarsearch').click(function() {
      window.location="<?php echo APP_BASE ?>search/"+$('#q').val();
    });
  })
</script>
<?php
if (file_exists("ap_pages/footer/footer_$APP_CURRENTPAGE.php"))
    include_once("ap_pages/footer/footer_$APP_CURRENTPAGE.php");
?>
<script>
  $(document).ready(function () {
	$(".dataTable").each(function(){
        var pageLength = $(this).data('pagelength');
        var tabletitle = $(this).data('title');
        if (tabletitle == undefined)
            tabletitle = $(this).attr('title');
        if (tabletitle == undefined)
            tabletitle = 'ExportedTable';
        if (pageLength == undefined)
            pageLength = 10;
        var paging = true;
        if ($(this).data('nopaging') !== undefined)
            paging = false;
        $(this).DataTable(
          {
          "search": {"search":""},                 
          stateSave: true,
          responsive:true,
          "pageLength":pageLength,
          "paging":paging,
          "oLanguage": {"sSearch": "Filter:"}, 
          "columnDefs": [ {"targets": 'no-sort',"orderable": false,} ],
          dom: "<'row'<'col-sm-4'l><'col-sm-8'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-4'i><'col-sm-8'p>>" + "<'row'<'col-sm-12'B>>",
          buttons: [
                   { extend: 'copy', text: '<i class="fa fa-copy"></i>', "className": 'btn btn-default btn-sm', titleAttr: 'Copy to Clipboard'},
                   { extend: 'csv', text: '<i class="fa fa-file-text-o"></i>', "className": 'btn btn-default btn-sm', titleAttr: 'Save as CSV', title: tabletitle },
                   { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i>', "className": 'btn btn-default btn-sm', titleAttr: 'Save as Excel', title: tabletitle  },
                   { extend: 'pdf', text: '<i class="fa fa-file-pdf-o"></i>', "className": 'btn btn-default btn-sm', titleAttr: 'Save as PDF', title: tabletitle  },
                   { extend: 'print', text: '<i class="fa fa-print"></i>', "className": 'btn btn-default btn-sm', titleAttr: 'Print Table', title: tabletitle  },
                   
                   ],
          }).search('').draw();
    });
    $('input:enabled:visible:not("#q"):not("[readonly]"):not(".datepicker,.noautofocus"):first').focus();

    $('.box-body').find('.form-group').find('label').parents('.box').find('.box-tools').prepend("<button type='button' class='btn btn-box-tool inputsizer' data-toggle='tooltip' title='Change Texbox Height'><i class='fa fa-font'></i></button>");
    $('.inputsizer').click(function() {
        $(this).parents('.box').find('label').next('.form-control').toggleClass('input-sm');
    });
    $('.box').find('.table').parents('.box').find('.box-tools').prepend("<button type='button' class='btn btn-box-tool tablesizer' data-toggle='tooltip' title='Change Table Height'><i class='fa fa-text-height'></i></button>");
    $('.tablesizer').click(function() {
        $(this).parents('.box').find('.table').toggleClass('table-condensed');
    });
    $('.appbuttons').find('a.btn-app').parents('.buttons').append('<i class="fa fa-caret-up appbuttonstoggle no-print" style="padding-left:5px;cursor:pointer;vertical-align:top" title="Toggle Buttons"></i>');
    $('.appbuttons').find('a.btn').css('margin-bottom','5px');
    $('.appbuttonstoggle').click(function() {
        $(this).parents('.appbuttons').slideUp(500, function() {
            $(this).find('.fa-caret-up,.fa-caret-down').toggleClass('fa-caret-up').toggleClass('fa-caret-down');                
            $(this).find('.appbutton').toggleClass('btn-app');
            $(this).slideDown(200);
        });
    });    

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    
    <?php
    echo @$footerscript;
    ?>
        
    $('[type=submit]').on('click', function() {
      var confirmation = $(this).data('confirmation');
      if (confirmation === undefined)
          var confirmation = '[' + $(this).text().trim() + '] : Do you want to proceed?';
      else if ((confirmation == 'none') || (confirmation == 'false') || (confirmation == '0'))
          return true;
      if (confirmation !== undefined) {
          if(confirm(confirmation.trim())) {
              return true;
          }
          return false;
      }
      return true;
    });         

    $('input:not(:file),select').change(function () {
        if ($(this).hasClass('no-warning') == false)
            $(this).parent().addClass('has-warning');
    });
    $('button[type=reset], input[type=reset]').click(function() {
        $('div').removeClass('has-warning');
        $(this).parents('form:first').find('input:not([readonly]):not([disabled]):first').focus();
    });
       
    $('.recordselector,.recordselectortoggle').click(function () {
          $('.forrecordselector').prop('disabled', $('.recordselector:checked').length==0);
    });

    $('.printpage').click(function() {
      window.print();
    });        
	
	$('.alert-dismissible').delay(10000).fadeOut(400);
  });
  
  
</script>

<link  href="<?php echo APP_BASE?>plugins/bootstrap-multiselect-dropdown/dist/css/bootstrap-multiselect.css" rel="stylesheet">
<script src='<?php echo APP_BASE?>plugins/bootstrap-multiselect-dropdown/dist/js/bootstrap-multiselect.js'></script>
<script>
    jQuery(document).ready(function () {
        $('select.columnfilter').attr('multiple','multiple');
        $('.columnfilter option').filter(function() {
            return !this.value || $.trim(this.value).length == 0;
        }).remove();
        $('.columnfilter').multiselect({
            includeResetOption: true,
            includeResetDivider: true,
            includeSelectAllOption: true,
            buttonClass: 'btn btn-default btn-flat',
            allSelectedText: 'All Columns'
        }).each(function() {
          $('#' + $(this).prop('id')+ '_div').removeClass('hidden');
        });

        $(window).on('beforeunload', function() {
          if ($('.loader').data('disabled') === undefined) {
            window.scrollTo(0, 0);
            $('.loader').removeClass('hidden');
            $('input,button,a,select,textarea').prop('disabled',true);
          }
        }); 
    });
</script>
<style>
  .searchtext_div {
    margin-bottom: 5px;
  }
</style>
</body>
</html>
