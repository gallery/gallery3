/**
 * Set up autocomplete on the server path list
 *
 */
$("document").ready(function() {
  add_autocomplete();
  ajaxify_import_form();
  add_onclick();
});

function add_autocomplete() {
  $("#gLocalImportAdmin input:text").autocomplete(base_url + "admin/local_import/autocomplete", {
    extraParams: {csrf: csrf},
    mustMatch: true,
    max: 256});
}
function ajaxify_import_form(options) {
  $("#gLocalImportAdmin form").ajaxForm({
			     dataType: "json",
			     success: function(data) {
			       if (data.form) {
				 $("#gLocalImportAdmin form").replaceWith(data.form);
				 ajaxify_import_form();
				 add_autocomplete();
			       }
			       if (data.result == "success") {
      $("#gNoImportPaths").css("display", "none");
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
      base_url + "admin/local_import/remove",
      {csrf: csrf,
       path: parent.text().replace(/^\s\s*/, "").replace(/\s\s*$/, "")},
      function(data, textStatus) {
	$("#gAuthorizedPath").html(data);
        add_onclick();
      });
  });
}
