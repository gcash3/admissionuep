<style>
.bodyxxx {
    width: 100%; 
    height: 200px; 
    font-size: 14px; 
    line-height: 18px; 
    border: 1px solid #dddddd; 
    padding: 10px;    
}
</style>
<script src="<?php echo APP_BASE; ?>bower_components/ckeditor/ckeditor.js"></script>
<script>
  $(function() {
    CKEDITOR.replace('MessageBody');
  });
</script>


<style>
.preview {
    padding: 10px;
    border: 1px solid #bbbbbb;
    font-size: 110%;
    background-color: #FFFFFA;
}
</style>