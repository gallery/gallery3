<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
$("#<?= $tree_id ?>").ready(function() {
  $("#<?= $tree_id ?> span.ui-icon").click(function(event) {
    open_close_branch(this, event);
  });

  $("#<?= $tree_id ?> :checkbox").click(function(event) {
    checkbox_click(this, event);
  });
});
</script>
<ul id="<?= $tree_id ?>" class="gCheckboxTree">
  <? foreach ($data as $file => $file_info): ?>
  <li class="<?= empty($file_info["is_dir"]) ? "gFile" : "gDirectory gCollapsed ui-icon-left" ?>">
    <? if (!empty($file_info["is_dir"])): ?>
      <span class="ui-icon ui-icon-plus" ref="<?= $file ?>"></span>
    <? endif ?>
    <label> <?= form::checkbox("checkbox", $file) . " $file" ?> </label>
  </li>
  <? endforeach ?>
</ul>
