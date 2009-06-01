<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var form_url = "<?= url::site("permissions/form/__ITEM__") ?>";
  show = function(id) {
    $.ajax({
      url: form_url.replace("__ITEM__", id),
      success: function(data) {
        $("div.form").slideUp();
        $("div#edit-" + id).html(data).slideDown();
      }
    });
  }

  var action_url =
    "<?= url::site("permissions/change/__CMD__/__GROUP__/__PERM__/__ITEM__?csrf=$csrf") ?>";
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
  <? if (!$htaccess_works): ?>
  <ul id="gMessage">
    <li class="gError">
      <?= t("Oh no!  Your server needs a configuration change in order for you to hide photos!  Ask your server administrator to set <a %attrs><i>AllowOverride FileInfo Options</i></a> to fix this.", array("attrs" => "href=\"http://httpd.apache.org/docs/2.0/mod/core.html#allowoverride\" target=\"_blank\"")) ?>
    </li>
  </ul>
  <? endif ?>
  <ul>
    <? foreach ($parents as $parent): ?>
    <li>
      <a href="javascript:show(<?= $parent->id ?>)">
        <?= p::clean($parent->title) ?>
      </a>
      <div class="form" id="edit-<?= $parent->id ?>"></div>
      <ul>
        <? endforeach ?>
        <li>
          <a href="javascript:show(<?= $item->id ?>)">
            <?= p::clean($item->title) ?>
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
