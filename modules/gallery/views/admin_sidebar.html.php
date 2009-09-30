<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(function() {
    $(".gAdminBlocksList ul").sortable({
      connectWith: ".sortableBlocks",
      opacity: .7,
      placeholder: "ui-state-highlight",
      update: function(event,ui) {
        if ($(this).attr("id") == "gActiveBlocks") {
          var active_blocks = "";
          $("ul#gActiveBlocks li").each(function(i) {
            active_blocks += "&block["+i+"]="+$(this).attr("ref");
          });
          $.getJSON($("#gSiteBlocks").attr("ref").replace("__ACTIVE__", active_blocks), function(data) {
            if (data.result == "success") {
              $("ul#gAvailableBlocks").html(data.available);
              $("ul#gActiveBlocks").html(data.active);
            }
          });
        }
      },
    }).disableSelection();
  });
</script>
<h1> <?= t("Manage Sidebar") ?> </h1>
<p>
  <?= t("Select and drag blocks from the available column to the active column to add to the sidebar; remove by dragging the other way.") ?>
</p>
    <div id="gSiteBlocks" ref="<?= url::site("admin/sidebar/update?csrf={$csrf}__ACTIVE__") ?>">
  <div class="gAdminBlocksList">
    <div><h3><?= t("Available Blocks") ?></h3></div>
    <div>
      <ul id="gAvailableBlocks" class="sortableBlocks">
      <?= $available ?>
      </ul>
    </div>
  </div>
  <div class="gAdminBlocksList">
    <div><h3><?= t("Active Blocks") ?></h3></div>
    <div>
      <ul id="gActiveBlocks" class="sortableBlocks">
      <?= $active ?>
      </ul>
    </div>
  </div>
</div>
