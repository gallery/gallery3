<? defined("SYSPATH") or die("No direct script access."); ?>
<script type="text/javascript">
  // <![CDATA[
function show_comment_add_form(url) {
  $("#gCommentAddLink").hide();
  $.get(url, function(data) {
    $("#gCommentAddFormContainer").html(data);
    ajaxify_comment_add_form();
  });
}

function ajaxify_comment_add_form() {
  $("form#gComment").ajaxForm({
    dataType: 'json',
    success: function(response_data, status_text) {
      if (response_data['valid']) {
        $("#gCommentThread").html(response_data["html"]);
    $("#gCommentAddFormContainer").html("");
        $("#gCommentAddLink").show();
      } else {
        $("#gCommentAddFormContainer").html(response_data["html"]);
        ajaxify_comment_add_form();
      }
    },
  });
}
  // ]]>
</script>
<span id="gCommentAddLink">
  <a href="javascript:show_comment_add_form('<?= url::site("photo/{$item_id}/comments/add") ?>')">
    <?= _("Add Comment") ?>
  </a>
</span>
<div id="gCommentAddFormContainer"></div>

