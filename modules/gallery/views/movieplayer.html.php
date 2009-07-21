<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= html::anchor($item->file_url(true), "", $attrs) ?>
<script>
  flowplayer(
    "<?= $attrs["id"] ?>",
    {
      src: "<?= url::abs_file("lib/flowplayer.swf") ?>",
      wmode: "transparent"
    },
    {
      plugins: {
        h264streaming: {
          url: "<?= url::abs_file("lib/flowplayer.h264streaming.swf") ?>"
        },
        controls: {
          autoHide: 'always',
          hideDelay: 2000
        }
      }
    }
  )
</script>
