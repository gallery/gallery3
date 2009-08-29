<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gLanguages">
  <h1> <?= t("Languages") ?> </h1>
  <p>
    <?= t("Install new languages, update installed ones and set the default language for your Gallery.") ?>
  </p>

  <form id="gLanguagesForm" method="post" action="<?= url::site("admin/languages/save") ?>">
    <?= access::csrf_form_field() ?>
    <table>
      <tr>
        <th> <?= t("Installed") ?> </th>
        <th> <?= t("Language") ?> </th>
				<th> <?= t("Default language") ?> </th>
      </tr>
      <? $i = 0 ?>
      <? foreach ($available_locales as $code => $display_name):  ?>
			
			<? if ($i == (count($available_locales)/2)): ?>
      <table>
        <tr>
          <th> <?= t("Installed") ?> </th>
          <th> <?= t("Language") ?> </th>
          <th> <?= t("Default language") ?> </th>
        </tr>
      <? endif ?>
			
      <tr class="<?= (isset($installed_locales[$code])) ? "installed" : "" ?><?= ($default_locale == $code) ? " default" : "" ?>">
        <td> <?= form::checkbox("installed_locales[]", $code, isset($installed_locales[$code])) ?> </td>
				<td> <?= $display_name ?> </td>
				<td>
					<?= form::radio("default_locale", $code, ($default_locale == $code), ((isset($installed_locales[$code]))?'':'disabled="disabled"') ) ?>
				</td>
      </tr>
      <? $i++ ?>
			
      <? endforeach ?>
    </table>
		<input type="submit" value="<?= t("Update languages") ?>" />
  </form>
	
	<script type="text/javascript">
    var old_default_locale = "<?= $default_locale ?>";
    
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
    
    $("#gLanguagesForm").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.result == "success") {
          el = $('<a href="<?= url::site("admin/maintenance/start/gallery_task::update_l10n?csrf=$csrf") ?>"></a>'); // this is a little hack to trigger the update_l10n task in a dialog
          el.gallery_dialog();
          el.trigger('click');
        }
      }
    });
  </script>
</div>

<div id="gTranslations">
  <h1> <?= t("Translations") ?> </h1>
	<p>
    <?= t("Create your own translations and share them with the rest of the Gallery community.") ?>
  </p>
	
	<h3><?= t("Translating Gallery") ?></h3>
	
	<div class="gBlock">
		<a href="http://codex.gallery2.org/Gallery3:Localization" target="_blank"
		  class="gDocLink ui-state-default ui-corner-all ui-icon ui-icon-help"
			title="<?= t("Localization documentation") ?>">
      <?= t("Localization documentation") ?>
    </a>
		
		<p><strong><?= t("Step 1") ?>:</strong> <?= t("Make sure the target language is installed and updated (check above).") ?></p>
		
		<p><strong><?= t("Step 2") ?>:</strong> <?= t("Make sure the target language is the active one (currently '").locales::display_name()."')." ?></p>
		
		<p><strong><?= t("Step 3") ?>:</strong> <?= t("Start the translation mode and the translation interface will appear at the bottom of each Gallery page.") ?></p>
		
		<a href="<?= url::site("l10n_client/toggle_l10n_mode?csrf=".access::csrf_token()) ?>"
		  class="gButtonLink ui-state-default ui-corner-all ui-icon-left">
		  <span class="ui-icon ui-icon-power"></span>
		  <?= t((Session::instance()->get("l10n_mode", false)) ? "Stop translation mode" : "Start translation mode") ?>
	  </a>
	</div>
	
	<h3>Sharing your translations</h3>
	
  <?= $share_translations_form ?>
</div>
