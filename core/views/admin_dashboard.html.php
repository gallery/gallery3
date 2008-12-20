<? defined("SYSPATH") or die("No direct script access."); ?>
<?= $theme->dashboard_blocks(); ?>

<div id="gPhotoStream" class="gBlock">
  <h2>Photo Stream</h2>
  <div class="gBlockContent">
    <p>Recent photos added to your Gallery</p>
    <img src="<?= $theme->url("images/photostream.png") ?>" alt="" />
    <p class="gWarning">Slider type of display. Show titles underneath or on hover. Draw a keyline around albums, or differentiate some how. Each will be linked to item view</p>
  </div>
</div>

<div id="gLogEntries" class="gBlock">
  <h2>Recent Comments</h2>
  <ul class="gBlockContent">
    <li><a href="">hacker</a> 2008-12-10 23:02:23 Something happened</li>
    <li><a href="">username</a> 2008-12-10 23:02:23 Someone logged in</li>
    <li><a href="">username</a> 2008-12-10 23:02:23 New module installed</li>
    <li><a href="">username</a> 2008-12-10 23:02:23 Someone logged in</li>
    <li><a href="">username</a> 2008-12-10 23:02:23 RSS feed updated</li>
  </ul>
</div>

<div id="gLogEntries" class="gBlock">
  <h2>Log Entries</h2>
  <ul class="gBlockContent">
    <li>2008-12-10 23:02:23 Something happened</li>
    <li>2008-12-10 23:02:23 Someone logged in</li>
    <li>2008-12-10 23:02:23 New module installed</li>
    <li>2008-12-10 23:02:23 Someone logged in</li>
    <li>2008-12-10 23:02:23 RSS feed updated</li>
  </ul>
</div>
