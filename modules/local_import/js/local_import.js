function open_close_branch(icon, event) {
  var parent = icon.parentNode;
  //var label = $(parent).find("label");
  var children = $(parent).find(".gCheckboxTree");
  var closed = $(icon).hasClass("ui-icon-plus");

  if (closed) {
    if (children.length == 0) {
      load_children( $(icon).attr("ref"), function(data, textStatus) {
	$(parent).append(data);
	$(icon).addClass("ui-icon-minus");
	$(icon).removeClass("ui-icon-plus");
	var checkbox = $(parent).find(":checkbox")[0];
	checkbox_click(checkbox, null);
      });
    } else {
      $(icon).addClass("ui-icon-minus");
      $(icon).removeClass("ui-icon-plus");
      $(parent).children("ul").slideDown("fast");
    }
  } else {
    $(icon).addClass("ui-icon-plus");
    $(icon).removeClass("ui-icon-minus");
    $(parent).children("ul").slideUp("fast");
  }
}

function checkbox_click(checkbox, event) {
  var parents = $(checkbox).parents("li");
  var parent = parents.get(0);
  $(parent).find(".gCheckboxTree :checkbox").attr("checked", checkbox.checked);
  var checked = $("#gLocalImport .gFile :checkbox[checked]");
  $("#gLocalImport form :submit").attr("disabled", checked.length == 0);
}

function load_children(path, callback) {
  var csrf = $("#gLocalImport form :hidden[name='csrf']")[0].value;
  var base_url = $("#gLocalImport form :hidden[name='base_url']")[0].value;
  $.post(base_url + "local_import/children",
    {csrf: csrf, path: path}, callback);
}

function do_import(submit, event) {
  event.preventDefault();
  $("#gProgressBar").progressbar('value', 0);
  $("#gProgressBar").css("visibility", "visible");
  var check_list = $("#gLocalImport .gFile :checkbox[checked]");
  var current = 0;
  var csrf = $("#gLocalImport form :hidden[name='csrf']")[0].value;
  var url = $("#gLocalImport form").attr("action");
  $.each(check_list, function () {
    var path = $(this).val();
    $.post(url, {csrf: csrf, path: path}, function(data, status) {
    });
    current++;
    $("#gProgressBar").progressbar('value', current / check_list.length * 100);
  });
  document.location.reload();
  return false;
}
