<?php defined("SYSPATH") or die("No direct script access."); ?>
<script>
$(document).ready( function() {
  $('#gRearrangeTree').RearrangeTree({}, function(file) {
    alert(file);
  });
});
</script>
<div id="gRearrange">
  <span id="gAddAlbum">New Album</span>
  &nbsp;
  <span id="gDeleteItem">Delete</span>
  <hr/>
  <div id="gRearrangeTree" />
</div
