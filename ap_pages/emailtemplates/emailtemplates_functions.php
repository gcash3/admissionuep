<?php
function formatname($withdiv, $deptcode, $campuscode, $itemid, $payclass, $s, $canadd=true, $candelete=true) {
    $deletelink = '';
    $addlink = '';
    if ($campuscode=='')
        $campuscode = 'A';
    if ($payclass=='')
        $payclass = 'A';
    if ($canadd) {
        $employeecode = '00000';
        $cs = Crypto::ClearanceChecksum($deptcode, $campuscode, $itemid, $payclass, $employeecode);
        $data = "$deptcode;$campuscode;$itemid;$payclass;$employeecode;$cs";
        $addid = "add_$deptcode$campuscode$itemid$payclass";
        $addlink = "<span id='$addid' class='link' data-params='$data' data-command='add' onclick='link(\"$addid\")'>".HTML::icon('plus','','','Add new signatory/verifier') . '</span>';
    }
    if ($s != false) {
        $list = explode(';', $s);
        for ($i=0; $i<count($list); $i++) {
            $matches = array();
            if (preg_match('/([\D .]+) \((\d+)\)/', $list[$i], $matches)) {
                $employeecode = $matches[2];
                $cs = Crypto::ClearanceChecksum($deptcode, $campuscode, $itemid, $payclass, $employeecode);
                $data = "$deptcode;$campuscode;$itemid;$payclass;$employeecode;$cs";
                if ($candelete) {
                    $deleteid = "delete_$deptcode$campuscode$itemid$payclass$employeecode";
                    $deletelink = " <span id='$deleteid' class='link' data-params='$data' data-command='delete' onclick='link(\"$deleteid\")'>".HTML::icon('times','','',"Delete $employeecode") . '</span>';
                }
                $list[$i] = preg_replace('/([\D .]+) (\(\d+\))/', '<span title="$2">$1</span>' . $deletelink, $list[$i]);
                if ($i==count($list)-1)
                    $list[$i] .= ' ' . $addlink;
            }
        }
    }
    else {
        $list[] = $addlink;
    }
    $cellid = "div_$deptcode$campuscode$itemid$payclass";  
    $html = '';
    
    if ($withdiv)
        $html = "<div class='sig' id='$cellid'>";
    $html .= implode('<br>', $list);
    if ($withdiv)
        $html .= "</div>" ;
    return $html;
}

function getcolors() {
    $colors[] = 'bg-red';    
    $colors[] = 'bg-blue';
    $colors[] = 'bg-orange';
    $colors[] = 'bg-maroon';
    $colors[] = 'bg-navy';
    $colors[] = 'bg-purple';
    $colors[] = 'bg-green';
    return $colors;
}

?>