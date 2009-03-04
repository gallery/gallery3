$("#gLocalImport").ready(function() {
  $("#gLocalImport :submit").click(function(event) {
    do_import(this, event);
  });
  $("#gProgressBar").progressbar();
  $("#gLocalImport ul").css("display", "block");
});

function open_close_branch(icon, event) {
  var parent = icon.parentNode;
  var children = $(parent).find(".gCheckboxTree");
  var closed = $(icon).hasClass("ui-icon-plus");

  if (closed) {
    if (children.length == 0) {
      load_children(icon, function(data, textStatus) {
        $(parent).append(data);
        $(icon).addClass("ui-icon-minus");
        $(icon).removeClass("ui-icon-plus");
        var checkbox = $(parent).find(":checkbox")[0];
        checkbox_click(checkbox, null);
      });
    } else {
      $(icon).addClass("ui-icon-minus");
      $(icon).removeClass("ui-icon-plus");
    }
    $(parent).children("ul").slideDown("fast");
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
  var checked = $("#gLocalImport :checkbox[checked]");
  $("#gLocalImport form :submit").attr("disabled", checked.length == 0);
}

function load_children(icon, callback) {
  var csrf = $("#gLocalImport form :hidden[name='csrf']")[0].value;
  var base_url = $("#gLocalImport form :hidden[name='base_url']")[0].value;
  var parms = "&csrf=" + csrf;
  var parents = $(icon).parents("li");
  for (var i=parents.length - 1; i >= 0; i--) {
    parms += "&path[]=" +  $(parents[i]).children("span").attr("ref");
  }
  $.ajax({async: false,
          success: callback,
          data: parms,
          dataType: "html",
          type: "POST",
          url: base_url + "local_import/children"
  });
}

var current = 0;
var process_length = 0;
function do_import(submit, event) {
  event.preventDefault();
  $("#gProgressBar").progressbar("value", 0);
  $("#gProgressBar").css("visibility", "visible");
  var check_list = $("#gLocalImport :checkbox[checked]");
  process_length = check_list.length;
  current = 0;
  $.each(check_list, function () {
    process_checkbox(this);
  });
  var base_url = $("#gLocalImport form :hidden[name='base_url']")[0].value;
  $.ajax({async: false,
    success: function(data, textStatus) {
      document.location.reload();
    },
    dataType: "json",
    type: "POST",
    url: base_url + "local_import/finish"
  });
  return false;
}

function process_checkbox(checkbox) {
  var parents = $(checkbox).parents("li");
  var csrf = $("#gLocalImport form :hidden[name='csrf']")[0].value;
  var parms = "&csrf=" + csrf;
  for (var i=parents.length - 1; i > 0; i--) {
    parms += "&path[]=" +  $(parents[i]).children("span").attr("ref");
  }
  parms += "&path[]=" + $(checkbox).val();

  var parent = parents[0];
  if ($(parent).hasClass("gFile")) {
    process_file(parents[0], parms);
  } else if ($(parent).hasClass("gDirectory") && $(parents[0]).find(".gCheckboxTree").length == 0) {
    // If it is a directory and retrieve the children and process them
    var icon = $(parent).children("span")[0];
    load_children(icon, function(data, textStatus) {
      $(parent).append(data);
      $(icon).addClass("ui-icon-plus");
      checkbox_click(checkbox, null);
      var boxes = $(parent).find(".gCheckboxTree :checkbox[checked]");
      process_length += boxes.length;
      $.each(boxes, function () {
        process_checkbox(this);
      });
    });
    current++;
    $("#gProgressBar").progressbar("value", current / process_length * 100);
  }
}

function process_file(li_element, parms) {
  $.ajax({async: false,
          success:  function(data, status) {
          },
          data: parms,
          dataType: "html",
          type: "POST",
          url: $("#gLocalImport form").attr("action")
  });
  current++;
  $("#gProgressBar").progressbar("value", current / process_length * 100);
}

