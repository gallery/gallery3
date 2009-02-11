<?php defined("SYSPATH") or die("No direct script access.") ?>
<style>
/* Tabs ----------------------------------*/
.ui-tabs {padding: .2em;}
.ui-tabs .ui-tabs-nav { padding: .2em .2em 0 .2em;  position: relative; }
.ui-tabs .ui-tabs-nav li { float: left; border-bottom: 0 !important; margin: 0 .2em -1px 0; padding: 0; list-style: none; }
.ui-tabs .ui-tabs-nav li a { display:block; text-decoration: none; padding: .5em 1em; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected {  padding-bottom: .1em; border-bottom: 0; }
.ui-tabs .ui-tabs-panel { padding: 1em 1.4em;  display: block; border: 0; background: none; }
.ui-tabs .ui-tabs-hide { display: none !important; }
</style>
<script type="/text/javascript">
  $("#gExifData").ready(function() {
    $('#gExifData').tabs();
  });
</script>
<h1 style="display: none;"><?= t("Photo Detail") ?></h1>
<div id="gExifData" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
  <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a href="#gExifSummary"><?= t("Summary")?></a></li>
    <li class="ui-state-default ui-corner-top"><a href="#gExifDetail"><?= t("Detail")?></a></li>
  </ul>
  <div id="gExifSummary" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    <table>
      <tbody>
        <? for ($i = 0; $i < count($details["summary"]); $i++): ?>
          <tr>
             <td class="gEven">
               <?= $details["summary"][$i]["caption"] ?>
             </td>
             <td class="gOdd">
               <?= $details["summary"][$i]["value"] ?>
             </td>
             <? if (!empty($details["summary"][++$i])): ?>
               <td class="gEven">
                 <?= $details["summary"][$i]["caption"] ?>
               </td>
               <td class="gOdd">
                 <?= $details["summary"][$i]["value"] ?>
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
  <div id="gExifDetail" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide">
    <table>
      <tbody>
        <? for ($i = 0; $i < count($details["detail"]); $i++): ?>
          <tr>
             <td class="gEven">
               <?= $details["detail"][$i]["caption"] ?>
             </td>
             <td class="gOdd">
               <?= $details["detail"][$i]["value"] ?>
             </td>
             <? if (!empty($details["detail"][++$i])): ?>
               <td class="gEven">
                 <?= $details["detail"][$i]["caption"] ?>
               </td>
               <td class="gOdd">
                 <?= $details["detail"][$i]["value"] ?>
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
</div>