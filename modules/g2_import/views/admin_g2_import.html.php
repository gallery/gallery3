<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gAdminG2Config">
  <h1> <?= t("Gallery 2 Import") ?> </h1>
  <p>
    <?= t("Import your Gallery 2 users, photos, movies, comments and tags into your new Gallery 3 installation.") ?>
    <?= t("<b>Note: The importer is a work in progress and does not currently support comments, tags, permissions, capture dates and movies (other than Flash video)</b>") ?>
  </p>
  <?= $form ?>
</div>

<? if (g2_import::is_initialized()): ?>
<div id="gAdminG2Import">
  <h1> <?= t("Import") ?> </h1>
  <ul id="gMessage">
    <li class="gSuccess">
      <?= t("Gallery version %version detected", array("version" => g2_import::version())) ?>
    </li>
  </ul>

  <div class="gInfo">
    <p>
      <?= t("Your Gallery 2 has the following importable data in it") ?>
    </p>
    <ul>
      <li>
        <?= t2("1 user", "%count users", $g2_stats["users"]) ?>
      </li>
      <li>
        <?= t2("1 group", "%count groups", $g2_stats["groups"]) ?>
      </li>
      <li>
        <?= t2("1 album", "%count albums", $g2_stats["albums"]) ?>
      </li>
      <li>
        <?= t2("1 photo", "%count photos", $g2_stats["photos"]) ?>
      </li>
      <li>
        <?= t2("1 movie", "%count movies", $g2_stats["movies"]) ?>
      </li>
      <li>
        <?= t2("1 comment", "%count comments", $g2_stats["comments"]) ?>
      </li>
    </ul>
  </div>

  <? if ($g2_sizes["thumb"]["size"] && $thumb_size != $g2_sizes["thumb"]["size"]): ?>
  <div class="gWarning">
    <?= t("Your most common thumbnail size in Gallery 2 is %g2_pixels pixels, but your Gallery 3 thumbnail size is set to %g3_pixels pixels. <a href=\"%url\">Using the same value</a> will speed up your import.",
        array("g2_pixels" => $g2_sizes["thumb"]["size"],
              "g3_pixels" => $thumb_size,
              "url" => url::site("admin/theme_details"))) ?>
  </div>
  <? endif ?>

  <? if ($g2_sizes["resize"]["size"] && $resize_size != $g2_sizes["resize"]["size"]): ?>
  <div class="gWarning">
    <?= t("Your most common intermediate size in Gallery 2 is %g2_pixels pixels, but your Gallery 3 thumbnail size is set to %g3_pixels pixels. <a href=\"%url\">Using the same value</a> will speed up your import.",
        array("g2_pixels" => $g2_sizes["resize"]["size"],
              "g3_pixels" => $resize_size,
              "url" => url::site("admin/theme_details"))) ?>
  </div>
  <? endif ?>

  <?= t("You can begin your import on the <a href=\"%url\">maintenance page</a>",
        array("url" => url::site("admin/maintenance"))) ?>
</div>
<? endif ?>
