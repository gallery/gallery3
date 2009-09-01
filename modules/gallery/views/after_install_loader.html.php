<?php defined("SYSPATH") or die("No direct script access.") ?>
<span id="gAfterInstall"
      title="<?= t("Welcome to Gallery 3")->for_html_attr() ?>"
      href="<?= url::site("after_install") ?>"/>
<script type="text/javascript">
  $(document).ready(function(){$("#gAfterInstall").gallery_dialog({immediate: true});});
</script>
