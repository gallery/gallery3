<? defined("SYSPATH") or die("No direct script access."); ?>
<script type="text/javascript">
  $(document).ready( function() {
    $('#gRearrangeTree').RearrangeTree({
      script: "<?= url::base(true) . "rearrange/show" ?>"
    }, function(file) {});
  });
</script>
<div id="gRearrange">
  <span id="gAddAlbum" rel="gAddAlbum">New Album</span>
  <div id="gAddAlbumPopup">
    <a id="gAddAlbumPopupClose">x</a>
    <div id="gAddAlbumArea"></div>
  </div>
  &nbsp;
  <span id="gDeleteItem">Delete</span>
  <hr/>
  <div id="gRearrangeTree"></div>
</div>
