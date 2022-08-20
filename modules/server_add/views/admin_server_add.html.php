<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->css("server_add.css") ?>
<script type="text/javascript">
$("document").ready(function() {
  $("#g-path").gallery_autocomplete(
    "<?= url::site("__ARGS__") ?>".replace("__ARGS__", "admin/server_add/autocomplete"),
    {});
});
</script>

<div class="g-block">
  <h1> <?= t("Add from server administration") ?> </h1>
  <div class="g-block-content">
    <?= $form ?>
    <h2><?= t("Authorized paths") ?></h2>
    <ul id="g-server-add-paths">
      <?php if (empty($paths)): ?>
      <li class="g-module-status g-info"><?= t("No authorized image source paths defined yet") ?></li>
      <?php endif ?>

      <?php foreach ($paths as $id => $path): ?>
      <li>
        <?= html::clean($path) ?>
        <a href="<?= url::site("admin/server_add/remove_path?path=" . urlencode($path) . "&amp;csrf=" . access::csrf_token()) ?>"
           id="icon_<?= $id ?>"
           class="g-remove-dir g-button">
          <span class="ui-icon ui-icon-trash">
            <?= t("delete") ?>
          </span>
        </a>
      </li>
      <?php endforeach ?>
    </ul>
  </div>
</div>
