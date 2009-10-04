<?php defined("SYSPATH") or die("No direct script access.") ?>
<span id="g-welcome-messageLink"
      title="<?= t("Welcome to Gallery 3")->for_html_attr() ?>"
      href="<?= url::site("welcome_message") ?>"/>
<script type="text/javascript">
  $(document).ready(function(){$("#g-welcome-messageLink").gallery_dialog({immediate: true});});
</script>
