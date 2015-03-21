<?php defined("SYSPATH") or die("No direct script access.") ?>
<p>
  <?php echo  t("Gallery can check to see if there is a new version available for you to use.  It is a good idea to upgrade your Gallery to get the latest features and security fixes.  Your privacy is important so no information about your Gallery is shared during this process.  You can disable this feature below.") ?>
</p>

<p>
  <?php if (gallery::RELEASE_CHANNEL == "release"): ?>
  <?php echo  t("You are using the official Gallery %version release, code named <i>%code_name</i>.", array("version" => gallery::VERSION, "code_name" => gallery::CODE_NAME)) ?>
  <?php elseif (isset($build_number)): ?>
  <?php echo  t("You are using an experimental snapshot of Gallery %version (build %build_number on branch %branch).", array("version" => gallery::VERSION, "branch" => gallery::RELEASE_BRANCH, "build_number" => $build_number)) ?>
  <?php else: ?>
  <?php echo  t("You are using an experimental snapshot of Gallery %version (branch %branch) but your gallery3/.build_number file is missing so we don't know what build you have.  You should probably upgrade so that you have that file.", array("version" => gallery::VERSION, "branch" => gallery::RELEASE_BRANCH, "build_number" => $build_number)) ?>
  <?php endif ?>
</p>

<?php if ($new_version): ?>
<ul class="g-message-block">
  <li class="g-message g-info">
    <?php echo  $new_version ?>
  </li>
</ul>
<?php endif ?>

<p>
  <a class="g-button ui-state-default ui-corner-all"
     href="<?php echo  url::site("admin/upgrade_checker/check_now?csrf=$csrf") ?>">
    <?php echo  t("Check now") ?>
  </a>
  <?php if ($auto_check_enabled): ?>
  <a class="g-button ui-state-default ui-corner-all"
     href="<?php echo  url::site("admin/upgrade_checker/set_auto/0?csrf=$csrf") ?>">
    <?php echo  t("Disable automatic checking") ?>
  </a>
  <?php else: ?>
  <a class="g-button ui-state-default ui-corner-all"
     href="<?php echo  url::site("admin/upgrade_checker/set_auto/1?csrf=$csrf") ?>">
    <?php echo  t("Enable automatic checking") ?>
  </a>
  <?php endif ?>
</p>

<p class="g-text-small">
  <?php if ($auto_check_enabled): ?>
  <?php echo  t("Automatic upgrade checking is enabled.") ?>
  <?php else: ?>
  <?php echo  t("Automatic upgrade checking is disabled.") ?>
  <?php endif ?>
  <?php if (!$version_info): ?>
  <?php echo  t("No upgrade checks have been made yet.") ?>
  <?php else: ?>
  <?php echo  t("The last upgrade check was made on %date.",
        array("date" => gallery::date_time($version_info->timestamp))) ?>
  <?php endif ?>
</p>

