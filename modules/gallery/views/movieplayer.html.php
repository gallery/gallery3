<?php defined("SYSPATH") or die("No direct script access.") ?>
<div <?php echo  html::attributes($div_attrs) ?>>
  <video <?php echo  html::attributes($video_attrs) ?>>
    <source <?php echo  html::attributes($source_attrs) ?>>
  </video>
</div>
<script type="text/javascript">
  $("#<?php echo  $div_attrs["id"] ?> video").mediaelementplayer(
    $.extend(true, {
      defaultVideoWidth: <?php echo  $width ?>,
      defaultVideoHeight: <?php echo  $height ?>,
      startVolume: 1.0,
      features: ["playpause", "progress", "current", "duration", "volume", "fullscreen"],
      pluginPath: "<?php echo  url::abs_file("lib/mediaelementjs/") ?>",
      flashName: "flashmediaelement.swf.php"
    }, <?php echo  json_encode($player_options) ?>)
  );
</script>
