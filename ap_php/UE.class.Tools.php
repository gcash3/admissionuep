<?php
/*----------------------------------------------------------
  Class       : Tools
  Author      : CSN-10/19/2017
  Description : General purpose functions
  Usage       : Tools::functionname(params...)
 ----------------------------------------------------------*/
class Tools {

    // transfrom clean URL to parameters
    static function transformURL() {
        global $APP_CURRENTPAGE, $APP_AJAXOUTPUT;
        $parameters = explode('/', $APP_CURRENTPAGE);
        for ($i=1; $i<count($parameters); $i++) {
            $_GET["_p$i"] = $parameters[$i];
            if ($parameters[$i] == 'plain')
                $APP_AJAXOUTPUT = true;
        }
        $APP_CURRENTPAGE = $parameters[0];
        $_GET['_p0'] = $APP_CURRENTPAGE;        
    }
    
    static function pictureurl($id='') {
        global $APP_SESSION;
        if ($id=='current')
            $id = $APP_SESSION->getEmployeeCode();
        if ($id) {
            $cs=Crypto::imagechecksum(trim($id));
            $id = base64_encode($id+0);
            return APP_BASE . "selfie/$id/$cs";
        }
        else {
            return APP_BASE . "ap_img/blank.jpg";
        }
    }
    
    static function picturelink($id, $class='', $title='') {
        if ($id)
            $link = Tools::pictureurl($id);
        else
            $link = APP_BASE . 'ap_img/blank.jpg';
        if ($title=='')
            $title = $id;
        return "<img src='$link' class='$class' title='$title' id='PIC$id'>";
    }

    static function emptyvalues() {
        $arg_list = func_get_args();
        $numargs = func_num_args();
        for ($i = 0; $i < $numargs; $i++) {
            if (trim($arg_list[$i]) == '')
                return true;
        }
        return false;
    }

    static function emptydataset($data) {
        return !(is_array($data) && count($data));
    }
    
    static function redirect($link) {
        ob_clean();
        header('Location: ' . APP_BASE . $link);
    }
    
