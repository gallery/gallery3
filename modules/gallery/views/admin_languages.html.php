<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gLanguages">
  <h1> <?= t("Languages") ?> </h1>
  <p>
    <?= t("Here you can install new languages, update installed ones and set the default language for your Gallery.") ?>
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

  <h2> <?= t("Your Own Translations") ?> </h2>
  <?= $share_translations_form ?>
</div>
