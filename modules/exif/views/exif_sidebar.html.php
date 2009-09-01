<?php defined("SYSPATH") or die("No direct script access.") ?>
<a id="gExifDataLink" href="<?= url::site("exif/show/{$item->id}") ?>" title="<?= t("Photo Details")->for_html_attr() ?>"
  class="gDialogLink gButtonLink ui-icon-left ui-state-default ui-corner-all">
  <span class="ui-icon ui-icon-info"></span>
  <?= t("View more information") ?>
</a>

