<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var form_url = "<?= url::site("permissions/form/__ITEM__") ?>";
  show = function(id) {
    $.ajax({
      url: form_url.replace("__ITEM__", id),
      success: function(data) {
          $("#gEditPermissionForm").html(data);
          $(".active").removeClass("active");
          $("#item-" + id).addClass("active");
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
        $("#gEditPermissionForm").load(form_url.replace("__ITEM__", item_id));
      }
    });
  }
</script>
<div id="gPermissions">
  <? if (!$htaccess_works): ?>
  <ul id="gMessage">
    <li class="gError">
      <?= t("Oh no!  Your server needs a configuration change in order for you to hide photos!  Ask your server administrator to enable <a %mod_rewrite_attrs>mod_rewrite</a> and set <a %apache_attrs><i>AllowOverride FileInfo Options</i></a> to fix this.",
            array("mod_rewrite_attrs" => html::mark_clean('href="http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html" target="_blank"'),
                  "apache_attrs" => html::mark_clean('href="http://httpd.apache.org/docs/2.0/mod/core.html#allowoverride" target="_blank"'))) ?>
    </li>
  </ul>
  <? endif ?>

  <p><?= t("Edit permissions for album:") ?></p>

  <ul class="gBreadcrumbs">
    <? foreach ($parents as $parent): ?>
    <li id="item-<?= $parent->id ?>">
      <? if (access::can("edit", $parent)): ?>
      <a href="javascript:show(<?= $parent->id ?>)">
        <?= html::purify($parent->title) ?>
      </a>
      <? else: ?>
      <?= html::purify($parent->title) ?>
      <? endif ?>
    </li>
    <? endforeach ?>
    <li class="active" id="item-<?= $item->id ?>">
      <a href="javascript:show(<?= $item->id ?>)">
        <?= html::purify($item->title) ?>
      </a>
    </li>
  </ul>

  <div id="gEditPermissionForm">
    <?= $form ?>
  </div>
</div>
