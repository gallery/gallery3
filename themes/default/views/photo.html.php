<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gItemHeader">
  <ul id="gItemMenu" class="sf-menu">
    <li><a href="#" id="gFullsizeLink" title="<?= _("View full size image") ?>"><?= _("View full size image") ?></a></li>
    <li><a href="#" id="gHybridLink" title="<?= _("View album in hybrid mode") ?>"><?= _("Hybrid view") ?></a></li>
    <li><?= $theme->album_top() ?></li>
    <li><a href="#">Options</a>
      <ul>
        <li><a href="<?= url::site("/form/add/photos/$item->id") ?>" 
            title="<?= _("Add an item") ?>"
            class="gDialogLink"><?= _("Add an item") ?></a></li>
        <li><a href="<?= url::site("/form/add/albums/$item->id") ?>" 
            title="<?= _("Add album") ?>"
            class="gDialogLink"><?= _("Add album") ?></a></li>
      </ul>
    </li>
  </ul>

  <h1><?= $item->title_edit ?></h1>
</div>

<div id="gItem">
  <img id="gPhotoID-<?= $item->id ?>" alt="photo" src="<?= $item->resize_url() ?>"
       width="<?= $item->resize_width ?>"
       height="<?= $item->resize_height ?>" />
  <div><?= $item->description_edit ?></div>

  <?= $theme->photo_bottom() ?>
</div>
