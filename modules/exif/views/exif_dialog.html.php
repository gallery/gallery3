<?php defined("SYSPATH") or die("No direct script access.") ?>
<h1 style="display: none;"><?= t("Photo Detail") ?></h1>
<div id="gExifData">
    <table>
      <tbody>
        <? for ($i = 0; $i < count($details); $i++): ?>
          <tr>
             <td class="gEven">
               <?= $details[$i]["caption"] ?>
             </td>
             <td class="gOdd">
               <?= $details[$i]["value"] ?>
             </td>
             <? if (!empty($details[++$i])): ?>
               <td class="gEven">
                 <?= $details[$i]["caption"] ?>
               </td>
               <td class="gOdd">
                 <?= $details[$i]["value"] ?>
               </td>
             <? else: ?>
               <td class="gEven"></td><td class="gOdd"></td>
             <? endif ?>
             </td>
          </tr>
        <? endfor ?>
      </tbody>
    </table>
</div>
