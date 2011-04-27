<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var set_state_url =
    <?= html::js_string(url::site("admin/manage_comments/set_state/__ID__/__STATE__?csrf=$csrf")) ?>;
  var set_state = function(state, id) {
    $("#g-comment-" + id).fadeOut("fast", function() {
      $.get(set_state_url.replace("__STATE__", state).replace("__ID__", id),
          {},
          update_menu);
      });
  }

  var update_menu = function() {
    $.get(<?= html::js_string(url::site("admin/manage_comments/menu_labels")) ?>, {},
          function(data) {
            for (var i = 0; i < data.length; i++) {
              $("#g-admin-comments ul li:eq(" + i + ") a").html(data[i]);
            }
          },
          "json");
  }

  // Paginator clicks load their href in the active tab panel
  var fix_links = function() {
    $(".g-paginator a, a#g-delete-all-spam").click(function(event) {
      event.stopPropagation();
      $.scrollTo(0, 800, { easing: "swing" });
      $(this).parents(".ui-tabs-panel").load(
        $(this).attr("href"),
        function() {
          fix_links();
        });
      return false;
    });
  }

  $(document).ready(function() {
    $("#g-admin-comments").tabs({
      show: fix_links,
    });
  });
</script>

<div id="g-admin-comments" class="g-block">
  <?= $menu->render() ?>
</div>
