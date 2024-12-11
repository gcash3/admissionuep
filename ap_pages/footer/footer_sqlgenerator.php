<script>
  $(document).ready(function () {
        $('#tablename').change(function () {
            $('#form').submit();
        });
        $('.select2').select2();  
        $('.select2').on('change', function (e) {
          $('pre').html('Please click <a href="#" onclick="$(\'#form\').submit()">Generate</a> to reload...');
          $('.execute').hide();
        });        
        $('.cc').click(function () {
          var id = $(this).attr('id');
          if ((id !== undefined) && (document.getElementById(id.substr(1,10)) !== undefined)) {
            var range = document.createRange();
            range.selectNode(document.getElementById(id.substr(1,10)));
            window.getSelection().removeAllRanges(); 
            window.getSelection().addRange(range); 
            document.execCommand("copy");
            window.getSelection().removeAllRanges();
            alert('Code copied to clipboard.');
          }
        });
        $('.ci').click(function () {
          var id = $(this).attr('id');
          if ((id !== undefined) && (document.getElementById(id.substr(1,10)) !== undefined)) {
            id = '#' + id.substr(1,10);
            var html = $(id).html();
            html = ' ' + html.replace(/\n/g,"\n ");
            $(id).html(html);
          }
        });     
        $('.co').click(function () {
          var id = $(this).attr('id');
          if ((id !== undefined) && (document.getElementById(id.substr(1,10)) !== undefined)) {
            id = '#' + id.substr(1,10);
            var html = $(id).html();
            html = html.replace(/\n /g,"\n");
            if (html.substr(0,1) == ' ')
              html = html.substr(1, html.length-1);
            $(id).html(html);
          }
        });           
        $('#Generate').attr('data-confirmation','0');
  })
</script>
