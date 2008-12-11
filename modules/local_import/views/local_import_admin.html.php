<? defined("SYSPATH") or die("No direct script access."); ?>
<script>
  var base_url = "<?= url::base(true) ?>";
</script>
<div id="gLocalImportAdmin">
  <div id="gImportLocalDirList">
    <?= $dir_list ?>
  </div>
  <div>
    <?= $add_form ?>
  </div>
</div>
