<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($item->children(25, $offset) as $child): ?>
<li id="gMicroThumb_<?= $child->id ?>"
    class="gMicroThumb <?= $child->is_album() ? "gAlbum" : "gPhoto" ?>"
    ref="<?= $child->id ?>">
  <?= $child->thumb_img(array("class" => "gThumbnail", "ref" => $child->id), 90, true) ?>
</li>
<? endforeach ?>

<? if ($item->children_count() > $offset): ?>
<script>
  setTimeout(function() {
    $.get("<?= url::site("organize/content/$item->id/" . ($offset + 25)) ?>",
      function(data) {
        $("#gMicroThumbGrid").append(data);
      }
    );
  }, 50);
</script>
<? endif ?>
