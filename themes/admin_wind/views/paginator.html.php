<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php
// This is a generic paginator for admin collections.  Depending on the page type, there are
// different sets of variables available.  With this data, you can make a paginator that
// lets you say "You're viewing photo 5 of 35", or "You're viewing photos 10 - 18 of 37"
// for album views.

//
// Available variables for all page types:
//   $page_type               - "collection", "item", or "other"
//   $page_subtype            - "album", "movie", "photo", "tag", etc.
//   $previous_page_url       - the url to the previous page, if there is one
//   $next_page_url           - the url to the next page, if there is one
//   $total                   - the total number of photos in this album
//
// Available for the "collection" page types:
//   $page                    - what page number we're on
//   $max_pages               - the maximum page number
//   $page_size               - the page size
//   $first_page_url          - the url to the first page, or null if we're on the first page
//   $last_page_url           - the url to the last page, or null if we're on the last page
//   $first_visible_position  - the position number of the first visible photo on this page
//   $last_visible_position   - the position number of the last visible photo on this page
//
// Available for "item" page types:
//   $position                - the position number of this photo
//
?>

<?php if ($total): ?>
<ul class="g-paginator ui-helper-clearfix">
  <li class="g-first">
  <?php if ($page_type == "collection"): ?>
    <?php if (isset($first_page_url)): ?>
      <a href="<?= $first_page_url ?>" class="g-button ui-icon-left ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-seek-first"></span><?= t("First") ?></a>
    <?php else: ?>
      <a class="g-button ui-icon-left ui-state-disabled ui-corner-all">
        <span class="ui-icon ui-icon-seek-first"></span><?= t("First") ?></a>
    <?php endif ?>
  <?php endif ?>

  <?php if (isset($previous_page_url)): ?>
    <a href="<?= $previous_page_url ?>" class="g-button ui-icon-left ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-seek-prev"></span><?= t("Previous") ?></a>
  <?php else: ?>
    <a class="g-button ui-icon-left ui-state-disabled ui-corner-all">
      <span class="ui-icon ui-icon-seek-prev"></span><?= t("Previous") ?></a>
  <?php endif ?>
  </li>

  <li class="g-info">
    <?php if ($total): ?>
      <?php if ($page_type == "collection"): ?>
        <?= /* @todo This message isn't easily localizable */
            t2("Viewing %from_number of %count",
               "Viewing %from_number - %to_number of %count",
               $total,
               array("from_number" => $first_visible_position,
                     "to_number" => $last_visible_position,
                     "count" => $total)) ?>
      <?php else: ?>
        <?= t("%position of %total", array("position" => $position, "total" => $total)) ?>
      <?php endif ?>
    <?php endif ?>
  </li>

  <li class="g-text-right">
  <?php if (isset($next_page_url)): ?>
    <a href="<?= $next_page_url ?>" class="g-button ui-icon-right ui-state-default ui-corner-all">
      <span class="ui-icon ui-icon-seek-next"></span><?= t("Next") ?></a>
  <?php else: ?>
    <a class="g-button ui-state-disabled ui-icon-right ui-corner-all">
      <span class="ui-icon ui-icon-seek-next"></span><?= t("Next") ?></a>
  <?php endif ?>

  <?php if ($page_type == "collection"): ?>
    <?php if (isset($last_page_url)): ?>
      <a href="<?= $last_page_url ?>" class="g-button ui-icon-right ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-seek-end"></span><?= t("Last") ?></a>
    <?php else: ?>
      <a class="g-button ui-state-disabled ui-icon-right ui-corner-all">
        <span class="ui-icon ui-icon-seek-end"></span><?= t("Last") ?></a>
    <?php endif ?>
  <?php endif ?>
  </li>
</ul>
<?php endif ?>