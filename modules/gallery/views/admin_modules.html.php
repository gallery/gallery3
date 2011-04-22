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
    <?= t("Power up your Gallery by adding more modules! Each module provides new cool features.") ?>
  </p>

  <div class="g-block-content">
    <form id="g-module-update-form" method="post" action="<?= url::site("admin/modules/confirm") ?>">
      <?= access::csrf_form_field() ?>
      <table>
        <tr>
          <th> <?= t("Installed") ?> </th>
          <th style="width: 8em"> <?= t("Name") ?> </th>
          <th> <?= t("Version") ?> </th>
          <th> <?= t("Description") ?> </th>
        </tr>
        <? foreach ($available as $module_name => $module_info):  ?>
        <tr class="<?= text::alternate("g-odd", "g-even") ?>">
          <? $data = array("name" => $module_name); ?>
          <? if ($module_info->locked) $data["disabled"] = 1; ?>
          <td> <?= form::checkbox($data, '1', module::is_active($module_name)) ?> </td>
          <td> <?= t($module_info->name) ?> </td>
          <td> <?= $module_info->version ?> </td>
          <td> <?= t($module_info->description) ?> </td>
        </tr>
        <? endforeach ?>
      </table>
      <input type="submit" value="<?= t("Update")->for_html_attr() ?>" />
    </form>
  </div>
</div>
