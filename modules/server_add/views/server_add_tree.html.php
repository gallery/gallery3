<?php defined("SYSPATH") or die("No direct script access.") ?>
<li class="ui-icon-left">
  <span class="ui-icon ui-icon-folder-open"></span>
  <span class="g-directory" ref="">
    <?php echo t("All") ?>
  </span>
  <ul>

    <?php foreach ($parents as $dir): ?>
    <li class="ui-icon-left">
      <span class="ui-icon ui-icon-folder-open"></span>
      <span class="g-directory" ref="<?php echo html::clean_attribute($dir) ?>">
        <?php echo html::clean(basename($dir)) ?>
      </span>
      <ul>
        <?php endforeach ?>

        <?php foreach ($files as $file): ?>
        <li class="ui-icon-left">
          <span class="ui-icon <?php echo is_dir($file) ? "ui-icon-folder-collapsed" : "ui-icon-document" ?>"></span>
          <span class="<?php echo is_dir($file) ? "g-directory" : "g-file" ?>"
                ref="<?php echo html::clean_attribute($file) ?>" >
            <?php echo html::clean(basename($file)) ?>
          </span>
        </li>
        <?php endforeach ?>
        <?php if (!$files): ?>
        <li> <i> <?php echo t("empty") ?> </i> </li>
        <?php endif ?>

        <?php foreach ($parents as $dir): ?>
      </ul>
    </li>
    <?php endforeach ?>

  </ul>
</li>
