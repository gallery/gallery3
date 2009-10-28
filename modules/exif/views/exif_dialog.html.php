<?php defined("SYSPATH") or die("No direct script access.") ?>
<style type="text/css">
  #g-exif-data { font-size: .85em; }
  .g-odd { background: #bdd2ff; }
  .g-even { background: #dfeffc; }
</style>
<h1 style="display: none;"><?= t("Photo detail") ?></h1>
<div id="g-exif-data">
  <table class="g-metadata" >
    <tbody>
      <? for ($i = 0; $i < count($details); $i++): ?>
      <tr>
         <td class="g-even">
         <?= $details[$i]["caption"] ?>
         </td>
         <td class="g-odd">
         <?= html::clean($details[$i]["value"]) ?>
         </td>
         <? if (!empty($details[++$i])): ?>
           <td class="g-even">
           <?= $details[$i]["caption"] ?>
           </td>
           <td class="g-odd">
           <?= html::clean($details[$i]["value"]) ?>
           </td>
         <? else: ?>
           <td class="g-even"></td><td class="g-odd"></td>
         <? endif ?>
       </tr>
       <? endfor ?>
    </tbody>
  </table>
</div>
