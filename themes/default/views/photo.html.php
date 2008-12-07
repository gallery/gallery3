<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gItemHeader">
  <ul id="gItemMenu">
    <li><a href="#" title="<?= _("View full size image") ?>">
	    <img src="<?= $theme->url("images/ico-view-fullsize.png") ?>" 
    		alt="<?= sprintf(_("View full size image (%dx%d)"), $item->width, $item->height) ?>" /></a></li>
    <li><a href="#" title="<?= _("View album in hybrid mode") ?>">
    	<img src="<?= $theme->url("images/ico-view-hybrid.png") ?>" 
    		alt="<?= _("View album in hybrid mode") ?>" /></a></li>
    <li><?= $theme->album_top() ?></li>
    <li><a href="#" title="<?= _("Options for this item") ?>" 
			class="gButtonLink gDialogLink">v <?= _("Options") ?></a></li>
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
