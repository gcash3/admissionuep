    <section class="content">
      <div class="error-page">
        <h2 class="headline text-yellow"> 404</h2>

        <div class="error-content">
          <h3><i class="fa fa-warning text-yellow"></i> Oops! Page not found.</h3>

          <p>
            We could not find the page you were looking for.
            Meanwhile, you may <a href="<?php echo APP_BASE ?>dashboard">return to dashboard</a> or try using the search form.
          </p>

          <form class="search-form">
            <div class="input-group">
              <input type="text" id="q404" class="form-control" placeholder="Search">
              <div class="input-group-btn">
                <button type="button" class="btn btn-warning btn-flat"><i class="fa fa-search" onclick="window.location='<?php echo APP_BASE ?>search/'+$('#q404').val()"></i></button>
              </div>
            </div>
          </form>
        </div>
      </div>
