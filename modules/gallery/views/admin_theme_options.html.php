<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $("#g-theme-options-form").ready(function() {
     var contents = $("#g-theme-options-form fieldset:not(:last-child)");
     if (contents.length > 1) {
       $("<div id='g-theme-options-form-tabs'>" +
         "  <ul class='tabnav'></ul>" +
         "</div>").insertBefore("#g-theme-options-form fieldset:last-child");
       $(contents).each(function(index) {
         var text = $("legend", this).text();
         var tabId = "tab_" + index;
         var tabContentId = "tab_content_" + index;
         if (text == "") {
           text = <?= t("Tab_")->for_js() ?> + index;
         }
         $(".tabnav").append(
           "<li><a id='" + tabId + "' href='#" + tabContentId + "'>" + text + "</a></li>");
         $("#g-theme-options-form-tabs").append(
           "<div id='" + tabContentId + "' class='tabdiv'></div>");
         if ($("li.g-error", this).length > 0) {
           $("#" + tabId).addClass("g-error");
         }
         $("#" + tabContentId).append($("ul", this));
         $(this).remove();
       });
       $("#g-theme-options-form-tabs").tabs({});
     } else {
       $("#g-theme-options-form fieldset:first legend").hide();
     }
  });
</script>

<div class="g-block">
  <h1> <?= t("Theme options") ?> </h1>
  <div class="g-block-content">
  <?= $form ?>
  </div>
</div>
