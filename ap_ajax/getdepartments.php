<?php
echo HTML::selectoptions('', Data::getdepartmentsall(trim(@$_POST['cc'])));
?>