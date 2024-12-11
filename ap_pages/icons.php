<?php
$buttons['fa']        = array('<small>font-awesome</small>','bower_components/font-awesome/css/font-awesome.min.css','fa-glass','success','font', HTML::badge('98','bg-red'));
$buttons['ion']       = array('ion-icon','bower_components/ionicons/css/ionicons.min.css','','info','italic', HTML::badge('1466','bg-orange'));
$buttons['glyphicon'] = array('glyphicon','bower_components/bootstrap/less/glyphicons.less','','warning','asterisk',HTML::badge('265','bg-blue'));
$font = @$_GET['_p1'];
if ($font == '')
    $font = 'fa';

foreach ($buttons as $name => $items) {
    $active = $name == $font ? 'active' : '';
    $class = $items[3];
    echo HTML::linkbutton("$APP_CURRENTPAGE/$name",HTML::icon($items[4],$items[0]).$items[5],"$class btn-app $active", $name);
}

$grid = getfontgrid($font,$buttons[$font][1],$buttons[$font][2]);
$title = $buttons[$font][0];
$footer  = '<a name="footer" /a><span id="footer1" title="Click to copy code to clipboard" class="footercode badge bg-green"></span> ';
$footer .= '<span id="footer2" title="Click to copy code to clipboard" class="footercode badge bg-blue"></span> ';
$footer .= '<span id="footer3" title="Click to copy code to clipboard" class="footercode badge bg-red"></span>'; 
echo HTML::box("<span id=boxicon><i class='fa fa-font'></i></span> $title",$grid,$footer,'success');


function getfontgrid($class, $cssfile, $start) {
    $csscontent = file_get_contents($cssfile);
    preg_match_all('/(\\.'.$class.'\\-[a-z-]+[a-z])/', $csscontent, $matches);
    $c = 0;
    $fontsperline = 8;
    $lines = array();
    $line  = array();
    $ok    = false;
    foreach ($matches[0] as $font) {
        $font = substr($font,1);
        if (($font==$start) || ($start == ''))
            $ok = true;
        if ($ok) {
            $icon = substr($font,stripos($font,'-')+1);
            $line[] = "<a href='#footer' class='btn btn-default btn-app icon' title='$class $font' data-collection='$class' data-icon='$icon'><i class='$class $font'></i>".substr($font,0,10)."<span class='hidden'>$font</span></a>";
            $c++;
            if ($c == $fontsperline) {
                $lines[] = $line;
                $line = array();
                $c = 0;
            }
        }
    }
    if ($line)
        $lines[] = $line;
    for ($c=0; $c<$fontsperline; $c++) 
        $columns[$c] = array("Column ".($c+1),'columnclass'=>'text-right');

    return HTML::datatable($class,$columns, $lines);
}
?>