<?php defined("SYSPATH") or die("No direct script access.") ?>
<div class="g-block ui-helper-clearfix">
  <script type="text/javascript">
  $("#g-module-update-form").ready(function() {
    $("#g-module-update-form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.reload) {
          window.location = "<?php url::site("/admin/modules") ?>";
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
            title: <?php echo t("Confirm module activation")->for_js() ?>,
            buttons: {
              <?php echo t("Continue")->for_js() ?>: function() {
                $("form", this).submit();
                $(".ui-dialog-buttonpane button:contains(" + <?php echo t("Continue")->for_js() ?> + ")")
                  .attr("disabled", "disabled")
                  .addClass("ui-state-disabled");
              },
              <?php echo t("Cancel")->for_js() ?>: function() {
                $(this).dialog("destroy").remove();
              }
            }
          });
          if (!data.allow_continue) {
            $(".ui-dialog-buttonpane button:contains(" + <?php echo t("Continue")->for_js() ?> + ")")
              .attr("disabled", "disabled")
              .addClass("ui-state-disabled");
          }
        }
      }
    });
  });
  </script>
  <h1> <?php echo t("Gallery Modules") ?> </h1>
  <p>
    <?php echo t("Power up your Gallery by <a href=\"%url\">adding more modules</a>! Each module provides new cool features.", array("url" => "http://codex.galleryproject.org/Category:Gallery_3:Modules")) ?>
  </p>

  <?php if ($obsolete_modules_message): ?>
  <p class="g-warning">
    <?php echo $obsolete_modules_message ?>
  </p>
  <?php endif ?>

  <div class="g-block-content">
    <form id="g-module-update-form" method="post" action="<?php echo url::site("admin/modules/confirm") ?>">
      <?php echo access::csrf_form_field() ?>
      <table>
        <tr>
          <th> <?php echo t("Installed") ?> </th>
          <th style="width: 8em"> <?php echo t("Name") ?> </th>
          <th> <?php echo t("Version") ?> </th>
          <th> <?php echo t("Description") ?> </th>
          <th style="width: 60px"> <?php echo t("Details") ?> </th>
        </tr>
        <?php foreach ($available as $module_name => $module_info):  ?>
        <tr class="<?php echo text::alternate("g-odd", "g-even") ?>">
          <?php $data = array("name" => $module_name); ?>
          <?php if ($module_info->locked) $data["disabled"] = 1; ?>
          <td> <?php echo form::checkbox($data, '1', module::is_active($module_name)) ?> </td>
          <td> <?php echo t($module_info->name) ?> </td>
          <td> <?php echo $module_info->version ?> </td>
          <td> <?php echo t($module_info->description) ?> </td>
          <td style="white-space: nowrap">
            <ul class="g-buttonset">
              <li>
                <a target="_blank"
                   <?php if (isset($module_info->author_url)): ?>
                   class="ui-state-default ui-icon ui-icon-person ui-corner-left"
                   href="<?php echo $module_info->author_url ?>"
                   <?php else: ?>
                   class="ui-state-disabled ui-icon ui-icon-person ui-corner-left"
                   href="#"
                   <?php endif ?>

                   <?php if (isset($module_info->author_name)): ?>
                   title="<?php echo $module_info->author_name ?>"
                   <?php endif ?>
                   >
                   <?php if (isset($module_info->author_name)): ?>
                   <?php echo $module_info->author_name ?>
                   <?php endif ?>
                </a>
              </li>
              <li>
                <a target="_blank"
                   <?php if (isset($module_info->info_url)): ?>
                   class="ui-state-default ui-icon ui-icon-info"
                   href="<?php echo $module_info->info_url ?>"
                   <?php else: ?>
                   class="ui-state-disabled ui-icon ui-icon-info"
                   href="#"
                   <?php endif ?>
                   >
                  <?php echo t("info") ?>
                </a>
              </li>
              <li>
                <a target="_blank"
                   <?php if (isset($module_info->discuss_url)): ?>
                   class="ui-state-default ui-icon ui-icon-comment ui-corner-right"
                   href="<?php echo $module_info->discuss_url ?>"
                   <?php else: ?>
                   class="ui-state-disabled ui-icon ui-icon-comment ui-corner-right"
                   href="#"
                   <?php endif ?>
                   >
                  <?php echo t("discuss") ?>
                </a>
              </li>
            </ul>
          </td>
        </tr>
        <?php endforeach ?>
      </table>
      <input type="submit" value="<?php echo t("Update")->for_html_attr() ?>" />
    </form>
  </div>
</div>
