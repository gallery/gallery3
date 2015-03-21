<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-album-header">
  <div id="g-album-header-buttons">
    <?php echo  $theme->dynamic_top() ?>
  </div>
  <h1><?php echo  html::purify($title) ?></h1>
</div>

<ul id="g-album-grid" class="ui-helper-clearfix">
  <?php foreach ($children as $i => $child): ?>
  <li class="g-item <?php echo  $child->is_album() ? "g-album" : "" ?>">
    <?php echo  $theme->thumb_top($child) ?>
    <a href="<?php echo  $child->url() ?>">
      <img id="g-photo-id-<?php echo  $child->id ?>" class="g-thumbnail"
           alt="photo" src="<?php echo  $child->thumb_url() ?>"
           width="<?php echo  $child->thumb_width ?>"
           height="<?php echo  $child->thumb_height ?>" />
    </a>
    <h2><?php echo  html::purify($child->title) ?></h2>
    <?php echo  $theme->thumb_bottom($child) ?>
    <ul class="g-metadata">
      <?php echo  $theme->thumb_info($child) ?>
    </ul>
  </li>
  <?php endforeach ?>
</ul>
<?php echo  $theme->dynamic_bottom() ?>

<?php echo  $theme->paginator() ?>
