<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function() {
    $("#g-profile-return").click(function(event) {
      history.go(-1);
      return false;
    })
  });
</script>
<div id="g-user-profile">
  <div class="ui-helper-clearfix">
    <a id="g-profile-return" class="g-button g-right ui-state-default ui-corner-all" href="#">
      <?php echo t("Return") ?>
    </a>
    <?php if ($editable): ?>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link" href="<?php echo url::site("users/form_change_email/{$user->id}") ?>">
      <?php echo t("Change email") ?>
    </a>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link" href="<?php echo url::site("users/form_change_password/{$user->id}") ?>">
      <?php echo t("Change password") ?>
    </a>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link" href="<?php echo url::site("form/edit/users/{$user->id}") ?>">
      <?php echo t("Edit") ?>
    </a>
    <?php endif ?>
    <?php if ($contactable): ?>
    <a class="g-button g-right ui-state-default ui-corner-all g-dialog-link"
       href="<?php echo url::site("user_profile/contact/{$user->id}") ?>">
      <?php echo t("Contact") ?>
    </a>
    <?php endif ?>
  </div>
  <h1>
    <img src="<?php echo $user->avatar_url(40, $theme->url("images/avatar.jpg", true)) ?>"
       alt="<?php echo html::clean_attribute($user->display_name()) ?>"
       class="g-avatar g-left" width="40" height="40" />
    <?php echo t("User profile: %name", array("name" => $user->display_name())) ?>
  </h1>
  <?php foreach ($info_parts as $info): ?>
  <div class="g-block">
    <h2><?php echo html::purify($info->title) ?></h2>
    <div class="g-block-content">
    <?php echo $info->view ?>
    </div>
  </div>
  <?php endforeach ?>
</div>
