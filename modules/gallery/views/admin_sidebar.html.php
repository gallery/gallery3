<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(function() {
    $(".g-admin-blocks-list ul").sortable({
      connectWith: ".g-sortable-blocks",
      opacity: .7,
      placeholder: "g-target",
      update: function(event,ui) {
        if ($(this).attr("id") == "g-active-blocks") {
          var active_blocks = "";
          $("ul#g-active-blocks li").each(function(i) {
            active_blocks += "&block["+i+"]="+$(this).attr("ref");
          });
          $.getJSON($("#g-site-blocks").attr("ref").replace("__ACTIVE__", active_blocks), function(data) {
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
<h1> <?= t("Manage Sidebar") ?> </h1>
<p>
  <?= t("Select and drag blocks from the available column to the active column to add to the sidebar; remove by dragging the other way.") ?>
</p>
    <div id="g-site-blocks" ref="<?= url::site("admin/sidebar/update?csrf={$csrf}__ACTIVE__") ?>">
  <div class="g-admin-blocks-list">
    <div><h3><?= t("Available Blocks") ?></h3></div>
    <div>
      <ul id="g-available-blocks" class="g-sortable-blocks">
      <?= $available ?>
      </ul>
    </div>
  </div>
  <div class="g-admin-blocks-list">
    <div><h3><?= t("Active Blocks") ?></h3></div>
    <div>
      <ul id="g-active-blocks" class="g-sortable-blocks">
      <?= $active ?>
      </ul>
    </div>
  </div>
</div>
