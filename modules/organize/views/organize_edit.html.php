<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gOrganizeForm">
  <?= form::open(url::site("organize/update/__ITEM_ID__?csrf=__CSRF__&action=__ACTION__"), array("method" => "post")) ?>
  <div id="gOrganizeFormThumbs">
    <div id="gOrganizeFormNoImage">
      <h3 style="text-align:center"><?= t("No Image Selected") ?></h3>
    </div>
    <div id="gOrganizeFormThumb" style="display: none"></div>
    <div id="gOrganizeFormMultipleImages" style="display:none">
      <h3 style="text-align:center"><?= t("Multiple Images Selected") ?></h3>
    </div>
  </div>

  <div id="gOrganizeButtonPane" style="display: none">
    <?= $button_pane ?>
  </div>
  
  <div id="gOrganizeFormInfo" style="display:none"
       ref="<?= url::site("organize/detail/__ITEM_ID__") ?>">
    <table style="padding: 0;">
      <tbody>
        <tr>
          <td>Title:</td><td><span id="gOrganizeFormTitle"></span></td>
        </tr>
        <tr>
          <td>Owner:</td><td><span id="gOrganizeFormOwner"></span></td>
         </tr>
        <tr>
         <td>Date:</td><td><span id="gOrganizeFormDate"></span></td>
        </tr>
        <tr>
          <td colspan="2">Description:</td>
        </tr>
        <tr>
          <td colspan="2"><span id="gOrganizeFormDescription">&nbsp;</span>
        </tr>
      </tbody>
    </table>
  </div>
  
  <span id="gOrganizeFormButtons">
    <?= form::submit(array("id" => "gOrganizePauseButton", "name" => "pause", "disabled" => true, "class" => "submit", "style" => "display:none"), t("Pause")) ?>
    <?= form::submit(array("id" => "gOrganizeApplyButton", "name" => "apply", "disabled" => true, "class" => "submit"), t("Apply")) ?>
  </span>
  <?= form::close() ?>
</div>