    static function timeago($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
    
    static function addmodule(&$app_modules, $pagename, $sidebarvisible, $accessrequired, $caption, $icon='', $submenuitems=null, $tooltip='', $requiredaccess='', $pagetitle='', $target="_self") {
        global $APP_MODULES;
        if ($pagetitle == '')
            $pagetitle = $caption;
        $app_modules[$pagename] = array($sidebarvisible, $accessrequired, $caption, $icon, $submenuitems, $tooltip, $requiredaccess, $pagetitle, $target);
    }    
    
    static function showdebuginfo($message) {
        global $APP_DEBUGCONTENT;
        $APP_DEBUGCONTENT .= '<hr>' . $message;
    }
    
    static function explodepagerights($requiredaccess='', $removetag=false) {
        $defaults['C'] = "Can <b>C</b>reate";
        $defaults['R'] = "<b>R</b>ead";
        $defaults['U'] = "<b>U</b>pdate";
        $defaults['D'] = "<b>D</b>elete";
        $defaults['P'] = "<b>Print</b>";
        $defaults['T'] = "Pos<b>t</b>";
        if ($requiredaccess == '') {
            $requiredaccess = 'C;R;U;D;P';
        }
        elseif ($requiredaccess[0] == '+') {
            $requiredaccess = 'C;R;U;D;P;' . substr($requiredaccess,1);
        }
        $accesslist = explode(';', $requiredaccess);         
        $rights = array();
        foreach ($accesslist as $pair) {
            $crudes = explode(':', $pair);
            $rightscode        = trim($crudes[0]);
            $rightsdescription = trim(@$crudes[1]);
            if ($rightsdescription == '')
                $rightsdescription = @$defaults[$rightscode] . '';
            $rightsdescription = trim($rightsdescription ? $rightsdescription : $crudes[0]);        
            if ($removetag) {
                $rightsdescription = preg_replace('/\<\/?.+\>(.+)\<\/b\>/i','$1',$rightsdescription);
            }
            $rights[$rightscode] = $rightsdescription;
        }
        return $rights;
    }    
    
    static function numbertowords($number, $forcheck=false) {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );
        if (!is_numeric($number)) {
            return false;
        }
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            return false;
        }
        if ($number < 0) {
            return $negative . Tools::numbertowords(abs($number), $forcheck);
        }
        $string = $fraction = null;
        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }
        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . Tools::numbertowords($remainder, $forcheck);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = Tools::numbertowords($numBaseUnits, $forcheck) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= Tools::numbertowords($remainder, $forcheck);
                }
                break;
        }
        
        
        if (null !== $fraction && is_numeric($fraction)) {
            if ($forcheck) {
                $fraction = $fraction / 10;
                $string .= " and $fraction";
            }
            else {
                $string .= $decimal;
                $words = array();
                foreach (str_split((string) $fraction) as $number) {
                    $words[] = $dictionary[$number];
                }
                $string .= implode(' ', $words);
            }
        }
        return $string;
    }
    
    static function devonly($s) {
        return APP_PRODUCTION ? '' : $s;
    }
    
    static function getMimeType($ext, $getext=false){
        if ($getext)
            $ext = substr($ext, stripos($ext,'.'));
            
        $ext = strtolower($ext);
        if (!(strpos($ext, '.') !== false)) {
            $ext = '.' . $ext ;
        }
        switch ($ext) {
            case '.aac': $mime ='audio/aac'; break; // AAC audio
            case '.abw': $mime ='application/x-abiword'; break; // AbiWord document
            case '.arc': $mime ='application/octet-stream'; break; // Archive document (multiple files embedded)
            case '.avi': $mime ='video/x-msvideo'; break; // AVI: Audio Video Interleave
            case '.azw': $mime ='application/vnd.amazon.ebook'; break; // Amazon Kindle eBook format
            case '.bin': $mime ='application/octet-stream'; break; // Any kind of binary data
            case '.bmp': $mime ='image/bmp'; break; // Windows OS/2 Bitmap Graphics
            case '.bz': $mime ='application/x-bzip'; break; // BZip archive
            case '.bz2': $mime ='application/x-bzip2'; break; // BZip2 archive
            case '.csh': $mime ='application/x-csh'; break; // C-Shell script
            case '.css': $mime ='text/css'; break; // Cascading Style Sheets (CSS)
            case '.csv': $mime ='text/csv'; break; // Comma-separated values (CSV)
            case '.doc': $mime ='application/msword'; break; // Microsoft Word
            case '.docx': $mime ='application/vnd.openxmlformats-officedocument.wordprocessingml.document'; break; // Microsoft Word (OpenXML)
            case '.eot': $mime ='application/vnd.ms-fontobject'; break; // MS Embedded OpenType fonts
            case '.epub': $mime ='application/epub+zip'; break; // Electronic publication (EPUB)
            case '.gif': $mime ='image/gif'; break; // Graphics Interchange Format (GIF)
            case '.htm': $mime ='text/html'; break; // HyperText Markup Language (HTML)
            case '.html': $mime ='text/html'; break; // HyperText Markup Language (HTML)
            case '.ico': $mime ='image/x-icon'; break; // Icon format
            case '.ics': $mime ='text/calendar'; break; // iCalendar format
            case '.jar': $mime ='application/java-archive'; break; // Java Archive (JAR)
            case '.jpeg': $mime ='image/jpeg'; break; // JPEG images
            case '.jpg': $mime ='image/jpeg'; break; // JPEG images
            case '.js': $mime ='application/javascript'; break; // JavaScript (IANA Specification) (RFC 4329 Section 8.2)
            case '.json': $mime ='application/json'; break; // JSON format
            case '.mid': $mime ='audio/midi audio/x-midi'; break; // Musical Instrument Digital Interface (MIDI)
            case '.midi': $mime ='audio/midi audio/x-midi'; break; // Musical Instrument Digital Interface (MIDI)
            case '.mpeg': $mime ='video/mpeg'; break; // MPEG Video
            case '.mpkg': $mime ='application/vnd.apple.installer+xml'; break; // Apple Installer Package
            case '.odp': $mime ='application/vnd.oasis.opendocument.presentation'; break; // OpenDocument presentation document
            case '.ods': $mime ='application/vnd.oasis.opendocument.spreadsheet'; break; // OpenDocument spreadsheet document
            case '.odt': $mime ='application/vnd.oasis.opendocument.text'; break; // OpenDocument text document
            case '.oga': $mime ='audio/ogg'; break; // OGG audio
            case '.ogv': $mime ='video/ogg'; break; // OGG video
            case '.ogx': $mime ='application/ogg'; break; // OGG
            case '.otf': $mime ='font/otf'; break; // OpenType font
            case '.png': $mime ='image/png'; break; // Portable Network Graphics
            case '.pdf': $mime ='application/pdf'; break; // Adobe Portable Document Format (PDF)
            case '.ppt': $mime ='application/vnd.ms-powerpoint'; break; // Microsoft PowerPoint
            case '.pptx': $mime ='application/vnd.openxmlformats-officedocument.presentationml.presentation'; break; // Microsoft PowerPoint (OpenXML)
            case '.rar': $mime ='application/x-rar-compressed'; break; // RAR archive
            case '.rtf': $mime ='application/rtf'; break; // Rich Text Format (RTF)
            case '.sh': $mime ='application/x-sh'; break; // Bourne shell script
            case '.svg': $mime ='image/svg+xml'; break; // Scalable Vector Graphics (SVG)
            case '.swf': $mime ='application/x-shockwave-flash'; break; // Small web format (SWF) or Adobe Flash document
            case '.tar': $mime ='application/x-tar'; break; // Tape Archive (TAR)
            case '.tif': $mime ='image/tiff'; break; // Tagged Image File Format (TIFF)
            case '.tiff': $mime ='image/tiff'; break; // Tagged Image File Format (TIFF)
            case '.ts': $mime ='application/typescript'; break; // Typescript file
            case '.ttf': $mime ='font/ttf'; break; // TrueType Font
            case '.txt': $mime ='text/plain'; break; // Text, (generally ASCII or ISO 8859-n)
            case '.vsd': $mime ='application/vnd.visio'; break; // Microsoft Visio
            case '.wav': $mime ='audio/wav'; break; // Waveform Audio Format
            case '.weba': $mime ='audio/webm'; break; // WEBM audio
            case '.webm': $mime ='video/webm'; break; // WEBM video
            case '.webp': $mime ='image/webp'; break; // WEBP image
            case '.woff': $mime ='font/woff'; break; // Web Open Font Format (WOFF)
            case '.woff2': $mime ='font/woff2'; break; // Web Open Font Format (WOFF)
            case '.xhtml': $mime ='application/xhtml+xml'; break; // XHTML
            case '.xls': $mime ='application/vnd.ms-excel'; break; // Microsoft Excel
            case '.xlsx': $mime ='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; break; // Microsoft Excel (OpenXML)
            case '.xml': $mime ='application/xml'; break; // XML
            case '.xul': $mime ='application/vnd.mozilla.xul+xml'; break; // XUL
            case '.zip': $mime ='application/zip'; break; // ZIP archive
            case '.3gp': $mime ='video/3gpp'; break; // 3GPP audio/video container
            case '.3g2': $mime ='video/3gpp2'; break; // 3GPP2 audio/video container
            case '.7z': $mime ='application/x-7z-compressed'; break; // 7-zip archive
            default: $mime = 'application/octet-stream' ; // general purpose MIME-type
        }
        return $mime ;
    }
    
    static function base64_encodeNOEQ($s) {
        return str_replace('=','',base64_encode($s));
    }
    
}
?>
