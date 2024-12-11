<?php
$searchtext = trim(@$_GET['q']);
if (isset($_GET['_p1']))
    $searchtext = trim(@$_GET['_p1']);

$totalmodules = 0;
$modulesfound = '';
findsidebarmenus($searchtext, $APP_MODULES, null, $modulesfound, $totalmodules);
if ($totalmodules == 0)
    $modulesfound = '<b>No modules found matching your search text<b>';

echo HTML::box('Modules matching [<b>' . htmlentities($searchtext) .  '</b>]', $modulesfound, "$totalmodules items found.");



function findsidebarmenus($search, $modules, $parent, &$list, &$totalmodules) {
    foreach ($modules as $key => $module) {
        if (isset($module[4]) && is_array($module[4])) {
            findsidebarmenus($search, $module[4], $parent, $list, $totalmodules);
        }
        else {
            findmenu($search, $key, $module, $list, $totalmodules);
        }
    }
}

function findmenu($search, $key, $module, &$list, &$totalmodules) {
    if (!$module[0])
        return;
    if ((stripos($key, $search) !== false) ||  (stripos($module[2], $search) !== false) ) {
        $list .=  "<li><a href='" . APP_BASE . "$key'>$module[2]</a></li>";
        $totalmodules++;
    }
}

?>

