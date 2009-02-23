<?php defined("SYSPATH") or die("No direct script access.") ?>
<? if ($item->type == "photo"): ?>
<? $title = t("Edit this photo") ?>
<? elseif ($item->type == "movie"): ?>
<? $title = t("Edit this movie") ?>
<? elseif ($item->type == "album"): ?>
<? $title = t("Edit this album") ?>
<? endif ?>
<a class="edit gDialogLink" href="<?= url::site("quick/form_edit/$item->id") ?>"
  title="<?= $title ?>">
  <span>
    <?= $title ?>
  </span>
</a>

<? if ($item->is_photo() && graphics::can("rotate")): ?>
<a class="counter-clockwise" href="<?= url::site("quick/rotate/$item->id/ccw?csrf=" . access::csrf_token()) ?>"
  title="<?= t("Rotate 90 degrees counter clockwise") ?>">
  <span>
    <?= t("Rotate 90 degrees counter clockwise") ?>
  </span>
</a>

<a class="clockwise" href="<?= url::site("quick/rotate/$item->id/cw?csrf=" . access::csrf_token()) ?>"
  title="<?= t("Rotate 90 degrees clockwise") ?>">
  <span>
    <?= t("Rotate 90 degrees clockwise") ?>
  </span>
</a>
<? endif ?>

<? if ($item->type == "photo"): ?>
<? $title = t("Move this photo to another album") ?>
<? elseif ($item->type == "movie"): ?>
<? $title = t("Move this movie to another album") ?>
<? elseif ($item->type == "album"): ?>
<? $title = t("Move this album to another album") ?>
<? endif ?>
<a class="move gDialogLink" href="<?= url::site("move/browse/$item->id") ?>"
  title="<?= $title ?>">
  <span>
    <?= $title ?>
  </span>
</a>

<? if (access::can("edit", $item->parent())): ?>
<? if ($item->type == "photo"): ?>
<? $title = t("Choose this photo as the album cover") ?>
<? elseif ($item->type == "movie"): ?>
<? $title = t("Choose this movie as the album cover") ?>
<? elseif ($item->type == "album"): ?>
<? $title = t("Choose this album as the album cover") ?>
<? endif ?>
<a class="cover" href="<?= url::site("quick/make_album_cover/$item->id?csrf=" . access::csrf_token()) ?>"
   title="<?= $title ?>">
  <span>
    <?= $title ?>
  </span>
</a>

<? if ($item->type == "photo"): ?>
<? $title = t("Delete this photo") ?>
<? elseif ($item->type == "movie"): ?>
<? $title = t("Delete this movie") ?>
<? elseif ($item->type == "album"): ?>
<? $title = t("Delete this album") ?>
<? endif ?>
<a class="delete" href="<?= url::site("quick/delete/$item->id?csrf=" . access::csrf_token()) ?>"
   title="<?= $title ?>">
  <span>
    <?= $title ?>
  </span>
</a>
<? endif ?>

<? if ($item->is_album()): ?>
<a class="options" href="#" title="<?= t("additional options") ?>">
  <span>
    <?= t("Additional options") ?>
  </span>
</a>

<div id="gQuickPaneOptions" style="display: none">
  <a class="add_item gDialogLink" href="<?= url::site("form/add/albums/$item->id?type=photo") ?>"
    title="<?= t("Add a photo") ?>">
    <?= t("Add a photo") ?>
  </a>

  <a class="add_album gDialogLink" href="<?= url::site("form/add/albums/$item->id?type=album") ?>"
    title="<?= t("Add an album") ?>">
    <?= t("Add an album") ?>
  </a>

  <a class="permissions gDialogLink" href="<?= url::site("permissions/browse/$item->id") ?>"
    title="<?= t("Edit permissions") ?>">
    <?= t("Edit permissions") ?>
  </a>
</div>
<? endif ?>
