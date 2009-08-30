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
    $.get("<?= url::site("organize/content/$album->id/" . ($offset + 25)) ?>",
      function(data) {
        $("#gOrganizeMicroThumbGrid").append(data);
        $.organize.set_handlers();
      }
    );
  }, 50);
</script>
<? endif ?>
