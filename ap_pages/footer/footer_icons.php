<script>
$(document).ready(function () {
    $('.icon').click(function () {
        var font = $(this).attr('title')
        $('#boxicon').html('<i class="'+ font + '"></i>');
        $('#footer1').html(font);
        $('#footer2').html('&lt;i class="'+ font + '"&gt;&lt;/i&gt;');
        $('#footer3').html('HTML::icon("' + $(this).data('icon') +"', '', '" + $(this).data('collection') + "');</span>");
        copytoclipboard('footer', 'Font code [' + font + '] copied to clipboard.');    
    });

    $('.footercode').click(function () {
        copytoclipboard($(this).attr('id'),'Code [' + $(this).text() + '] copied to clipboard');
    });
});

function copytoclipboard(id, message) {
    var range = document.createRange();
    range.selectNode(document.getElementById(id));
    window.getSelection().removeAllRanges(); 
    window.getSelection().addRange(range); 
    document.execCommand("copy");
    window.getSelection().removeAllRanges();
    alert(message);
}
</script>

<style>
    .footercode {
        cursor: pointer;
    }
</style>