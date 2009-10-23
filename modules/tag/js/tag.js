$("document").ready(function() {
  ajaxify_tag_form();
});

function ajaxify_tag_form() {
  $("#g-tag form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.result == "success") {
        $.get($("#g-tag-cloud").attr("title"), function(data, textStatus) {
          $("#g-tag-cloud").html(data);
        });
      }
      $("#g-tag form").resetForm();
    }
  });
}

function closeEditInPlaceForms() {
  // closes currently open inplace edit forms
  if ($("#g-rename-tag-form").length) {
    $("#g-action-status").remove();
    var li = $("#g-rename-tag-form").parent();
    $("#g-rename-tag-form").parent().html($("#g-rename-tag-form").parent().data("revert"));
    li.height("");
    $(".g-editable", li).bind("click", editInPlace);
    $(".g-dialog-link", li).gallery_dialog();
  }
}

function str_replace(search_term, replacement, string) {
  var temp = string.split(search_term);
  return temp.join(replacement);
}

function editInPlace(element) {
  closeEditInPlaceForms();

  // create edit form
  var tag_id = $(this).attr('rel');
  var tag_name = $(this).html();
  var tag_width = $(this).width();
  $(this).parent().data("revert", $(this).parent().html());
  var form = '<form id="g-rename-tag-form" method="post" class="g-short-form" ';
  form += 'action="' + TAG_RENAME_URL.replace('__ID__', tag_id) + '">';
  form += '<input name="csrf" type="hidden" value="' + csrf_token + '" />';
  form += '<ul>';
  form += '<li><input id="name" name="name" type="text" class="textbox" value="' +
          str_replace('"', "&quot;", tag_name) + '" /></li>';
  form += '<li><input type="submit" class="submit ui-state-default" value="' + save_i18n + '" /></li>';
  form += '<li><a href="#" class="g-cancel">' + cancel_i18n + '</a></li>';
  form += '</ul>';
  form += '</form>';

  // add edit form
  $(this).parent().html(form);
  $("#g-rename-tag-form #name")
    .width(tag_width)
    .focus();
  $(".g-short-form").gallery_short_form();
  $("#g-rename-tag-form .g-cancel").bind("click", closeEditInPlaceForms);

  ajaxify_editInPlaceForm = function() {
    $("#g-rename-tag-form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.result == "success") {
          closeEditInPlaceForms(); // close form
          $(".g-tag[rel=" + data.tag_id + "]").text(data.new_tagname); // update tagname
          window.location.reload();
        } else if (data.result == "error") {
          $("#g-rename-tag-form #name")
            .addClass("g-error")
            .focus();
          var message = "<ul id=\"g-action-status\" class=\"g-message-block\">";
          message += "<li class=\"g-error\">" + data.message + "</li>";
          message += "</ul>";
          $("#g-tag-admin").before(message);
        }
      }
    });
  };
  ajaxify_editInPlaceForm();
}
