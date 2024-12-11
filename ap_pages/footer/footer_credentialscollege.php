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
    background-image: url("<?php echo APP_BASE ?>gap_img/photo.jpg?v2");
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

.badge {
    z-index: 9999 !important;
}

.TDC > button {
    margin-top: 25px;
}

.classfilter {
    cursor: pointer;
}

.appbutton {
    margin-right: 10px !important;
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
                + '        csn.style.width = Math.round(currWidth) + "px";'  
                + '    }' 
                + '    function fittopage() {'
                + '        var csn = document.getElementById("csn");' 
                + '        var currHeight = csn.clientHeight;'
                + '        var currWidth = csn.clientWidth;' 
                + '        while (currHeight < window.innerHeight) {currHeight*=1.1; currWidth*=1.1;}'  
                + '        currHeight=Math.ceil(currHeight/10)*10; currWidth=Math.ceil(currWidth/10)*10;'
                + '        while (currHeight > window.innerHeight) {currHeight*=0.9; currWidth*=0.9;}'  
                + '        csn.style.height = Math.ceil(currHeight/10)*10 + "px";'
                + '        csn.style.width = Math.ceil(currWidth/10)*10 + "px";'   
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

                
    if ($('#' + id).prop('src').match(/blank/))
        return;
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
    echo HTML::jsbuttonaddon('Email','Compose gmail','fa fa-envelope');
    if ($APP_SESSION->getCanUpdate())
        echo HTML::jsbuttonaddon('LastSchoolID','Edit School','fa fa-pencil');
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
    
    $('#viewpef').click(function(){
        alert('Not yet available!');
        return false;
    });
    
    $('#pasteuerecord').click(function() {
        if (true) {
            var ln = $('#CurrentLastName').val();
            var fn = $('#CurrentFirstName').val();
            var mn = $('#CurrentMiddleName').val();
            var bd = $('#CurrentBirthDate').val();
            if (ln != '')
                $('#LastName').val(ln).parent().addClass('has-warning');
            if (fn != '')
                $('#FirstName').val(fn).parent().addClass('has-warning');  
            if (mn != '')
                $('#MiddleName').val(mn).parent().addClass('has-warning');   
            if (bd != '')
                $('#BirthDate').val(bd).parent().addClass('has-warning'); 
        }
    });
    
    $('#LastName,#FirstName,#MiddleName,#BirthDate').each(function() {
        var name = $(this).prop('name');
        if ($('#Current'+name).length > 0) {
            if ($('#Current'+name).val() != $(this).val()) {
                $(this).parent().addClass('has-warning');  
                var label = $('label[for="' + name + '"]');
                if (label)
                    label.html(label.html() + '<sup class="text-danger">Mismatched</sup>');
            }
            else {
                $(this).prop('readonly',true);
            }
        }
    });

    $('#addon_Email').click(function(){
        var href = $('#mailto').prop('href');
        var target = $('#mailto').prop('target');   
        window.open(href, target);
    });


    // TDC Reference
    $('.TDC').parents('.row').find('input').each(function() {
        $(this).data('default', $(this).val()).prop('type','number').prop('min',0);
    });

    $('#TRANSCREDITEDUNITS,#TRANSGWAGPA').prop('step','0.01');

    $('#EditTDC').click(function() {
        $(this).addClass('hidden');
        $('.tdcsave').removeClass('hidden');
        $(this).parents('.row').find('input').prop('readonly',false).first().focus();
    });

    $('#CancelTDC').click(function() {
        $('#EditTDC').removeClass('hidden');
        $('.tdcsave').addClass('hidden');
        $(this).parents('.row').find('input').each(function() {
            $(this).val($(this).data('default')).prop('readonly',true);;
        });
    });

    $('#DeleteTDC').click(function() {
        if (confirm('Do you want to set values to zero?')) {
            $(this).parents('.row').find('input').each(function() {
                $(this).val(0);
            });
            $(this).parents('.row').find('input').first().focus();
        }
    });

    $('.classfilter').click(function() {
        $('#searchtext').val($(this).data('filter')).focus();
    });
    

});

function setschool(i, a, c, n, r) {
    if ((i == 0) && (n == '')) {
        $('#SchoolName').focus();
        return;
    }
    if (confirm('Do you want to update school ID to ' + i + '?' + '\n' + n))
        $('#SearchSchoolResults').load('<?php echo APP_BASE ?>updateschool/plain', {i:i,a:a,c:c,r:r,n:n});   
}
</script>