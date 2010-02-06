<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($album->children(25, $offset) as $child): ?>
<div class="g-organize-microthumb-grid-cell g-left ui-state-default ui-state-active <?= $child->is_album() ? "g-album" : "g-photo" ?>"
    ref="<?= $child->id ?>">
  <?= $child->thumb_img(array("class" => "g-thumbnail", "ref" => $child->id), 90, true) ?>
  <span<?= $child->is_album() ? " class=\"ui-icon ui-icon-note\"" : "" ?>></span>
</div>
<? endforeach ?>

<? if ($album->children_count() > $offset): ?>
<script type="text/javascript">
  setTimeout(function() {
    $.get("<?= url::site("organize/album/$album->id/" . ($offset + 25)) ?>",
          {},
          function(data) {
            $("#g-organize-microthumb-grid").append(data.grid);
            $.organize.set_handlers();
          },
          "json");
  }, 50);
</script>
<? endif ?>
