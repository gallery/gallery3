<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= html::anchor($item->file_url(true), "", $attrs) ?>
<script type="text/javascript">
  flowplayer(
    "<?= $attrs["id"] ?>",
    {
      src: "<?= url::abs_file("lib/flowplayer.swf") ?>",
      wmode: "transparent",
      provider: "pseudostreaming"
    },
    {
      plugins: {
        pseudostreaming: {
          url: "<?= url::abs_file("lib/flowplayer.pseudostreaming.swf") ?>"
        },
        controls: {
          autoHide: 'always',
          hideDelay: 2000
        }
      }
    }
  )
</script>
