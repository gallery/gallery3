<? defined("SYSPATH") or die("No direct script access."); ?>
<script type="text/javascript">
$(document).ready(function() {
  ajax_update = function(className, id) {
    return function(value, settings) {
      $.post("<?= url::site("item/__ID__") ?>".replace("__ID__", id),
             {"key": settings.name, "value": value},
             function(data, textStatus) {
               if (textStatus == "success") {
                 $(className).html(data);
               }
             },
             "html");
    }
  }

  var seen_before = {};
  var editable = $("span.gInPlaceEdit");
  for (i = 0; i < editable.length; i++) {
    var matches = editable[i].className.match(/gEditField-(\d+)-(\S+)/);
    if (matches && matches.length == 3) {
      var className = "." + matches[0];
      if (!seen_before[className]) {
        $(className).editable(
          ajax_update(className, matches[1]),
          {indicator : "<?= _("Saving...") ?>",
           tooltip   : "<?= _("Double-click to edit...") ?>",
           event     : "dblclick",
           style     : "inherit",
           name      : matches[2],
           select    : true}
        );
        seen_before[className] = 1;
      }
    }
  }
});
</script>
