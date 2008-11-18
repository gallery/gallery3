<? defined("SYSPATH") or die("No direct script access."); ?>
<html>
  <head>
    <title>Gallery3 Scaffold</title>
    <style>
      body {
        background: #999;
        font-family: Trebuchet MS;
      }

      div.outer {
        width: 600px;
        background: white;
        border: 1px solid black;
        margin: 0 auto;
        padding: -10px;
      }

      div.inner {
        padding: 0 1em 0 1em;
        margin: 0px;
      }

      h1, h2, h3 {
        margin-bottom: .1em;
      }

      p {
        margin: 0 0 0 0;
        padding-left: 1em;
      }

      table {
        padding-left: 1em;
      }

      pre {
        border: 1px solid #666;
        margin: 1em 0;
        padding: .5em;
        overflow: scroll;
      }

      .error {
        color: red;
      }

      .success {
        color: green;
      }

      p.success {
        font-weight: bold;
      }

      div.block {
        padding: 0px;
        margin: 0px;
      }

      ul {
        margin-top: -.25em;
      }

      div#activities {
        margin-bottom: 1em;
      }

      div.activity {
        display: none;
      }

      ul#tabs {
        margin-left: 0;
        padding: 1em 0 2px 1em;
        white-space: nowrap;
        border-bottom: 1px solid black;
      }

      ul#tabs li {
        display: inline;
        list-style-type: none;
      }

      ul#tabs a {
        padding: 3px 10px;
        color: #fff;
        background-color: #036;
        text-decoration: none;
      }

      ul#tabs a:hover {
        background-color: #369;
      }

      fieldset {
        margin-left: 1em;
        padding-bottom: 0;
      }

      div#photo_upload_wrap {
        display: inline;
      }

      div#photo_upload_wrap {
        display: inline;
      }

    </style>
    <script type="text/javascript" src="<?= url::file("lib/jquery.js") ?>"></script>
    <script type="text/javascript" src="<?= url::file("lib/jquery.cookie.js") ?>"></script>
    <script type="text/javascript" src="<?= url::file("lib/jquery.MultiFile.js") ?>"></script>
  </head>
  <body>
    <div class="outer">
      <center>
        <img src="http://www.gallery2.org/gallery2.png"/>
      </center>
      <div class="inner">
        <h1>Gallery3 Scaffold</h1>
        <p>
          This is
          a <b><a href="http://www.google.com/images?q=scaffold">scaffold</a></b>:
          a <i>temporary structure built to support the developers as
          they create the real product</i>. As we flesh out Gallery 3,
          we'll make it possible for you to peer inside and see the
          application taking shape.  Eventually, this page will go
          away and you'll start in the application itself.  In the
          meantime, here are some useful links to get you started.
        </p>

        <ul id="tabs">
          <li><a href="javascript:show('config')">Configuration</a></li>
          <li><a href="javascript:show('actions')">Actions</a></li>
          <li><a href="javascript:show('info')">Info</a></li>
          <li><a href="javascript:show('benchmarks')">Benchmarks</a></li>
          <li><a href="javascript:show('docs')">Docs</a></li>
        </ul>

        <div id="activities">
          <script>
            show = function(section) {
              var section_id = "#" + section;
              if ($(section_id).css("display") == "block") {
                return;
              }
              $("div.activity").slideUp();
              $(section_id).slideDown();
              $.cookie("active_section", section);
            }
            $(document).ready(function(){
              var active_section = $.cookie("active_section");
              if (!active_section) {
                active_section = 'config';
              }
              $("#" + active_section).show();
              $("#photo_upload").MultiFile();
            });
          </script>

          <div id="config" class="activity">
            <?= $syscheck ?>
          </div>

          <div id="actions" class="activity">
            <p>
              <?= html::anchor("album/1", "Browse Gallery") ?>
              <i>(<?= $album_count ?> albums, <?= $photo_count ?> photos)</i>
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
              <?= html::anchor("welcome/add_albums_and_photos/$count", "$count") ?>
              <? endforeach ?>
              ] photos and albums
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
              <?= html::anchor("welcome/add_comments/$count", "$count") ?>
              <? endforeach ?>
              ] comments
            </p>
            <fieldset>
              <legend>Photos</legend>
              <form method="post" action="<?= url::site("album/1") ?>" enctype="multipart/form-data">
                <input type="submit" value="upload"/>
                <input id="photo_upload" name="file[]" type="file"/>
                <input type="hidden" name="type" value="photo"/>
                <input type="hidden" name="_method" value="put"/>
              </form>
            </fieldset>
            <fieldset>
              <legend>Albums</legend>
              <form method="post" action="<?= url::site("album/1") ?>">
                <input type="submit" value="create"/>
                <input type="text" name="name"/>
                <input type="hidden" name="type" value="album"/>
                <input type="hidden" name="_method" value="put"/>
              </form>
            </fieldset>
          </div>

          <div id="info" class="activity">
            <ul>
              <li> <?= html::anchor("welcome/mptt?type=text", "MPTT tree (text)") ?> </li>
              <li>
                <?= html::anchor("welcome/mptt", "MPTT tree (graph)") ?>
                <i>(requires /usr/bin/dot from the graphviz package)</i>
              </li>
              <? if ($deepest_photo): ?>
              <li>
                <?= html::anchor("photo/{$deepest_photo->id}", "Deepest photo") ?>
                <i>(<?= $deepest_photo->level ?> levels deep)</i>
              </li>
              <? endif ?>
              <li> Profiling:
                <? if (Session::instance()->get("use_profiler", false)): ?>
                <b>on</b> <?= html::anchor("welcome/profiler?use_profiler=0", "off") ?>
                <? else: ?>
                <?= html::anchor("welcome/profiler?use_profiler=1", "on") ?> <b>off</b>
                <? endif ?>
              </li>
            </ul>
          </div>

          <div id="benchmarks" class="activity">
            <ul>
              <li>
                <?= html::anchor("welcome/i18n/build", "Make Translation") ?>
              </li>
              <li>
                <?= html::anchor("welcome/i18n/run", "Run Translation") ?>
              </li>
            </ul>
          </div>

          <div id="docs" class="activity">
            <ul>
              <li>
                <a href="http://codex.gallery2.org/Gallery3:Features">Gallery3: Features</a>
              </li>
              <li>
                <a href="http://gallery.svn.sourceforge.net/viewvc/gallery/trunk/eval/gx/ui/HTML/index.html">Mockups</a>
              </li>
              <li>
                <a href="http://www.nabble.com/Rough-Gallery-3-time-line-td20240153.html">Rough Timeline</a> (as of Oct 29, 2008)
              </li>
              <li>
                <a href="http://codex.gallery2.org/Gallery3:About">Gallery3: About Page</a>
              </li>
              <li>
                <a href="http://codex.gallery2.org/Gallery3:Coding_Standards">Gallery3: Coding Standards</a>
              </li>
              <li>
                <a href="http://docs.kohanaphp.com/">Kohana Documentation</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
