var paused = false;
var task = null;

$("#gServerAdd").ready(function() {
  init_server_add_form();
});

function init_server_add_form() {
  $("#gServerAdd #gServerAddButton").click(function(event) {
    do_add(this, event);
  });
  $("#gServerAdd #gServerPauseButton").click(function(event) {
    event.preventDefault();
    paused = true;
  });
  $(".gProgressBar").progressbar();
  $("#gServerAddTree ul").css("display", "block");
  $("#gServerAdd form").bind("form_closing", function(target) {
    if (task != null && !task.done) {
      $.ajax({async: false,
        success: function(data, textStatus) {
          document.location.reload();
        },
        dataType: "json",
        type: "POST",
        url: get_url("server_add/pause", task.id)
      });
    }
  });
}

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
  var _this = this;
  var parents = $(checkbox).parents("li");
  var parent = parents.get(0);
  if ($(parent).hasClass("gDirectory") &&  $(parent).find(".gCheckboxTree").length == 0) {
      load_children(parent, function(data, textStatus) {
        $(parent).append(data);
      });
  }
  $(parent).find(".gCheckboxTree :checkbox").click();
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

  $("#gServerAdd #gServerAddButton").hide();
  $("#gServerAdd #gServerPauseButton").show();

  if (!paused) {
    $(".gProgressBar").progressbar("value", 0);
    $(".gProgressBar").css("visibility", "visible");
    var check_list = $("#gServerAdd :checkbox[checked]");

    var parms = "";
    $.each(check_list, function () {
      var parent = $(this).parents("li")[0];
      parms += "&path[]=" + this.value;
    });
  }
  paused = false;

  $.ajax({async: false,
    data: parms,
    dataType: "json",
    success: function(data, textStatus) {
      task = data.task;
      var url = data.url;
      var done = false;
      while (!done && !paused) {
        $.ajax({async: false,
          success: function(data, textStatus) {
            $(".gProgressBar").progressbar("value", data.task.percent_complete);
            done = data.task.done;
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            paused = true;
            display_upload_error(XMLHttpRequest.responseText);
          },
          dataType: "json",
          type: "POST",
          url: url
        });
      }
      if (!paused) {
        $.ajax({async: false,
          success: function(data, textStatus) {
            document.location.reload();
          },
          dataType: "json",
          type: "POST",
          url: get_url("server_add/finish", task.id)
        });
      } else {
        $("#gServerAdd #gServerAddButton").show();
        $("#gServerAdd #gServerPauseButton").hide();
      }
    },
    type: "POST",
    url: get_url("server_add/start")
  });

  return false;
}

function display_upload_error(error) {
  $("body").append("<div id=\"gServerAddError\" title=\"Fatal Error\">" + error + "</div>");
  $("#gServerAddError").dialog({
      autoOpen: true,
      autoResize: false,
      modal: true,
      resizable: true,
      width: 610,
      height: $("#gDialog").height()
    });
}

