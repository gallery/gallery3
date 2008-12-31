<?php defined("SYSPATH") or die("No direct script access.") ?>
<script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
<script type="text/javascript">
  show = function(id, form_url) {
    $.ajax({
      url: form_url,
      success: function(data) {
        $("div.form").slideUp();
        var el = $("div#edit-" + id);
        el.html(data).slideDown();
      }
    });
  }
</script>
<div id="gPermissions">
  <ul>
    <? foreach ($parents as $parent): ?>
    <li>
      <a href="javascript:show(<?= $parent->id ?>,'<?= url::site("permissions/form/$parent->id") ?>')">
        <?= $parent->title ?>
      </a>
      <div class="form" id="edit-<?= $parent->id ?>"></div>
      <ul>
        <? endforeach ?>
        <li>
          <a href="javascript:show(<?= $item->id ?>,'<?= url::site("permissions/form/$item->id") ?>')">
            <?= $item->title ?>
          </a>
          <div class="form" id="edit-<?= $item->id ?>">
            <?= $form ?>
          </div>
        </li>
        <? foreach ($parents as $parent): ?>
      </ul>
    </li>
  </ul>
  <? endforeach ?>
</div>
