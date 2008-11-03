<? defined("SYSPATH") or die("No direct script access."); ?>
<script type="text/javascript">
  myTooltip = new YAHOO.widget.Tooltip("myTooltip", {
  context:"photo-id-1",
  text:"<strong>Photo title</strong><br />taken December 24, 2007<br />Viewed 27 times<br /><br/>Tags: christmas, familiy, home, xmas",
  showDelay:500 } );
</script>

<div id="gAlbumGrid">
  <div id="gAlbumGridHeader">
    <h1>Christmas 2007</h1>
    <span class="understate">(interesting information about the Album)</span>
    <a href="#" id="gSlideshowLink" class="buttonlink">Slideshow</a>
  </div>

  <div class="gAlbumContainer first gAlbum">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Album title</h2>
    <ul class="gMetadata">
      <li>Views: 321</li>
      <li>By: <a href="#">username</a></li>
    </ul>
  </div>

  <div class="gItemContainer">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div class="gItemContainer">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div class="gItemContainer first">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div class="gItemContainer">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div class="gItemContainer">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div class="gItemContainer first">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div class="gItemContainer">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div class="gItemContainer">
    <a href="photo.html"><img id="photo-id-1" class="photo" alt="photo" src="<?= theme::url("images/thumbnail.jpg") ?>" /></a>
    <h2>Photo title</h2>
  </div>

  <div id="gPagination">
    Items 1-10 of 34
    <span class="first_inactive">first</span>
    <span class="previous_inactive">previous</span>
    <a href="#" class="next">next</a>
    <a href="#" class="last">last</a>
  </div>
</div>
