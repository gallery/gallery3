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
  if ($("#g-edit-tag-form").length) {
    $("#g-edit-error-message").remove();
    var li = $("#g-edit-tag-form").parent();
    $("#g-edit-tag-form").parent().html($("#g-edit-tag-form").parent().data("revert"));
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
  var tag_id = $(this).attr('id').substr(5);
  var tag_name = $(this).html();
  var tag_width = $(this).width();
  $(this).parent().data("revert", $(this).parent().html());
  var form = '<form id="g-edit-tag-form" method="post" class="g-inline ui-helper-clearfix" ';
  form += 'action="' + TAG_RENAME_URL.replace('__ID__', tag_id) + '">';
  form += '<input name="csrf" type="hidden" value="' + csrf_token + '" />';
  form += '<input id="name" name="name" type="text" class="textbox" value="' +
          str_replace('"', "&quot;", tag_name) + '" />';
  form += '<input type="submit" class="submit ui-state-default ui-corner-all" value="' + save_i18n + '" i/>';
  form += '<a href="#">' + cancel_i18n + '</a>';
  form += '</form>';

  // add edit form
  $(this).parent().html(form);
  $("#g-edit-tag-form #name")
    .width(tag_width+30)
    .focus();
  //$("#g-edit-tag-form").parent().height( $("#g-edit-tag-form").height() );
  $("#g-edit-tag-form a").bind("click", closeEditInPlaceForms);

  ajaxify_editInPlaceForm = function() {
    $("#g-edit-tag-form").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.result == "success") {
          closeEditInPlaceForms(); // close form
          $("#g-tag-" + data.tag_id).text(data.new_tagname); // update tagname
          console.log(data);
          window.location.reload();
        } else if (data.result == "error") {
          $("#g-edit-tag-form #name")
            .addClass("g-error")
            .focus();
          $("#g-tag-admin").before("<p id=\"g-edit-error-message\" class=\"g-error\">" + data.message + "</p>");
        }
      }
    });
  };
  ajaxify_editInPlaceForm();
}

