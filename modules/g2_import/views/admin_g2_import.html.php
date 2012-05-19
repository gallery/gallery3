<?php defined("SYSPATH") or die("No direct script access.") ?>
<?= $theme->css("jquery.autocomplete.css") ?>
<?= $theme->script("jquery.autocomplete.js") ?>
<script type="text/javascript">
$("document").ready(function() {
  $("form input[name=embed_path]").gallery_autocomplete(
    "<?= url::site("__ARGS__") ?>".replace("__ARGS__", "admin/g2_import/autocomplete"),
    {
      max: 256,
      loadingClass: "g-loading-small",
    });
});
</script>

<div id="g-admin-g2-import" class="g-block">
  <h1> <?= t("Gallery 2 import") ?> </h1>
  <p>
    <?= t("Import your Gallery 2 users, photos, movies, comments and tags into your new Gallery 3 installation.") ?>
  </p>

  <script type="text/javascript">
    $(document).ready(function() {
      $("#g-admin-g2-import-tabs").tabs()
      <? if (!isset($g2_version)): ?>
      .tabs("disable", 1)
      .tabs("disable", 2)
      <? elseif ($g3_resource_count > .9 * $g2_resource_count):  ?>
      .tabs("select", 2)
      <? else: ?>
      .tabs("select", 1)
      <? endif ?>
      ;

      // Show the tabs after the page has loaded to prevent Firefox from rendering the
      // unstyled page and then flashing.
      $("#g-admin-g2-import-tabs").show();
    });
  </script>
  <div id="g-admin-g2-import-tabs" class="g-block-content" style="display: none">
    <ul>
      <li>
        <a href="#g-admin-g2-import-configure"><?= t("1. Configure Gallery2 path") ?></a>
      </li>
      <li>
        <a href="#g-admin-g2-import-import"><?= t("2. Import!") ?></a>
      </li>
      <li>
        <a href="#g-admin-g2-import-notes"><?= t("3. After your import") ?></a>
      </li>
    </ul>
    <div id="g-admin-g2-import-configure" class="g-block-content">
      <?= $form ?>
    </div>
    <div id="g-admin-g2-import-import">
      <? if (isset($g2_version)): ?>
      <ul>
        <li>
          <?= t("Gallery version %version detected", array("version" => $g2_version)) ?>
        </li>
        <? if ($g2_sizes["thumb"]["size"] && $thumb_size != $g2_sizes["thumb"]["size"]): ?>
        <li>
          <?= t("Your most common thumbnail size in Gallery 2 is %g2_pixels pixels, but your Gallery 3 thumbnail size is set to %g3_pixels pixels. <a href=\"%url\">Using the same value</a> will speed up your import.",
                array("g2_pixels" => $g2_sizes["thumb"]["size"],
                      "g3_pixels" => $thumb_size,
                      "url" => html::mark_clean(url::site("admin/theme_options")))) ?>
        </li>
        <? endif ?>

        <? if ($g2_sizes["resize"]["size"] && $resize_size != $g2_sizes["resize"]["size"]): ?>
        <li>
          <?= t("Your most common intermediate size in Gallery 2 is %g2_pixels pixels, but your Gallery 3 intermediate size is set to %g3_pixels pixels. <a href=\"%url\">Using the same value</a> will speed up your import.",
                array("g2_pixels" => $g2_sizes["resize"]["size"],
                      "g3_pixels" => $resize_size,
                      "url" => html::mark_clean(url::site("admin/theme_options")))) ?>
        </li>
        <? endif ?>

        <li>
          <?
          $t = array();
          $t[] = t2("1 user", "%count users", $g2_stats["users"]);
          $t[] = t2("1 group", "%count groups", $g2_stats["groups"]);
          $t[] = t2("1 album", "%count albums", $g2_stats["albums"]);
          $t[] = t2("1 photo", "%count photos/movies", $g2_stats["photos"] + $g2_stats["movies"]);
          $t[] = t2("1 comment", "%count comments", $g2_stats["comments"]);
          $t[] = t2("1 tagged photo/movie/album", "%count tagged photos/movies/albums",
                    $g2_stats["tags"]);
          ?>
          <?= t("Your Gallery 2 has the following importable data in it: %t0, %t1, %t2, %t3, %t4, %t5",
                array("t0" => $t[0], "t1" => $t[1], "t2" => $t[2],
                      "t3" => $t[3], "t4" => $t[4], "t5" => $t[5])) ?>
        </li>

        <? if ($g3_resource_count): ?>
        <li>
          <?
          $t = array();
          $t[] = t2("1 user", "%count users", $g3_stats["user"]);
          $t[] = t2("1 group", "%count groups", $g3_stats["group"]);
          $t[] = t2("1 album", "%count albums", $g3_stats["album"]);
          $t[] = t2("1 photo/movie", "%count photos/movies", $g3_stats["item"]);
          $t[] = t2("1 comment", "%count comments", $g3_stats["comment"]);
          $t[] = t2("1 tagged photo/movie/album", "%count tagged photos/movies/albums", $g3_stats["tag"]);
          ?>
          <?= t("It looks like you've imported the following Gallery 2 data already: %t0, %t1, %t2, %t3, %t4, %t5",
                array("t0" => $t[0], "t1" => $t[1], "t2" => $t[2],
                      "t3" => $t[3], "t4" => $t[4], "t5" => $t[5])) ?>
        </li>
        <? endif ?>
      </ul>
      <p>
        <a class="g-button g-dialog-link ui-state-default ui-corner-all"
           href="<?= url::site("admin/maintenance/start/g2_import_task::import?csrf=$csrf") ?>">
          <?= t("Begin import!") ?>
        </a>
      </p>
      <? endif ?>
    </div>
    <div id="g-admin-g2-import-notes" class="g-text">
      <ul>
        <li>
          <?= t("Gallery 3 does not support per-user / per-item permissions.  <b>Review permissions!</b>") ?>
        </li>
        <li>
          <?= t("The only supported file formats are JPG, PNG and GIF, FLV and MP4.  Other formats will be skipped.") ?>
        </li>
        <li>
          <p>
            <?= t("Redirecting Gallery 2 URLs once your migration is complete.  Put this block at the top of your gallery2/.htaccess file and all Gallery 2 urls will be redirected to Gallery 3") ?>
          </p>

          <textarea id="g-g2-redirect-rules" rows="4" cols="60">&lt;IfModule mod_rewrite.c&gt;
      Options +FollowSymLinks
      RewriteEngine On
      RewriteBase <?= html::clean(g2_import::$g2_base_url) ?>

      RewriteRule ^(.*)$ <?= url::site("g2/map?path=\$1") ?>   [QSA,L,R=301]
    &lt;/IfModule&gt;</textarea>
          <script type="text/javascript">
            $(document).ready(function() {
              $("#g-g2-redirect-rules").click(function(event) {
                this.select();
              });
            });
          </script>
        </li>
      </ul>
    </div>
  </div>
</div>
