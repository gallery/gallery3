<? defined("SYSPATH") or die("No direct script access."); ?>
<?= $theme->dashboard_blocks(); ?>
<div class="gBlock">
  <h2>Status Messages</h2>
  <ul class="gBlockContent gMessages">
    <li class="gWarning"><a href="#" title="">Gallery 3.1 is available, you're running Gallery 3.0. Update now!</a></li>
    <li class="gError"><a href="#" title="">Unable to write to /home/username/gallery3/var</a></li>
    <li class="gSuccess"><a href="#" title="">Permissions issues fixed</a></li>
    <li class="gInfo"><a href="#" title="">Just a plain information message</a></li>
    <li class="gHelp"><a href="#" title="">Contextual help or tip<br/>And here's a second line</a></li>
  </ul>
</div>

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
