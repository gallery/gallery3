<? defined("SYSPATH") or die("No direct script access."); ?>
<script type="text/javascript">
  // <![CDATA[
function show_comment_add_form(url) {
  $("#gCommentAddLink").hide();
  $.get(url, function(data) {
    $("#gAddCommentFormContainer").html(data);
    ajaxify_comment_add_form();
  });
}

function ajaxify_comment_add_form() {
  $("#gLoginMenu form ul").addClass("gInline");
  $("form#gComment").ajaxForm({
    target: "#gAddCommentFormContainer",
    success: function(responseText, statusText) {
      if (!responseText) {
        reload_comments();
        $("#gCommentAddLink").show();
      } else {
        ajaxify_comment_add_form();
      }
    },
  });
}

function reload_comments() {
  $.get("<?= url::site("photo/{$item_id}/comments") ?>", function(data) {
    $("#gCommentThread").html(data);
  });
}
  // ]]>
</script>
<span id="gCommentAddLink">
  <a href="javascript:show_comment_add_form('<?= url::site("photo/{$item_id}/comments/add") ?>')">
    <?= _("Add Comment") ?>
  </a>
</span>
<div id="gAddCommentFormContainer"></div>

