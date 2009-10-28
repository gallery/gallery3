<?php defined("SYSPATH") or die("No direct script access.") ?>
<a id="g-exif-data-link" href="<?= url::site("exif/show/{$item->id}") ?>" title="<?= t("Photo details")->for_html_attr() ?>"
  class="g-dialog-link g-button ui-icon-left ui-state-default ui-corner-all">
  <span class="ui-icon ui-icon-info"></span>
  <?= t("View more information") ?>
</a>
