  <header class="main-header">
    <!-- Logo -->
    <a href="<?php echo APP_BASE ?>dashboard" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b><?php echo substr(APP_TITLE,0,1);?></b>P</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>UE <?php echo str_ireplace('portal','',APP_TITLE2); ?></b>Portal</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
           <li>
             <a href="<?php echo $APP_SESSION->getDualCampus() ?  APP_BASE . "changecampus/changecampus/0/0/0/changecampus/$APP_CURRENTPAGE" : '#' ?>" class="dropdown-toggle">
             <i class="fa fa-home"></i> <span id='campuscodetop'><?php echo Data::getcampusdescription($APP_SESSION->getCampusCode(),true) ?></span>
             </a>  
          </li>       
          <li>
             <a href="<?php echo APP_BASE ?>changesemester/changesemester/0/0/0/changesemester/<?php echo $APP_CURRENTPAGE ?>" class="dropdown-toggle">
             <i class="fa fa-calendar"></i> <span id='semestertop'><?php echo max(' [Semester not selected]', Data::getsemesterdescription($APP_SESSION->getPageSemester(),true)) ?></span>
             </a>  
          </li> 
         
          <!-- Messages: style can be found in dropdown.less-->

          <!-- Notifications: style can be found in dropdown.less -->
          <?php
          //echo shownotifications();
          ?>

          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo Tools::pictureurl('current') ?>" class="user-image" alt="img">
              <span class="hidden-xs"><?php echo $APP_SESSION->getEmployeeFirstname() ?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo Tools::pictureurl('current') ?>" class="img-circle" alt="User Image">

                <p>
                  <?php echo $APP_SESSION->getEmployeeName(), ' - ', $APP_SESSION->getDepartmentReference() ?>
                  <small><?php echo $APP_SESSION->getEmployeeCode(); ?></small>
                </p>
              </li>
              <!-- Menu Body -->
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center">
                    <a href="<?php echo APP_BASE ?>onlineusers" title='View Online Users'>Users</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="<?php echo APP_BASE ?>#">Reserved</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="<?php echo APP_BASE ?>logs">Logs</a>
                  </div>
                </div>
                <!-- /.row -->
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="<?php echo APP_BASE ?>profile" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="<?php echo APP_BASE ?>" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  
  <?php
  function shownotifications() {
    $totalnofications = 0;
    $incomingdocuments = @count(Data::getincomingdocuments()) + 0;
    $totalnofications += $incomingdocuments; 
    $html  = '<li class="dropdown notifications-menu">';
    $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
    $html .= '<i class="fa fa-bell-o"></i>';
    if ($totalnofications)
        $html .= '<span class="label label-warning">0</span>';
    $html .= '</a>';
    $html .= '<ul class="dropdown-menu">';
    $html .= '<li class="header">You have ' . $totalnofications . ' notifications</li>';
    $html .= '<li>';
    $html .= '<ul class="menu">';
    if ($incomingdocuments)
        $html .= '<li><a href="'.APP_BASE . 'doctracker/incoming"><i class="fa fa-map-marker text-red"></i> ' . $incomingdocuments . ' incoming documents</a></li>';
    if ($totalnofications == 0)
        $html .= '<li><a href="#"><i class="fa fa-smile-o text-yellow"></i> No new notifications for today</a></li>';
    $html .= '</ul>';
    $html .= '</li>';
    $html .= '<li class="footer"><a href="'. APP_BASE . 'notifications">View all</a></li>';
    $html .= '</ul>';
    $html .= '</li>';
    return $html;
  
  }
  ?>