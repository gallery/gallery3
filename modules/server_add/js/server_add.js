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
      }
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

  // @todo if we uncheck all the children for a parent, we should uncheck the
  // parent itself, otherwise in the code we'll add the entire parent since if
  // we find an album as a leaf, we assume that it's never been expanded in the UI.
  if ($("#gServerAddTree").find("input[type=checkbox]").is(":checked")) {
    $("#gServerAddAddButton").enable(true);
    $("#gServerAddAddButton").removeClass("ui-state-disabled");
  } else {
    $("#gServerAddAddButton").enable(false);
    $("#gServerAddAddButton").addClass("ui-state-disabled");
  }
}

function start_add() {
  var paths = [];
  $.each($("#gServerAdd :checkbox[checked]"), function () {
    paths.push(this.value);
  });
  $.ajax({
    url: START_URL,
    type: "POST",
    async: false,
    data: { "paths[]": paths },
    dataType: "json",
    success: function(data, textStatus) {
      $("#gServerAdd .gProgressBar").progressbar("value", data.percent_complete);
      setTimeout(function() { run_add(data.url); }, 0);
    }
  });
  return false;
}

function run_add(url) {
  $.ajax({
    url: url,
    async: false,
    dataType: "json",
    success: function(data, textStatus) {
      $("#gServerAdd .gProgressBar").progressbar("value", data.percent_complete);
      if (data.done) {
	$("#gServerAdd .gProgressBar").slideUp();
      } else {
	setTimeout(function() { run_add(url); }, 0);
      }
    }
  });
}

