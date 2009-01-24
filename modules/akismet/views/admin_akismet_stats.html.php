<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javscript">
  $(document).ready(function() {
    $("#gAkismetExternalStats").css("height", "600");
  });
</script>
<div id="gAkismetStats">
  <iframe id="gAkismetExternalStats" width="100%" height="500" frameborder="0"
          src="http://<?= $api_key ?>.web.akismet.com/1.0/user-stats.php?blog=<?= urlencode($blog_url) ?>">
  </iframe>
</div>
