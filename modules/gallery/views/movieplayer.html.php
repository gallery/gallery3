<?php defined("SYSPATH") or die("No direct script access.") ?>
<div <?= html::attributes($div_attrs) ?>>
  <video <?= html::attributes($video_attrs) ?>>
    <source <?= html::attributes($source_attrs) ?>>
  </video>
</div>
<script type="text/javascript">
  $("#<?= $div_attrs["id"] ?> video").mediaelementplayer(
    $.extend(true, {
      defaultVideoWidth: <?= $width ?>,
      defaultVideoHeight: <?= $height ?>,
      startVolume: 1.0,
      features: ["playpause", "progress", "current", "duration", "volume", "fullscreen"],
      pluginPath: "<?= url::abs_file("lib/mediaelementjs/") ?>",
      flashName: "flashmediaelement.swf.php"
    }, <?= json_encode($player_options) ?>)
  );
</script>
