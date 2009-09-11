<?php defined("SYSPATH") or die("No direct script access.") ?>
<span id="gWelcomeMessageLink"
      title="<?= t("Welcome to Gallery 3")->for_html_attr() ?>"
      href="<?= url::site("welcome_message") ?>"/>
<script type="text/javascript">
  $(document).ready(function(){$("#gWelcomeMessageLink").gallery_dialog({immediate: true});});
</script>
