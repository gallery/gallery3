<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javscript">
  $(document).ready(function() {
    $("#g-akismet-external-stats").css("height", "600");
  });
</script>
<div id="g-akismet-stats">
  <iframe id="g-akismet-external-stats" width="100%" height="500" frameborder="0"
          src="//akismet.com/1.0/user-stats.php?api_key=<?= $api_key ?>&blog=<?= urlencode($blog_url) ?>">
  </iframe>
</div>
