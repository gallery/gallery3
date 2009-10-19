<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function(){
    $(".g-blocks-list").equal_heights();
  });

  $(function() {
    $(".g-blocks-list ul").sortable({
      connectWith: ".g-sortable-blocks",
      opacity: .7,
      placeholder: "g-target",
      update: function(event,ui) {
        if ($(this).attr("id") == "g-active-blocks") {
          var active_blocks = "";
          $("ul#g-active-blocks li").each(function(i) {
            active_blocks += "&block["+i+"]="+$(this).attr("ref");
          });
          $.getJSON($("#g-admin-blocks").attr("ref").replace("__ACTIVE__", active_blocks), function(data) {
            if (data.result == "success") {
              $("ul#g-available-blocks").html(data.available);
              $("ul#g-active-blocks").html(data.active);
            }
          });
        }
      }
    }).disableSelection();
  });
</script>
<div class="g-block">
  <h1> <?= t("Manage Sidebar") ?> </h1>
  <p>
    <?= t("Select and drag blocks from the available column to the active column to add to the sidebar; remove by dragging the other way.") ?>
  </p>

  <div id="g-admin-blocks" class="g-block-content ui-helper-clearfix" ref="<?= url::site("admin/sidebar/update?csrf={$csrf}__ACTIVE__") ?>">
    <div class="g-block g-left">
      <h3><?= t("Available Blocks") ?></h3>
      <div class="g-blocks-list g-block-content">
        <ul id="g-available-blocks" class="g-sortable-blocks">
        <?= $available ?>
        </ul>
      </div>
    </div>
    <div class="g-block g-left">
      <h3><?= t("Active Blocks") ?></h3>
      <div class="g-blocks-list g-block-content">
        <ul id="g-active-blocks" class="g-sortable-blocks">
        <?= $active ?>
        </ul>
      </div>
    </div>
  </div>
</div>
