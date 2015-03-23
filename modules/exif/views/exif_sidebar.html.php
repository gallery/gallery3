<?php defined("SYSPATH") or die("No direct script access.") ?>
<a id="g-exif-data-link" href="<?php echo url::site("exif/show/{$item->id}") ?>" title="<?php echo t("Photo details")->for_html_attr() ?>"
  class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all">
  <span class="ui-icon ui-icon-info"></span>
  <?php echo t("View more information") ?>
</a>
