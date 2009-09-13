<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($album->children(25, $offset) as $child): ?>
<li class="gOrganizeMicroThumbGridCell" ref="<?= $child->id ?>">
  <div id="gOrganizeMicroThumb_<?= $child->id ?>"
       class="gOrganizeMicroThumb <?= $child->is_album() ? "gAlbum" : "gPhoto" ?>">
    <?= $child->thumb_img(array("class" => "gThumbnail", "ref" => $child->id), 90, true) ?>
  </div>
</li>
<? endforeach ?>

<? if ($album->children_count() > $offset): ?>
<script>
  setTimeout(function() {
    $.get("<?= url::site("organize/album/$album->id/" . ($offset + 25)) ?>",
          {},
          function(data) {
            $("#gOrganizeMicroThumbGrid").append(data.grid);
            $.organize.set_handlers();
          },
          "json");
  }, 50);
</script>
<? endif ?>
