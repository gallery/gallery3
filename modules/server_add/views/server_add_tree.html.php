<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
</script>
<ul id="<?= $tree_id ?>" class="gCheckboxTree">
  <? foreach ($data as $file => $file_info): ?>
  <li class="<?= empty($file_info["is_dir"]) ? "gFile" : "gDirectory gCollapsed ui-icon-left" ?>">
    <? if (!empty($file_info["is_dir"])): ?>
    <span class="ui-icon ui-icon-plus"></span>
    <? endif ?>
       <label> <?= form::checkbox("checkbox[]", p::clean($file_info["path"]), $checked) . " " . p::clean($file) ?> </label>
    <div class="gServerAddChildren" style="display: none"></div>
  </li>
  <? endforeach ?>
</ul>
