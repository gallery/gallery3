<?php defined("SYSPATH") or die("No direct script access.") ?>
<script>
$("#package").ready(function() {
  ajaxify_package_form();
});

function ajaxify_package_form() {
  $("#package form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.result == "success") {
        $("#package .success").html(data.message);
        $("#package .success").removeClass("gHide");
        $("#package .error").addClass("gHide");
      } else {
        $("#package .error").html(data.message);
        $("#package .error").removeClass("gHide");
        $("#package .success").addClass("gHide");
      }
    }
  });
};

</script>
<p>Press the button to package this the modules as an installation package.</p>
<form action="<?= url::site("welcome/package") ?>" method="POST">
 <table style="width: 400px">
   <tr>
     <th align="left">Include</th>
     <th align="left">Module</th>
   </tr>
   <? foreach ($installed as $module_name => $required): ?>
   <tr>
     <td>
       <input type="checkbox" name="include[]" value="<?= $module_name ?>" checked
         <? if (!empty($required)): ?> disabled="disabled"<? endif ?>
       />
     </td>
     <td><?= $module_name ?></td>
   </tr>
   <? endforeach ?>
   <tr>
     <td colspan="2" align="center">
       <input type="Submit" value="Package" />
      </td>
   </tr>
 </table>
 <div id="SuccessMsg" class="success gHide"></div>
 <div id="FailMsg" class="error gHide"></div>
</form>
