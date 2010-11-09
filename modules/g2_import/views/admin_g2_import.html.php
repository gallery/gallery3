<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-admin-g2-import" class="g-block">
  <h1> <?= t("Gallery 2 import") ?> </h1>
  <p>
    <?= t("Import your Gallery 2 users, photos, movies, comments and tags into your new Gallery 3 installation.") ?>
  </p>

  <div class="g-block-content">
    <?= $form ?>
  </div>

  <div class="g-block-content">
    <? if (g2_import::is_initialized()): ?>
    <div id="g-admin-g2-import-details">
      <h2> <?= t("Import") ?> </h2>
      <ul id="g-action-status" class="g-message-block">
        <li class="g-success">
          <?= t("Gallery version %version detected", array("version" => $version)) ?>
        </li>
        <? if ($g2_sizes["thumb"]["size"] && $thumb_size != $g2_sizes["thumb"]["size"]): ?>
        <li class="g-warning">
          <?= t("Your most common thumbnail size in Gallery 2 is %g2_pixels pixels, but your Gallery 3 thumbnail size is set to %g3_pixels pixels. <a href=\"%url\">Using the same value</a> will speed up your import.",
                array("g2_pixels" => $g2_sizes["thumb"]["size"],
                      "g3_pixels" => $thumb_size,
                      "url" => html::mark_clean(url::site("admin/theme_options")))) ?>
        </li>
        <? endif ?>

        <? if ($g2_sizes["resize"]["size"] && $resize_size != $g2_sizes["resize"]["size"]): ?>
        <li class="g-warning">
          <?= t("Your most common intermediate size in Gallery 2 is %g2_pixels pixels, but your Gallery 3 intermediate size is set to %g3_pixels pixels. <a href=\"%url\">Using the same value</a> will speed up your import.",
              array("g2_pixels" => $g2_sizes["resize"]["size"],
                    "g3_pixels" => $resize_size,
                    "url" => html::mark_clean(url::site("admin/theme_options")))) ?>
        </li>
        <? endif ?>

        <li class="g-info">
          <?= t("Your Gallery 2 has the following importable data in it:") ?>
          <p>
            <?= t2("1 user", "%count users", $g2_stats["users"]) ?>,
            <?= t2("1 group", "%count groups", $g2_stats["groups"]) ?>,
            <?= t2("1 album", "%count albums", $g2_stats["albums"]) ?>,
            <?= t2("1 photo", "%count photos", $g2_stats["photos"]) ?>,
            <?= t2("1 movie", "%count movies", $g2_stats["movies"]) ?>,
            <?= t2("1 comment", "%count comments", $g2_stats["comments"]) ?>,
            <?= t2("1 tagged photo/movie/album",
                "%count tagged photos/movies/albums", $g2_stats["tags"]) ?>
          </p>
        </li>
      </ul>

      <p>
        <a class="g-button g-dialog-link ui-state-default ui-corner-all"
           href="<?= url::site("admin/maintenance/start/g2_import_task::import?csrf=$csrf") ?>">
          <?= t("Begin import!") ?>
        </a>
      </p>
    </div>

    <div class="g-block-content">
      <div id="g-admin-g2-import-notes">
        <h2> <?= t("Notes") ?> </h2>
        <ul class="enumeration">
          <li>
            <?= t("Gallery 3 does not support per-user / per-item permissions.  <b>Review permissions after your import is done.</b>") ?>
          </li>
          <li>
            <?= t("The only supported file formats are JPG, PNG and GIF, FLV and MP4.  Other formats will be skipped.") ?>
          </li>
          <li>
            <?= t("Deactivating the <b>notification</b>, <b>search</b> and <b>exif</b> modules during your import will make it go faster.") ?>
          </li>
          <li>
            <?= t("The eAccelerator and XCache PHP performance extensions are known to cause issues.  If you're using either of those and are having problems, please disable them while you do your import.  Add the following lines: <pre>%lines</pre> to gallery3/.htaccess and remove them when the import is done.", array("lines" => "\n\n  php_value eaccelerator.enable 0\n  php_value xcache.cacher off\n  php_value xcache.optimizer off\n\n")) ?>
          </li>
        </ul>
      </div>
    </div>

    <div class="g-block-content">
      <div>
        <h2> <?= t("Migrating from Gallery 2") ?> </h2>
        <p>
          <?= t("Once your migration is complete, put this block at the top of your gallery2/.htaccess file and all Gallery 2 urls will be redirected to Gallery 3") ?>
        </p>

        <textarea rows="4" cols="60">&lt;IfModule mod_rewrite.c&gt;
  Options +FollowSymLinks
  RewriteEngine On
  RewriteBase <?= html::clean(g2_import::$g2_base_url) ?>

  RewriteRule ^(.*)$ <?= url::site("g2/map?path=\$1") ?>   [QSA,L,R=301]
&lt;/IfModule&gt;</textarea>
      </div>
      <? endif ?>
    </div>
  </div>
</div>
