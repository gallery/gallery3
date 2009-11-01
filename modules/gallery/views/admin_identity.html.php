<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  $(document).ready(function() {
    $("#g-modules form").submit(function() {
      var eDialog = '<div id="g-dialog"></div>';
      var params = $(this).serialize();
      var url = $(this).attr("action");
      $("body").append(eDialog);
      $.post($(this).attr("action"), $(this).serialize(), function(data, textStatus) {
        $("#g-dialog").html(data);
        $("#g-dialog").dialog({
          bgiframe: true,
          title: <?= t("Confirm identity provider change")->for_js() ?>,
          resizable: false,
          height:180,
          modal: true,
          overlay: {
            backgroundColor: '#000',
            opacity: 0.5
          },
          buttons: {
            "Continue": function() {
              $("#g-dialog form").submit();
            },
            Cancel: function() {
              $(this).dialog('destroy').remove();
            }
          }
        });
      });
      return false;
    });
  });

</script>
<div id="g-modules">
  <h1> <?= t("Manage identity providers") ?> </h1>
  <p>
    <?= t("Choose a different user/group management provider.") ?>
  </p>

  <form method="post" action="<?= url::site("admin/identity/confirm") ?>">
    <?= access::csrf_form_field() ?>
    <table>
      <tr>
        <th> <?= t("Active") ?> </th>
        <th> <?= t("Description") ?> </th>
      </tr>
      <? $i = 0 ?>
      <? foreach ($available as $module_name => $description):  ?>
      <tr class="<?= ($i % 2 == 0) ? "g-odd" : "g-even" ?>">
        <? $data = array("name" => "provider"); ?>
        <td> <?= form::radio($data, $module_name, $module_name == $active) ?> </td>
        <td> <?= t($description) ?> </td>
      </tr>
      <? $i++ ?>
      <? endforeach ?>
    </table>
    <input type="submit" value="<?= t("Change")->for_html_attr() ?>" />
  </form>
</div>
