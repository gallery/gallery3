$("document").ready(function() {
  ajaxify_tag_form();
});

function ajaxify_tag_form() {
  $("#gTag form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.result == "success") {
        $.get($("#gTagCloud").attr("title"), function(data, textStatus) {
	      $("#gTagCloud").html(data);
	    });
      }
      $("#gTag form").resetForm();
    }
  });
}

function closeEditInPlaceForms() {
  // closes currently open inplace edit forms
  if ($("#gRenameTagForm").length) {
    var li = $("#gRenameTagForm").parent();
    $("#gRenameTagForm").parent().html($("#gRenameTagForm").parent().data("revert"));
    li.height("");
    $(".gEditable", li).bind("click", editInPlace);
    $(".gDialogLink", li).gallery_dialog();
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
  var form = '<form id="gRenameTagForm" method="post" class="ui-helper-clearfix" ';
  form += 'action="' + TAG_RENAME_URL.replace('__ID__', tag_id) + '">';
  form += '<input name="csrf" type="hidden" value="' + csrf_token + '" />';
  form += '<input id="name" name="name" type="text" class="textbox" value="' +
          str_replace('"', "&quot;", tag_name) + '" />';
  form += '<input type="submit" class="submit ui-state-default ui-corner-all" value="' + save_i18n + '" i/>';
  form += '<a href="#">' + cancel_i18n + '</a>';
  form += '</form>';

  // add edit form
  $(this).parent().html(form);
  $("#gRenameTagForm #name")
    .width(tag_width+30)
    .focus();
  //$("#gRenameTagForm").parent().height( $("#gRenameTagForm").height() );
  $("#gRenameTagForm a").bind("click", closeEditInPlaceForms);

  ajaxify_editInPlaceForm = function() {
    $("#gRenameTagForm").ajaxForm({
      dataType: "json",
      success: function(data) {
        if (data.result == "success") {
          closeEditInPlaceForms(); // close form
          $("#gTag-" + data.tag_id).text(data.new_tagname); // update tagname
          console.log(data);
          window.location.reload();
        }
      }
    });
  };
  ajaxify_editInPlaceForm();
}

