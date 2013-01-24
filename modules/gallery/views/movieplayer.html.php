<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= html::anchor($url, "", $attrs) ?>
<script type="text/javascript">
  var id = "<?= $attrs["id"] ?>";
  var max_size = <?= $max_size ?>;
  // set the size of the movie html anchor, taking into account max_size and height of control bar
  function set_movie_size(width, height) {
    if((width > max_size) || (height > max_size)) {
      if (width > height) {
        height = Math.ceil(height * max_size / width);
        width = max_size;
      } else {
        width = Math.ceil(width * max_size / height);
        height = max_size;
      }
    }
    height += flowplayer(id).getConfig().plugins.controls.height;
    $("#" + id).css({width: width, height: height});
  };
  // setup flowplayer
  flowplayer(id,
    $.extend(true, {
      "src": "<?= url::abs_file("lib/flowplayer.swf") ?>",
      "wmode": "transparent",
      "provider": "pseudostreaming"
    }, <?= json_encode($fp_params) ?>),
    $.extend(true, {
      "plugins": {
        "pseudostreaming": {
          "url": "<?= url::abs_file("lib/flowplayer.pseudostreaming-byterange.swf") ?>"
        },
        "controls": {
          "autoHide": "always",
          "hideDelay": 2000,
          "height": 24
        }
      },
      "clip": {
        "scaling": "fit",
        "onMetaData": function(clip) {
          // set movie size a second time using actual size from metadata
          set_movie_size(parseInt(clip.metaData.width), parseInt(clip.metaData.height));
        }
      }
    }, <?= json_encode($fp_config) ?>)
  ).ipad();
  // set movie size using width and height passed from movie_img function
  $("document").ready(set_movie_size(<?= $width ?>, <?= $height ?>));
</script>
