<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul class="g-buttonset">
  <li>
    <a target="_blank"
       <?php if (isset($info['author_url'])): ?>
       class="ui-state-default ui-icon ui-icon-person ui-corner-left"
       href="<?php echo $info['author_url'] ?>"
       <?php else: ?>
       class="ui-state-disabled ui-icon ui-icon-person ui-corner-left"
       href="#"
       <?php endif ?>

       <?php if (isset($info['author_name'])): ?>
       title="<?php echo $info['author_name'] ?>"
       <?php endif ?>
       >
       <?php if (isset($info['author_name'])): ?>
       <?php echo $info['author_name'] ?>
       <?php endif ?>
    </a>
  </li>
  <li>
    <a target="_blank"
       <?php if (isset($info['info_url'])): ?>
       class="ui-state-default ui-icon ui-icon-info"
       href="<?php echo $info['info_url'] ?>"
       <?php else: ?>
       class="ui-state-disabled ui-icon ui-icon-info"
       href="#"
       <?php endif ?>
       >
      <?php echo t("info") ?>
    </a>
  </li>
  <li>
    <a target="_blank"
       <?php if (isset($info['discuss_url'])): ?>
       class="ui-state-default ui-icon ui-icon-comment ui-corner-right"
       href="<?php echo $info['discuss_url'] ?>"
       <?php else: ?>
       class="ui-state-disabled ui-icon ui-icon-comment ui-corner-right"
       href="#"
       <?php endif ?>
       >
      <?php echo t("discuss") ?>
    </a>
  </li>
</ul>
