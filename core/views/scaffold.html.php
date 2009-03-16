<?php defined("SYSPATH") or die("No direct script access.") ?>
<html>
  <head>
    <title>Gallery3 Scaffold</title>
    <style>
      body {
        background: #999;
        font-family: Trebuchet MS;
      }

      div.outer {
        width: 650px;
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

      fieldset {
        margin-left: 1em;
        margin-bottom: 1em;
        padding-bottom: 0;
      }

      a {
        text-decoration: none;
      }

      a:hover {
        text-decoration: underline;
      }

      a.allowed {
        color: green;
        font-size: 110%;
      }

      a.denied {
        color: red;
        font-size: 90%;
      }

      .gHide {
        display: none;
      }

      div#browse {
        border: 1px solid black;
        background: #eee;
        width: 450px;
        padding: 2px;
        margin: 5px 0px 0px 1em;
      }
    </style>
  </head>
  <body>
    <div class="outer">
      <center>
        <img src="<?= url::file("core/images/gallery.png") ?>"/>
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

        <? if ($album_count > 0): ?>
        <div id="browse">
          <p>
            <?= html::anchor("albums/1", "Browse Gallery") ?>
            <i>(<?= $album_count ?> albums, <?= $photo_count ?> photos, <?= $comment_count ?> comments, <?= $tag_count ?> tags)</i>
          </p>
        </div>
        <? endif ?>

        <div id="actions" class="activity">
            <fieldset>
              <legend>Generate Test Data</legend>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("scaffold/add_albums_and_photos/$count", "$count") ?>
              <? endforeach ?>
              ] photos and albums
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("scaffold/add_albums_and_photos/$count/album", "$count") ?>
              <? endforeach ?>
              ] albums only
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("scaffold/add_comments/$count", "$count") ?>
              <? endforeach ?>
              ] comments
            </p>
            <p>
              add: [
              <? foreach (array(1, 10, 50, 100, 500, 1000) as $count): ?>
                <?= html::anchor("scaffold/add_tags/$count", "$count") ?>
              <? endforeach ?>
              ] tags
            </p>
            </fieldset>
            <fieldset>
              <legend>Packaging</legend>
              <a href="<?= url::site("scaffold/package") ?>">Make Package</a>
            </fieldset>
        </div>
      </div>
    </div>
  </body>
</html>
