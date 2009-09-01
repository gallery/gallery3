<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  update_blocks = function() {
    $.get(<?= html::js_string(url::site("admin/dashboard/reorder")) ?>,
          {"csrf": "<?= $csrf ?>",
           "dashboard_center[]": $("#gAdminDashboard").sortable(
             "toArray", {attribute: "block_id"}),
           "dashboard_sidebar[]": $("#gAdminDashboardSidebar").sortable(
             "toArray", {attribute: "block_id"})});
  };

  $(document).ready(function(){
    $("#gAdminDashboard .gBlock .ui-widget-header").addClass("gDraggable");
    $("#gAdminDashboard").sortable({
      connectWith: ["#gAdminDashboardSidebar"],
      cursor: "move",
      handle: $(".ui-widget-header"),
      opacity: 0.6,
      placeholder: "gDropTarget",
      stop: update_blocks
    });

    $("#gAdminDashboardSidebar .gBlock .ui-widget-header").addClass("gDraggable");
    $("#gAdminDashboardSidebar").sortable({
      connectWith: ["#gAdminDashboard"],
      cursor: "move",
      handle: $(".ui-widget-header"),
      opacity: 0.6,
      placeholder: "gDropTarget",
      stop: update_blocks
    });
  });
</script>
<div id="gAdminDashboard">
  <?= $blocks ?>
</div>
