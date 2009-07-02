/**
 * We've clicked the + icon next to a directory.  Load up children of this
 * directory from the server and display them.
 */
function open_close_branch(path, id) {
  var parent = $("#file_" + id);
  var children = $("#tree_" + id);
  var icon = parent.find(".ui-icon:first");

  if (!children.html()) {
    parent.addClass("gLoadingSmall");
    $.ajax({
      url: GET_CHILDREN_URL.replace("__PATH__", path),
      success: function(data, textStatus) {
        children.html(data);
	parent.removeClass("gLoadingSmall");

	// Propagate checkbox value
	children.find("input[type=checkbox]").attr(
          "checked", parent.find("input[type=checkbox]:first").attr("checked"));
      },
    });
  }

  children.slideToggle("fast", function() {
    if (children.is(":hidden")) {
      icon.addClass("ui-icon-plus");
      icon.removeClass("ui-icon-minus");
    } else {
      icon.addClass("ui-icon-minus");
      icon.removeClass("ui-icon-plus");
      parent.removeClass("gCollapsed");
    }
  });
}

/**
 * We've clicked a checkbox.  Propagate the value downwards as necessary.
 */
function click_node(checkbox) {
  var parent = $(checkbox).parents("li").get(0);
  var checked = $(checkbox).attr("checked");
  $(parent).find("input[type=checkbox]").attr("checked", checked);

  if ($("#gServerAddTree").find("input[type=checkbox]").is(":checked")) {
    $("#gServerAddAddButton").attr("disabled", true);
    $("#gServerAddAddButton").removeClass("ui-state-disabled");
  } else {
    $("#gServerAddAddButton").attr("disabled", false);
    $("#gServerAddAddButton").addClass("ui-state-disabled");
  }
}

/* ================================================================================ */

/*
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
    } else {
      document.location.reload();
    }
  });
  set_click_events();
}

function set_click_events() {
  $(".ui-icon").unbind("click");
  $(":checkbox").unbind("click");
  $(".ui-icon").click(function(event) {
    open_close_branch(this, event);
  });

  $("input[type=checkbox]").click(function(event) {
    checkbox_click(this);
  });
}

function open_close_branch(icon, event) {
  var parent = icon.parentNode;
  var closed = $(icon).hasClass("ui-icon-plus");
  var children = $(parent).find(".gCheckboxTree");

  if (closed) {
    if (children.length == 0) {
      load_children(icon);
    } else {
        toggle_branch("open", icon);
    }
  } else {
    toggle_branch("close", icon);
  }
}

function toggle_branch(direction, icon) {
  var parent = icon.parentNode;
  var branch = $(parent).children(".gServerAddChildren");
  $(branch).slideToggle("fast", function() {
    if (direction == "open") {
      $(icon).addClass("ui-icon-minus");
      $(icon).removeClass("ui-icon-plus");
      $(parent).removeClass("gCollapsed");
    } else {
      $(icon).addClass("ui-icon-plus");
      $(icon).removeClass("ui-icon-minus");
    }
  });
}

function get_url(uri, task_id) {
  var url = $("#gServerAdd form").attr("action");
  url = url.replace("__ARGS__", uri);
  url = url.replace("__TASK_ID__", !task_id ? "" : "/" + task_id);
  return url;
}

function load_children(icon) {
  $("#gDialog").addClass("gDialogLoadingLarge");
  var parent = icon.parentNode;
  var checkbox = $(parent).find("input[type=checkbox]");
  var parms = "&path=" + $(checkbox).attr("value");
  parms += "&checked=" + $(checkbox).is(":checked");
  parms += "&collapsed=" + $(parent).hasClass("gCollapsed");

  $.ajax({success: function(data, textStatus) {
            $(parent).children(".gServerAddChildren").html(data);
            set_click_events();
            $("#gDialog").removeClass("gDialogLoadingLarge");
            toggle_branch("open", icon);
          },
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

  var parms = "";
  if (!paused) {
    $(".gProgressBar").progressbar("value", 0);
    $(".gProgressBar").css("visibility", "visible");
    var check_list = $("#gServerAdd :checkbox[checked]");

    var paths = "";
    var collapsed = "";
    $.each(check_list, function () {
      var parent = $(this).parents("li")[0];
      paths += "&path[]=" + this.value;
      collapsed += "&collapsed[]=" + $(parent).hasClass("gCollapsed");
    });
    parms = paths + collapsed;
  }
  paused = false;

  $.ajax({async: false,
    data: parms,
    dataType: "json",
    success: function(data, textStatus) {
      var done = data.task.done;
      if (done) {
        task = null;
        $("body").append("<div id='gNoFilesDialog'>" + data.task.status + "</div>");

        $("#gNoFilesDialog").dialog({modal: true,
                                    autoOpen: true,
                                    title: FILE_IMPORT_WARNING});
        $(".gProgressBar").css("visibility", "hidden");
        $("#gServerAdd #gServerAddButton").show();
        $("#gServerAdd #gServerPauseButton").hide();
        return;
      }
      task = data.task;
      var url = data.url;
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
  $("body").append("<div id=\"gServerAddError\" title=\"" + FATAL_ERROR + "\">" + error + "</div>");
  $("#gServerAddError").dialog({
      autoOpen: true,
      autoResize: false,
      modal: true,
      resizable: true,
      width: 610,
      height: $("#gDialog").height()
    });
}

*/
