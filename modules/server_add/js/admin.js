/**
 * Set up autocomplete on the server path list
 *
 */
$("document").ready(function() {
  add_autocomplete();
  ajaxify_add_form();
  add_onclick();
});

function add_autocomplete() {
  $("#gServerAddAdmin input:text").autocomplete(
    base_url.replace("__ARGS__", "admin/server_add/autocomplete"), {max: 256});
}
function ajaxify_add_form(options) {
  $("#gServerAddAdmin form").ajaxForm({
    dataType: "json",
    success: function(data) {
      if (data.form) {
	$("#gServerAddAdmin form").replaceWith(data.form);
	ajaxify_add_form();
	add_autocomplete();
      }
      if (data.result == "success") {
	$("#gNoAuthorizedPaths").css("display", "none");
	$("#gAuthorizedPath").html(data.paths);
	add_onclick();
      }
    }
  });
}

function add_onclick() {
  $(".gRemoveDir").click(function() {
    var parent = $(this).parent();
    $.post(
      base_url.replace("__ARGS__", "admin/local_import/remove_path"),
      {csrf: csrf,
       path: parent.text().replace(/^\s\s*/, "").replace(/\s\s*$/, "")},
      function(data, textStatus) {
	$("#gAuthorizedPath").html(data);
        add_onclick();
      });
  });
}
