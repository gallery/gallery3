<?php defined("SYSPATH") or die("No direct script access.") ?>
<ul>
  <li>
    <label for="title"><?= t("Title") ?></label>
    <div id="title" type="text" class="textbox" ><?= $item->title ?></div>
  </li>
  <li>
    <label for="description"><?= t("Description") ?></label>
    <div id="description" class="textarea" ><?= $item->description ?></div>
  </li>
  <li>
    <label for="dirname"><?= t("Directory Name") ?></label>
    <div id="dirname" type="text" class="textbox" ><?= $item->name ?></div>
  </li>
</ul>
