<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  update_blocks = function() {
    $.get("<?= url::site("admin/dashboard/reorder") ?>",
          {"csrf": "<?= access::csrf_token() ?>",
           "dashboard_center[]": $("#gAdminDashboard").sortable(
             "toArray", {attribute: "block_id"}),
           "dashboard_sidebar[]": $("#gAdminDashboardSidebar").sortable(
             "toArray", {attribute: "block_id"})});
  };

  $(document).ready(function(){
    $("#gAdminDashboard .gBlock *:first").addClass("gDraggable");
    $("#gAdminDashboard").sortable({
      connectWith: ["#gAdminDashboardSidebar"],
      containment: "document",
      cursor: "move",
      handle: $("div:first"),
      opacity: 0.6,
      placeholder: "gDropTarget",
      stop: update_blocks
    });

    $("#gAdminDashboardSidebar .gBlock *:first").addClass("gDraggable");
    $("#gAdminDashboardSidebar").sortable({
      connectWith: ["#gAdminDashboard"],
      containment: "document",
      cursor: "move",
      handle: $("div:first"),
      opacity: 0.6,
      placeholder: "gDropTarget",
      stop: update_blocks
    });
  });
</script>
<div id="gAdminDashboard">
  <?= $blocks ?>
</div>
