/**
 * Manage file selection state.
 */
function select_file(li) {
  $(li).toggleClass("selected");
  if ($("#gServerAdd span.selected").length) {
    $("#gServerAddAddButton").enable(true).removeClass("ui-state-disabled");
  } else {
    $("#gServerAddAddButton").enable(false).addClass("ui-state-disabled");
  }
}

/**
 * Load a new directory
 */
function open_dir(path) {
  $.ajax({
    url: GET_CHILDREN_URL.replace("__PATH__", path),
    success: function(data, textStatus) {
      $("#gServerAddTree").html(data);
    }
  });
}

function start_add() {
  var paths = [];
  $.each($("#gServerAdd span.selected"),
	 function () {
	   paths.push($(this).attr("file"));
	 }
  );

  $.ajax({
    url: START_URL,
    type: "POST",
    async: false,
    data: { "paths[]": paths },
    dataType: "json",
    success: function(data, textStatus) {
      $("#gStatus").html(data.status);
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
      $("#gStatus").html(data.status);
      $("#gServerAdd .gProgressBar").progressbar("value", data.percent_complete);
      if (data.done) {
	$("#gServerAddProgress").slideUp();
      } else {
	setTimeout(function() { run_add(url); }, 0);
      }
    }
  });
}

