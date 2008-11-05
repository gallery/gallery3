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
        margin: 0;
        padding-left: 2em;
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

      ul.choices {
        padding-top: 0;
        padding-left: 1em;
        margin: 0px;
      }

      ul.choices li {
        display: inline;
        list-stype-type: none;
        padding: 0px;
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
      }

      ul#tabs a:link, ul#tabs a:visited {
        color: #fff;
        background-color: #036;
        text-decoration: none;
      }

      ul#tabs a:hover {
        color: #fff;
        background-color: #369;
        text-decoration: none;
      }
    </style>
    <script type="text/javascript" src="<?= url::base(), "lib/jquery.js" ?>"></script>

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
          <li><a href="javascript:show('#configuration')">Configuration</a></li>
          <li><a href="javascript:show('#actions')">Actions</a></li>
          <li><a href="javascript:show('#docs')">Docs</a></li>
        </ul>

        <div id="activities">
          <script>
            show = function(section) {
              $("div.activity").slideUp();
              $(section).slideDown();
            }
          </script>

          <div id="configuration" class="activity" style="display: block">
            <?= $syscheck ?>
          </div>

          <div id="actions" class="activity">
            <p>
              <?= html::anchor("album/1", "Browse Gallery") ?>
              <i>(<?= $album_count ?> albums, <?= $photo_count ?> photos)</i>
            </p>
            <ul class="choices">
              <li> add: [</li>
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
              <li> <?= html::anchor("welcome/add/$count", "$count") ?> </li>
              <? endforeach ?>
              <li>] photos and albums </li>
            </ul>
          </div>

          <div id="docs" class="activity">
            <ul>
              <li>
                <a href="http://docs.google.com/Doc?id=dfjxt593_184ff9jhmd8&hl=en">Gallery3: Prioritized Feature List</a>
              </li>
              <li>
                <a href="http://docs.google.com/Doc?id=dfjxt593_185czczpm4f&hl=en">Gallery3: Secondary Features</a>
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
