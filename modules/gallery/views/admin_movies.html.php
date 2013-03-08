<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="g-movies-admin" class="g-block ui-helper-clearfix">
  <h1> <?= t("Movies settings") ?> </h1>
  <p>
    <?= t("Gallery comes with everything it needs to upload and play movies.") ?>
    <?= t("However, it needs the FFmpeg toolkit to extract thumbnails and size information from them.") ?>
  </p>
  <p>
    <?= t("Although popular, FFmpeg is not installed on all Linux systems.") ?>
    <?= t("To use FFmpeg without fully installing it, download a pre-compiled, <b>static build</b> of FFmpeg from one of the links <a href=\"%url\">here</a>.", array("url" => "http://ffmpeg.org/download.html")) ?>
    <?= t("Then, put the \"ffmpeg\" file in Gallery's \"bin\" directory (e.g. \"/gallery3/bin\"), where Gallery will auto-detect it.") ?>
  </p>
  <p>
    <?= t("Movies will work without FFmpeg, but their thumbnails will be placeholders.") ?>
  </p>
  <p>
    <?= t("Can't get FFmpeg configured on your system? <a href=\"%url\">We can help!</a>",
          array("url" => "http://codex.galleryproject.org/Gallery3:FAQ#Why_does_it_say_I.27m_missing_ffmpeg.3F")) ?>
  </p>

  <div class="g-available">
    <h2> <?= t("Current configuration") ?> </h2>
    <div id="g-ffmpeg" class="g-block">
      <img class="logo" width="284" height="70" src="<?= url::file("modules/gallery/images/ffmpeg.png") ?>" alt="<? t("Visit the FFmpeg project site") ?>" />
      <p>
        <?= t("FFmpeg is a cross-platform standalone audio/video program.") ?><br/>
        <?= t("Please refer to the <a href=\"%url\">FFmpeg website</a> for more information.", array("url" => "http://ffmpeg.org")) ?>
      </p>
      <div class="g-module-status g-info">
        <? if ($ffmpeg_dir): ?>
          <? if ($ffmpeg_version): ?>
            <p><?= t("FFmpeg version %version was found in %dir", array("version" => $ffmpeg_version, "dir" => $ffmpeg_dir)) ?></p>
          <? else: ?>
            <p><?= t("FFmpeg (of unknown version) was found in %dir", array("dir" => $ffmpeg_dir)) ?></p>
          <? endif ?>
        <? else: ?>
          <p><?= t("We could not locate FFmpeg on your system.") ?></p>
        <? endif ?>
      </div>
    </div>
  </div>

  <?= $form ?>
</div>
