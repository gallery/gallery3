<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block ui-helper-clearfix">
  <script type="text/javascript">
  $("#g-module-update-form").ready(function() {
    $("#g-module-update-form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.reload) {
          window.location = "<? url::site("/admin/modules") ?>";
        } else {
          $("body").append('<div id="g-dialog">' + data.dialog + '</div>');
          $("#g-dialog").dialog({
            bgiframe: true,
            autoOpen: true,
            autoResize: true,
            modal: true,
            resizable: false,
            height: 400,
            width: 500,
            position: "center",
            title: <?= t("Confirm module activation")->for_js() ?>,
            buttons: {
              <?= t("Continue")->for_js() ?>: function() {
                $("form", this).submit();
                $(".ui-dialog-buttonpane button:contains(" + <?= t("Continue")->for_js() ?> + ")")
                  .attr("disabled", "disabled")
                  .addClass("ui-state-disabled");
              },
              <?= t("Cancel")->for_js() ?>: function() {
                $(this).dialog("destroy").remove();
              }
            }
          });
          if (!data.allow_continue) {
            $(".ui-dialog-buttonpane button:contains(" + <?= t("Continue")->for_js() ?> + ")")
              .attr("disabled", "disabled")
              .addClass("ui-state-disabled");
          }
        }
      }
    });
  });
  </script>
  <h1> <?= t("Gallery Modules") ?> </h1>
  <p>
    <?= t("Power up your Gallery by <a href=\"%url\">adding more modules</a>! Each module provides new cool features.", array("url" => "http://codex.galleryproject.org/Category:Gallery_3:Modules")) ?>
  </p>

  <? if ($obsolete_modules_message): ?>
  <p class="g-warning">
    <?= $obsolete_modules_message ?>
  </p>
  <? endif ?>

  <div class="g-block-content">
    <form id="g-module-update-form" method="post" action="<?= url::site("admin/modules/confirm") ?>">
      <?= access::csrf_form_field() ?>
      <table>
        <tr>
          <th> <?= t("Installed") ?> </th>
          <th style="width: 8em"> <?= t("Name") ?> </th>
          <th> <?= t("Version") ?> </th>
          <th> <?= t("Description") ?> </th>
          <th style="width: 60px"> <?= t("Details") ?> </th>
        </tr>
        <? foreach ($available as $module_name => $module_info):  ?>
        <tr class="<?= text::alternate("g-odd", "g-even") ?>">
          <? $data = array("name" => $module_name); ?>
          <? if ($module_info->locked) $data["disabled"] = 1; ?>
          <td> <?= form::checkbox($data, '1', module::is_active($module_name)) ?> </td>
          <td> <?= t($module_info->name) ?> </td>
          <td> <?= $module_info->version ?> </td>
          <td> <?= t($module_info->description) ?> </td>
          <td style="white-space: nowrap">
            <ul class="g-buttonset">
              <li>
                <a target="_blank"
                   <? if (isset($module_info->author_url)): ?>
                   class="ui-state-default ui-icon ui-icon-person ui-corner-left"
                   href="<?= $module_info->author_url ?>"
                   <? else: ?>
                   class="ui-state-disabled ui-icon ui-icon-person ui-corner-left"
                   href="#"
                   <? endif ?>

                   <? if (isset($module_info->author_name)): ?>
                   title="<?= $module_info->author_name ?>"
                   <? endif ?>
                   >
                   <? if (isset($module_info->author_name)): ?>
                   <?= $module_info->author_name ?>
                   <? endif ?>
                </a>
              </li>
              <li>
                <a target="_blank"
                   <? if (isset($module_info->info_url)): ?>
                   class="ui-state-default ui-icon ui-icon-info"
                   href="<?= $module_info->info_url ?>"
                   <? else: ?>
                   class="ui-state-disabled ui-icon ui-icon-info"
                   href="#"
                   <? endif ?>
                   >
                  <?= t("info") ?>
                </a>
              </li>
              <li>
                <a target="_blank"
                   <? if (isset($module_info->discuss_url)): ?>
                   class="ui-state-default ui-icon ui-icon-comment ui-corner-right"
                   href="<?= $module_info->discuss_url ?>"
                   <? else: ?>
                   class="ui-state-disabled ui-icon ui-icon-comment ui-corner-right"
                   href="#"
                   <? endif ?>
                   >
                  <?= t("discuss") ?>
                </a>
              </li>
            </ul>
          </td>
        </tr>
        <? endforeach ?>
      </table>
      <input type="submit" value="<?= t("Update")->for_html_attr() ?>" />
    </form>
  </div>
</div>
