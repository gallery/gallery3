<?php defined("SYSPATH") or die("No direct script access.") ?>
<div id="gOrganizeForm" class="yui-u">
  <?= form::open(url::site("organize/update/__ITEM_ID__?csrf=__CSRF__&action=__ACTION__"), array("method" => "post")) ?>
  
  <!-- div id="gOrganizeFormInfo" style="display:none"
       ref="<?= url::site("organize/detail/__ITEM_ID__") ?>">
    <ul>
        <li>
          Title: <span id="gOrganizeFormTitle"></span>
        </li>
        <li>
          Owner: <span id="gOrganizeFormOwner"></span>
         </li>
        <li>
          Date: <span id="gOrganizeFormDate"></span>
        </li>
        <li>
          Description: <span id="gOrganizeFormDescription">&nbsp;</span>
        </li>
    </ul>
  </div -->
  
  <span id="gOrganizeFormButtons">
    <?= form::submit(array("id" => "gOrganizeApplyButton", "name" => "apply", "disabled" => true, "class" => "submit"), t("Apply")) ?>
  </span>
  <?= form::close() ?>
</div>