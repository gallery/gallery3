<? defined("SYSPATH") or die("No direct script access."); ?>
<div id="gHeader">
  <img id="gLogo" alt="<?= _("Logo") ?>" src="<?= theme::url("images/logo.png") ?>" />

  <h1><?= $item->title ?></h1>

  <div id="gLoginMenu">
    <a href="#"><?= _("Register") ?></a> |
    <a href="#"><?= _("Login") ?>
  </div>

  <ul id="gSiteMenu">
    <li><a href="index.html"><?= _("HOME") ?></a></li>
    <li><a class="active" href="browse.html"><?= _("BROWSE") ?></a></li>
    <li><a href="upload.html"><?= _("UPLOAD") ?></a></li>
    <li><a href="upload.html"><?= _("MY GALLERY") ?></a></li>
    <li><a href="#"><?= _("ADMIN") ?></a></li>
  </ul>

  <ul id="gBreadcrumbs">
    <li class="root"><a href="#">Home</a></li>
    <li><a href="#">Friends &amp; Family</a></li>
    <li class="active"><span>Christmas 2007</span></li>
  </ul>

  <form id="gSearchForm">
    <input type="text" class="text" value="<?= _("Search Gallery ...") ?>"/>
    <input type="submit" class="submit" value="search" />
  </form>
</div>
