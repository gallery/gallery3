<? defined("SYSPATH") or die("No direct script access."); ?>
<script type="text/javascript">
<![CDATA[
$(document).ready(function() {
  ajax_update = function(className, id) {
    return function(value, settings) {
      var post_data = {'_method': 'put', '_return': settings.name};
      post_data[settings.name] = value;
      $.post("<?= url::site("items/__ID__") ?>".replace("__ID__", id),
             post_data,
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
]]>
</script>
