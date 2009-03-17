$("#gServerAdd").ready(function() {
  $("#gServerAdd :submit").click(function(event) {
    do_add(this, event);
  });
  $(".gProgressBar").progressbar();
  $("#gServerAddTree ul").css("display", "block");
});

function open_close_branch(icon, event) {
  var parent = icon.parentNode;
  var children = $(parent).find(".gCheckboxTree");
  var closed = $(icon).hasClass("ui-icon-plus");

  if (closed) {
    if (children.length == 0) {
      load_children(parent, function(data, textStatus) {
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

function get_url(uri, task_id) {
  var url = $("#gServerAdd form").attr("action");
  url = url.replace("__ARGS__", uri);
  url = url.replace("__TASK_ID__", !task_id ? "" : "/" + task_id);
  return url;
}

function checkbox_click(checkbox, event) {
  var parents = $(checkbox).parents("li");
  var parent = parents.get(0);
  $(parent).find(".gCheckboxTree :checkbox").attr("checked", checkbox.checked);
  var checked = $("#gServerAdd :checkbox[checked]");
  $("#gServerAdd form :submit").attr("disabled", checked.length == 0);
}

function load_children(parent, callback) {
  var parms = "&path=" +  $(parent).find(":checkbox").attr("value");
  $.ajax({async: false,
          success: callback,
          data: parms,
          dataType: "html",
          type: "POST",
          url: get_url("server_add/children")
  });
}

function do_add(submit, event) {
  event.preventDefault();
  $(".gProgressBar").progressbar("value", 0);
  $(".gProgressBar").css("visibility", "visible");
  var check_list = $("#gServerAdd :checkbox[checked]");

  var parms = "";
  $.each(check_list, function () {
    var parent = $(this).parents("li")[0];
    // If its a file or a directory with no children
    if ($(parent).hasClass("gFile") ||
        ($(parent).hasClass("gDirectory") && $(parent).find(".gCheckboxTree").length == 0)) {
      parms += "&path[]=" + this.value;
    }
  });
  $.ajax({async: false,
    data: parms,
    dataType: "json",
    success: function(data, textStatus) {
      var task = data.task;
      var url = data.url;
      var done = false;
      while (!done) {
        $.ajax({async: false,
          success: function(data, textStatus) {
            $(".gProgressBar").progressbar("value", data.task.percent_complete);
            done = data.task.done;
          },
          dataType: "json",
          type: "POST",
          url: url
        });
      }
      $.ajax({async: false,
        success: function(data, textStatus) {
          document.location.reload();
        },
        dataType: "json",
        type: "POST",
        url: get_url("server_add/finish", task.id)
      });
    },
    type: "POST",
    url: get_url("server_add/start")
  });

  return false;
}

