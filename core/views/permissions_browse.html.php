<?php defined("SYSPATH") or die("No direct script access.") ?>
<script src="<?= url::file("lib/jquery.js") ?>" type="text/javascript"></script>
<script type="text/javascript">
  show = function(id, form_url) {
    $.ajax({
      url: form_url,
      success: function(data) {
        $("div.form").slideUp();
        $("div#edit-" + id).html(data).slideDown();
      }
    });
  }

  var action_url = "<?= url::site("permissions/__CMD__/__GROUP__/__PERM__/__ITEM__?csrf=" . access::csrf_token()) ?>";
  var form_url = "<?= url::site("permissions/form/__ITEM__") ?>";
  set = function(cmd, group_id, perm_id, item_id) {
    $.ajax({
      url: action_url.replace("__CMD__", cmd).replace("__GROUP__", group_id).
           replace("__PERM__", perm_id).replace("__ITEM__", item_id),
      success: function(data) {
        $("div#edit-" + item_id).load(form_url.replace("__ITEM__", item_id));
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
