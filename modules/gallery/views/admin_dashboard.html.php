<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  update_blocks = function() {
    $.get(<?= html::js_string(url::site("admin/dashboard/reorder")) ?>,
          {"csrf": "<?= $csrf ?>",
           "dashboard_center[]": $("#g-admin-dashboard").sortable(
             "toArray", {attribute: "block_id"}),
           "dashboard_sidebar[]": $("#g-admin-dashboard-sidebar").sortable(
             "toArray", {attribute: "block_id"})});
  };

  $(document).ready(function(){
    $("#g-admin-dashboard .g-block .ui-widget-header").addClass("g-draggable");
    $("#g-admin-dashboard").sortable({
      connectWith: ["#g-admin-dashboard-sidebar"],
      cursor: "move",
      handle: $(".ui-widget-header"),
      opacity: 0.6,
      placeholder: "g-target",
      stop: update_blocks
    });

    $("#g-admin-dashboard-sidebar .g-block .ui-widget-header").addClass("g-draggable");
    $("#g-admin-dashboard-sidebar").sortable({
      connectWith: ["#g-admin-dashboard"],
      cursor: "move",
      handle: $(".ui-widget-header"),
      opacity: 0.6,
      placeholder: "g-target",
      stop: update_blocks
    });
  });
</script>
<div>
  <? if ($obsolete_modules_message): ?>
  <p class="g-warning">
    <?= $obsolete_modules_message ?>
  </p>
  <? endif ?>
</div>
<div id="g-admin-dashboard">
  <?= $blocks ?>
</div>
