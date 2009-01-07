<?php defined("SYSPATH") or die("No direct script access.") ?>
<iframe width="100%"
        height="100%"
        style="border: 0px"
        src="http://<?= $api_key ?>.web.akismet.com/1.0/user-stats.php?blog=<?= urlencode($blog_url) ?>">
</iframe>
