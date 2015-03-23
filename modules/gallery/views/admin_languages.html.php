<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var old_default_locale = <?php echo html::js_string($default_locale) ?>;

  $("#g-languages-form").ready(function() {
    $("input[name='installed_locales[]']").change(function (event) {
      if (this.checked) {
        $("input[type='radio'][value='" + this.value + "']").enable();
      } else {
        if ($("input[type='radio'][value='" + this.value + "']").selected()) { // if you deselect your default language, switch to some other installed language
          $("input[type='radio'][value='" + old_default_locale + "']").attr("checked", "checked");
        }
        $("input[type='radio'][value='" + this.value + "']").attr("disabled", "disabled");
      }
    });

    $("#g-languages-form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.result == "success") {
          el = $('<a href="' + <?php echo html::js_string(url::site("admin/maintenance/start/gallery_task::update_l10n?csrf=$csrf")) ?> + '"></a>'); // this is a little hack to trigger the update_l10n task in a dialog
          el.gallery_dialog();
          el.trigger('click');
        }
      }
    });
  });
</script>

<div class="g-block">
  <h1> <?php echo t("Languages and translation") ?> </h1>

  <div class="g-block-content">

    <div id="g-languages" class="g-block">
      <h2> <?php echo t("Languages") ?> </h2>
      <p>
        <?php echo t("Install new languages, update installed ones and set the default language for your Gallery.") ?>
      </p>

      <div class="g-block-content ui-helper-clearfix">
        <form id="g-languages-form" method="post" action="<?php echo url::site("admin/languages/save") ?>">
          <?php echo access::csrf_form_field() ?>
          <table class="g-left">
            <tr>
              <th> <?php echo t("Installed") ?> </th>
              <th> <?php echo t("Language") ?> </th>
              <th> <?php echo t("Default language") ?> </th>
            </tr>
            <?php $i = 0 ?>
            <?php foreach ($available_locales as $code => $display_name):  ?>
            <?php if ($i == (int) (count($available_locales)/2)): ?>
          </table>
          <table class="g-left">
            <tr>
              <th> <?php echo t("Installed") ?> </th>
              <th> <?php echo t("Language") ?> </th>
              <th> <?php echo t("Default language") ?> </th>
            </tr>
            <?php endif ?>
            <tr class="<?php echo (isset($installed_locales[$code])) ? "g-available" : "" ?><?php echo ($default_locale == $code) ? " g-selected" : "" ?>">
              <td> <?php echo form::checkbox("installed_locales[]", $code, isset($installed_locales[$code])) ?> </td>
              <td> <?php echo $display_name ?> </td>
              <td>
              <?php echo form::radio("default_locale", $code, ($default_locale == $code), ((isset($installed_locales[$code]))?'':'disabled="disabled"') ) ?>
              </td>
            </tr>
            <?php $i++ ?>
            <?php endforeach ?>
          </table>
          <input type="submit" value="<?php echo t("Update languages")->for_html_attr() ?>" />
        </form>
      </div>
    </div>

    <div id="g-translations" class="g-block">
      <h2> <?php echo t("Translations") ?> </h2>
      <p>
        <?php echo t("Create your own translations and share them with the rest of the Gallery community.") ?>
      </p>

      <div class="g-block-content">
        <a href="http://codex.galleryproject.org/Gallery3:Localization" target="_blank"
            class="g-right ui-state-default ui-corner-all ui-icon ui-icon-help"
            title="<?php echo t("Localization documentation")->for_html_attr() ?>">
          <?php echo t("Localization documentation") ?>
        </a>

        <h3><?php echo t("Translating Gallery") ?></h3>

        <p><?php echo t("Follow these steps to begin translating Gallery.") ?></p>

        <ol>
          <li><?php echo t("Make sure the target language is installed and up to date (check above).") ?></li>
          <li><?php echo t("Make sure you have selected the right target language (currently %default_locale).",
               array("default_locale" => locales::display_name())) ?></li>
          <li><?php echo t("Start the translation mode and the translation interface will appear at the bottom of each Gallery page.") ?></li>
        </ol>
        <a href="<?php echo url::site("l10n_client/toggle_l10n_mode?csrf=".access::csrf_token()) ?>"
           class="g-button ui-state-default ui-corner-all ui-icon-left">
          <span class="ui-icon ui-icon-power"></span>
          <?php if (Session::instance()->get("l10n_mode", false)): ?>
          <?php echo t("Stop translation mode") ?>
          <?php else: ?>
          <?php echo t("Start translation mode") ?>
          <?php endif ?>
        </a>

        <h3><?php echo t("Sharing your translations") ?></h3>
          <p>
            <?php echo t("Sharing your own translations with the Gallery community is easy. Please do!") ?>
          </p>
        <?php echo $share_translations_form ?>
      </div>
    </div>

  </div>
</div>
