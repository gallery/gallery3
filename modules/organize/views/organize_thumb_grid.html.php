<?php defined("SYSPATH") or die("No direct script access.") ?>
<? foreach ($children as $i => $child): ?>
  <? $item_class = "gPhoto"; ?>
  <? if ($child->is_album()): ?>
    <? $item_class = "gAlbum"; ?>
  <? endif ?>
  <li id="gMicroThumb_<?= $child->id ?>" class="gMicroThumb  <?= $item_class ?>" ref="<?= $child->id ?>">
    <?= $child->thumb_img(array("class" => "gThumbnail"), $thumbsize, true) ?>
  </li>
<? endforeach ?>
<? if (count($children) >= 25): ?>
<script>
   $.get("<?= url::site("organize/content/{$item_id}/$offset") ?>",
     function(data) {
       $("#gMicroThumbGrid").append(data);
     }
   );
</script>
<? endif ?>