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

.setschool {
    cursor: pointer;
}

.fileothers {
    margin-bottom: 5px;
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
    //echo HTML::jsbuttonaddon('Email','Compose gmail','fa fa-envelope');
    echo HTML::jsbuttonaddon('Email','Edit Email','fa fa-pencil');
    
    if ($APP_SESSION->getCanUpdate()) {
        echo HTML::jsbuttonaddon('Semester','Edit Semester','fa fa-' . (isset($_POST['SaveSemester']) ? 'save' : 'pencil'));
        if (isset($_POST['SaveSemester'])) {
            echo "\$('#Semester').prop('disabled',false).data('semester','xxxxx');";
        }
        echo HTML::jsbuttonaddon('LastSchoolID','Edit School','fa fa-pencil');
        
        echo HTML::jsbuttonaddon('NewCourse','Edit Campus/Course','fa fa-pencil');
        echo HTML::jsbuttonaddon('Class2','Edit Class','fa fa-pencil');
    }
    ?>
    
    $('#addon_LastSchoolID').click(function() {
        $('#SearchSchoolResults').html('');
        $('#SearchSchoolsModal').modal();
    });
    $('#SearchSchoolButton').click(function() {
        var school = $('#SearchSchoolText').val().trim();
        $('#SearchSchoolText').focus();
        if (school == '') {
            return;
        }
        $('#SearchSchoolResults').load('<?php echo APP_BASE ?>searchschools/plain', 
            {
                st:school,
                an:<?php echo $applicationnumber+0 ?>,
                rn:<?php echo $webreferencenumber+0 ?>,
                cs:'<?php echo Crypto::GenericChecksum("$applicationnumber;uwi;na;$webreferencenumber") ?>'
            });
        
    });
    
    $('.SearchSchoolUE').click(function() {
        $('#SearchSchoolText').val($(this).data('search'));
        $('#SearchSchoolButton').click();
    });

    $('#SearchSchoolsModal').on('shown.bs.modal', function () {
        $('#SearchSchoolText').focus()
    })
    
    $('#searchtext').addClass('noautofocus').daterangepicker({autoclose:true,autoUpdateInput:false}).prop('autocomplete','off');      

    $('#searchtext').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('#searchtext').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });    

    // $('#addon_Email').click(function(){
    //     var href = $('#mailto').prop('href');
    //     var target = $('#mailto').prop('target');   
    //     window.open(href, target);
    // });


    $('#addon_Semester').click(function(){
        var semester = $('#Semester').val();
        if ($(this).find('.fa-pencil').length == 1) {
            $('#Semester').data('semester',semester);
            if (confirm('Do you want to edit semester?')) {
                $('#Semester').prop('disabled',false).focus();
                $(this).find('.fa').removeClass('fa-pencil').addClass('fa-save');
            }
        }
        else {
            if (semester == '') {
                $('#Semester').focus();
            }
            else {
                if (semester != $('#Semester').data('semester')) {
                    if (confirm('Do you want to update semester to [' + semester + ']?')) {
                        var oldsemester = $("#Semester").data('semester');
                        $("#Semester").append("<input type=hidden name='SaveSemester' value='" + oldsemester + "'>");
                        $('#Semester').closest('form').submit();
                    }
                }
                else {
                    $('#Semester').focus();
                }
            }
        }

    });


    $('#addon_Email').click(function(){
        var email = $('#Email').val();
        if ($(this).find('.fa-pencil').length == 1) {
            $('#Email').data('email',email);
            if (confirm('Do you want to edit email address?')) {
                $('#Email').prop('disabled',false).prop('readonly',false).focus();
                $(this).find('.fa').removeClass('fa-pencil').addClass('fa-save');
            }
        }
        else {
            if (email == '') {
                $('#Email').focus();
            }
            else {
                if (email != $('#Email').data('email')) {
                    if (confirm('Do you want to update email address to [' + email + ']?')) {
                        $("#Semester").append("<input type=hidden name='SaveEmail' value='" + email + "'>");
                        $('#Semester').closest('form').submit();
                    }
                }
                else {
                    $('#Email').focus();
                }
            }
        }

    });  
    
    $('#addon_NewCourse').click(function(){
        var ccc = $('#NewCourse').val();
        if ($(this).find('.fa-pencil').length == 1) {
            $('#NewCourse').data('ccc',ccc);
            var label = $('#NewCourse').parents('.form-group').find('.prompt').html();
            if (confirm(label)) {
                $('#NewCourse').prop('disabled',false).prop('readonly',false).focus();
                $(this).find('.fa').removeClass('fa-pencil').addClass('fa-save');
            }
        }
        else {
            if (ccc == '') {
                $('#NewCourse').focus();
            }
            else {
                if (ccc != $('#Email').data('ccc')) {
                    if (confirm('Do you want to set campus and program to [' + ccc + ']?')) {
                        $("#NewCourse").append("<input type=hidden name='SaveCCC' value='" + ccc + "'>");
                        $('#NewCourse').closest('form').submit();
                    }
                }
                else {
                    $('#NewCourse').focus();
                }
            }
        }

    });       


    $('#addon_Class2').click(function(){
        var class2 = $('#Class2').val();
        if ($(this).find('.fa-pencil').length == 1) {
            $('#Class2').data('class2',class2);
            if (confirm('Do you want to edit class??')) {
                $('#Class2').prop('disabled',false).prop('readonly',false).focus();
                $(this).find('.fa').removeClass('fa-pencil').addClass('fa-save');
            }
        }
        else {
            if (class2 == '') {
                $('#Class2').focus();
            }
            else {
                if (class2 != $('#Class2').data('class2')) {
                    if (confirm('Do you want to update class to [' + class2 + ']?')) {
                        $("#Class2").append("<input type=hidden name='SaveClass2' value='" + class2 + "'>");
                        $('#Class2').closest('form').submit();
                    }
                }
                else {
                    $('#Class2').focus();
                }
            }
        }

    });  
    
    $('.verify,#SaveApplicationFeeDatePaid').click(function() {
        $('#txtRemarks').prop('required',false);
    });



});

function setschool(i, a, c, n, r) {
    if ((i == 0) && (n == '')) {
        $('#SchoolName').focus();
        return;
    }
    var s  = '';
    var p  = n;
    if ((i == 406313) || (i ==483599) ) {
        s = prompt('Enter UE student number:');
        if ((s == null) || (s == ''))
            return;
        p = p + ' (SN: ' + s + ')';
    }
    if (confirm('Do you want to update school ID to ' + i + '?' + '\n' + p))
        $('#SearchSchoolResults').load('<?php echo APP_BASE ?>updateschool/plain', {i:i,a:a,c:c,r:r,n:n,s:s});   
}
</script>