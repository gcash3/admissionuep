
  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?php echo Tools::pictureurl('current') ?>" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php echo $APP_SESSION->getEmployeeName() ?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> <?php echo $APP_SESSION->getSessionMinutes() ?> Online</a>
        </div>
      </div>
      <!-- search form -->
      <form class="sidebar-form">
        <div class="input-group">
          <input type="text" id="q" class="form-control" placeholder="Search...">
          <span class="input-group-btn">
                <button type="button" class="btn btn-flat" id='sidebarsearch'><i class="fa fa-search"></i></button>
              </span>
        </div>
      </form>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
          <?php
          $parentmenu = getparentmenu($APP_CURRENTPAGE, '', $APP_MODULES);
          echo HTML::sidebarmenus($APP_CURRENTPAGE, $APP_MODULES, $parentmenu);
          ?>          
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>