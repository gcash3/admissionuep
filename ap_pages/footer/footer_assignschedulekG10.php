<link rel="stylesheet" href="<?php echo APP_BASE ?>bower_components/bootstrap-daterangepicker/daterangepicker.css">
<script src="<?php echo APP_BASE ?>bower_components/moment/min/moment.min.js"></script>
<script src="<?php echo APP_BASE ?>bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>

<style>
.fpPortrait {
    width: 78px;
    height: 100px;
    background-image: url("<?php echo APP_BASE ?>ap_img/photo.jpg?v2");
    background-color: #ffffff; 
    background-position: center; 
    background-repeat: no-repeat;
    cursor: zoom-in;
}

.fpLandscape {
    width: 129px;
    height: 100px;
    background-image: url("<?php echo APP_BASE ?>ap_img/photo.jpg?v2");
    background-color: #ffffff; 
    background-position: center; 
    background-repeat: no-repeat; 
    cursor: zoom-in; 
}

.fpSquare {
    width: 100px;
    height: 100px;
    background-image: url("<?php echo APP_BASE ?>ap_img/photo.jpg?v2");
    background-color: #ffffff; 
    background-position: center; 
    background-repeat: no-repeat;
    cursor: zoom-in;
}

.fileicon {
    margin-top: 5px;
    margin-left: 2px;
    cursor: zoom-in;
}
.imageDescription
{
    width: 100px;
/*    border: 1px solid black;  */
    font-weight: bold;
    word-wrap: break-word;
}
</style>


<script type="text/javascript">

function js_zoom(id) {
    var buttons = ''
                + '<style>'
                + '@media print {'
                + 'button {display:none}'
                + '}'
                + 'div {position:fixed;top:5px;left:5px;width:100%;z-index:9999}' 
                + 'img {border:2px solid #000000;top:2px;left:2px;overflow:visible;margin:0px;}'
                + 'button {height:25px;}'   
                + '.zoom-in {cursor:zoom-in}'
                + '.zoom-out {cursor:zoom-out}'
                + 'body {margin:0}'
                + '</style>'
                + '<div><button type="button" onclick="window.print()">Print</button> ' 
                + '<button type="button" onclick="zoom(1.1)" class="zoom-in">Zoom-In</button> '
                + '<button type="button" onclick="zoom(0.9)" class="zoom-out">Zoom-Out</button> '
                + '<button type="button" onclick="fittopage()">Fit to Page</button> '
                + '<button type="button" onclick="rotate()">Rotate</button> '  
                + '<button type="button" onclick="window.close()">Close</button> '  
                + '</div>';
    var script  = '' 
                + '<script>'    
                + '    var deg=0;'
                + '    function zoom(f) {'
                + '        var csn = document.getElementById("csn");' 
                + '        var currWidth = csn.clientWidth * f;'
                + '        var currHeight = csn.clientHeight * f;'
                + '        csn.style.height = Math.round(currHeight) + "px";'
                + '        csn.style.width = Math.round(currHeight) + "px";'  
                + '    }' 
                + '    function fittopage() {'
                + '        var csn = document.getElementById("csn");' 
                + '        var currHeight = csn.clientHeight;'
                + '        var currWidth = csn.clientWidth;' 
                + '        while (currHeight < window.innerHeight) {currHeight*=1.1; currWidth*=1.1;}'  
                + '        currHeight=Math.ceil(currHeight/10)*10; currWidth=Math.ceil(currHeight/10)*10;'
                + '        while (currHeight > window.innerHeight) {currHeight*=0.9; currWidth*=0.9;}'  
                + '        csn.style.height = Math.ceil(currHeight/10)*10 + "px";'
                + '        csn.style.width = Math.ceil(currHeight/10)*10 + "px";'   
                + '    }'  
                + '    function rotate() {'
                + '        fittopage();'
                + '        deg +=90; if (deg>270) deg=0;'
                + '        var csn = document.getElementById("csn");' 
                + '        var currHeight = csn.clientHeight;'
                + '        var currWidth = csn.clientWidth;' 
                + '        csn.style.height = Math.round(currWidth) + "px";'
                + '        csn.style.width = Math.round(currHeight) + "px";'   
                + '        csn.style.transform = "rotate("+deg+"deg)";'
                + '    }'                                
                + '</' + 'script' + '>';    

                
    
    var image = new Image();
    image.src = $('#' + id).attr('src');
    image.id = 'csn';
    var w = window.open("",'ImagePreviewV02');
    w.document.write('<title>Image Preview</title><body>' 
                     + buttons 
                     + image.outerHTML 
                     + script
                     + '</body>');
    w.document.close(); 
}


$(document).ready(function () { 
    <?php
    echo HTML::jsbuttonaddon('Email','Compose gmail','fa fa-pencil');
    ?>
    
    $('#searchtext').addClass('noautofocus').daterangepicker({autoclose:true,autoUpdateInput:false}).prop('autocomplete','off');      

    $('#searchtext').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('#searchtext').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });    

    $('#addon_Email').click(function(){
        var href = $('#mailto').prop('href');
        var target = $('#mailto').prop('target');   
        window.open(href, target);
    });
    alert("test");
    if ($('.ApplicantData').length>0){
        alert("test");
        // fBlockSchedule('< ?php echo $_SESSION["BlockCode"]; ?>','< ? php echo Crypto::GenericChecksum($_SESSION[ "BlockCode"] );?>');
    }
    
});

    
function fBlockSchedule(str, cs)
{
    // alert(str);
//    document.getElementById("tbodySchedule").removeChild;

    $(".selector").removeClass('info');
    $('#'+str).addClass('info');

//    document.getElementById(str).style.backgroundColor = "gray";
    $('#tbodySchedule').load(
        "<?php echo APP_BASE ?>fBlockSchedule/plain",{BlockCode:str,cs:cs}
    )

    $('#btnPrintBlock').remove();
}

function getSelectedCourseYear(cs)
{
    var selectedCourseYear = document.getElementById("listCourseYear").value;
    //alert(selectedValue);

    //$('#Distribution').hide();
    $('#Blocktbody').load(
        "<?php echo APP_BASE ?>BlockSection/plain",{selectedCourseYear:selectedCourseYear,cs:cs}
    );
}    
    
  
</script>