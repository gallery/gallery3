/*
 * @todo Trap resize of dialog and resize the child areas (tree, grid and edit form)
 */
var url;
var height;
var paused = false;
var task = null;
var transitItems = [];

// **************************************************************************
// JQuery UI Widgets
// Draggable
var draggable = {
  cancel: ".gMicroThumbContainer:not(.ui-selected)",
  handle: ".gMicroThumbContainer.ui-selected",
  revert: true,
  zindex: 2000,
  helper: function(event, ui) {
    $("#gMicroThumbPanel").append("<div id=\"gDragHelper\"><ul></ul></div>");
    var beginTop = event.pageY;
    var beginLeft = event.pageX;
    var zindex = $(".gMicroThumbContainer").draggable("option", "zindex");
    $("#gDragHelper").css('top', event.pageY - 22.5);
    $("#gDragHelper").css('left', event.pageX + 22.5);
    var placeHolder = $(this).clone();
    $(placeHolder).attr("id", "gPlaceHolder");
    $(placeHolder).css("visibility", "hidden");
    $(placeHolder).removeClass("ui-selected");
    $(placeHolder).removeClass("ui-draggable");
    $(this).after(placeHolder);

    $("li.ui-selected").each(function(i) {
      var clone = $(this).clone();
      $(clone).attr("id", "drag_clone_" + $(this).attr("ref"));
      $("#gDragHelper ul").append(clone);
      $(clone).css("position", "absolute");
      $(clone).css("top", beginTop);
      $(clone).css("left", beginLeft);
      $(clone).css("z-index", zindex--);
      $(this).hide();

      var children = $(clone).find(".gMicroThumb .gThumbnail");
      var width = new String(children.css("width")).replace(/[^0-9]/g,"") * .5;
      height = new String(children.css("height")).replace(/[^0-9]/g,"") * .5;
      var marginTop = new String(children.css("margin-top")).replace(/[^\.0-9]/g,"") * .5;
      children.attr("width", width);
      children.attr("height", height);
      children.css("margin-top", marginTop);
      if (i < 9) {
        beginTop -= 5;
        beginLeft += 5;
      }
    });
    return $("#gDragHelper");
  },
  stop: function(event, ui) {
    $("#gDragHelper li").each(function(i) {
      $("#thumb_" + $(this).attr("ref")).show();
    });
    $(".gMicroThumbContainer.ui-selected").css("z-index", null);
    $("#gDragHelper").remove();
    $("#gPlaceHolder").remove();
  }
};

// Thumbnail Grid Droppable
var thumbDroppable =  {
  tolerance: "pointer",
  over: function(event, ui) {
    $("#gPlaceHolder").show();
  },
  out:  function(event, ui) {
    $("#gPlaceHolder").hide();
  },
  drop: function(event, ui) {
    $("#gDragHelper").hide();
    $("#gPlaceHolder").hide();
    var newOrder = "";
    $("#gMicroThumbGrid .gMicroThumbContainer").each(function(i) {
      if ($(this).attr("id") == "gPlaceHolder") {
        $("#gDragHelper li").each(function(i) {
          newOrder += "&item[]=" + $(this).attr("ref");
        });
      } else if ($(this).css("display") != "none") {
        newOrder += "&item[]=" + $(this).attr("ref");
      } else  {
        // If its not displayed then its one of the ones being moved so ignore.
      }
    });
    $("#gDragHelper li").each(function(i) {
      $("#gPlaceHolder").before($("#thumb_" + $(this).attr("ref")).show());
    });
    $.ajax({
      data: newOrder,
      dataType: "json",
      success: operationCallback,
      type: "POST",
      url: get_url("organize/startTask/rearrange", {item_id: item_id})
    });
  }
};

// Album Tree Droppable
var treeDroppable =  {
  tolerance: "pointer",
  greedy: true,
  hoverClass: "gBranchDroppable",
  drop: function(event, ui) {
    $("#gDragHelper").hide();
    var targetItemId = $(this).attr("ref");
    if ($(this).hasClass("gBranchSelected")) {
      $("#gOrganizeStatus").empty().append(INVALID_DROP_TARGET);
      ui.draggable.trigger("stop", event);
      return false;
    }
    var postData = serializeItemIds("#gDragHelper li");
    var okToMove = true;
    $("#gDragHelper li").each(function(i) {
      okToMove &= targetItemId != $(this).attr("ref");
    });
    if (!okToMove) {
      $("#gOrganizeStatus").empty().append(INVALID_DROP_TARGET);
      ui.draggable.trigger("stop", event);
      return false;
    }
    $("#gDragHelper li").each(function(i) {
      $("#thumb_" + $(this).attr("ref")).remove();
    });
    $.ajax({
      data: postData,
      dataType: "json",
      success: operationCallback,
      type: "POST",
      url: get_url("organize/startTask/move", {item_id: targetItemId})
    });
  }
};

// Selectable
var selectable = {
  filter: ".gMicroThumbContainer",
  selected: function(event, ui) {
    $(ui.selected).addClass("gSelecting");
    setDrawerButtonState();
  },
  unselected: function(event, ui) {
    setDrawerButtonState();
  },
  stop: function(event) {
  }
};

// **************************************************************************
// Event Handlers
// MicroThumbContainer click
var onMicroThumbContainerClick = function(event) {
  if (!$(this).hasClass("gSelecting") && $(this).hasClass("ui-selected")) {
    $(this).removeClass("ui-selected");
  }
  $(this).removeClass("gSelecting");

  setDrawerButtonState();
};

// MicroThumbContainer mousemove
var onMicroThumbContainerMousemove = function(event) {
  if ($("#gDragHelper").length > 0 && $(this).attr("id") != "gPlaceHolder") {
    if (event.pageX < $(this).offset().left + $(this).width() / 2) {
      $(this).before($("#gPlaceHolder"));
    } else {
      $(this).after($("#gPlaceHolder"));
    }
    var container = $("#gMicroThumbPanel").get(0);
    var scrollHeight = container.scrollHeight;
    var scrollTop = container.scrollTop;
    var height = $(container).height();
    if (event.pageY > height + scrollTop) {
      container.scrollTop = this.offsetTop;
    } else if (event.pageY < scrollTop) {
      container.scrollTop -= height;
    }
  }
};

// Handle click events on the buttons on the drawer handle
function drawerHandleButtonsClick(event) {
  event.preventDefault();
  if (!$(this).attr("disabled")) {
    var operation = $(this).attr("ref");
    switch (operation) {
    case "edit":
      $("#gOrganizeEditDrawerPanel").slideToggle("normal");
      break;
    case "select-all":
      $(".gMicroThumbContainer").addClass("ui-selected");
      $("#gMicroThumbSelectAll").hide();
      $("#gMicroThumbUnselectAll").show();
      break;
    case "unselect-all":
      $(".gMicroThumbContainer").removeClass("ui-selected");
      $("#gMicroThumbSelectAll").show();
      $("#gMicroThumbUnselectAll").hide();
      break;
    case "close":
      $("#gDialog").dialog("close");
      break;
    default:
      var postData = serializeItemIds("#gMicroThumbPanel li.ui-selected");
      $.ajax({
        data: postData,
        dataType: "json",
        success: operationCallback,
        type: "POST",
        url: get_url("organize/startTask/" + operation, {item_id: item_id})
      });
      break;
    }
  }
};

// **************************************************************************
// AJAX Callbacks
// MicroThumbContainer click
var getMicroThumbsCallback = function(json, textStatus) {
  if (json.count > 0) {
    $("#gMicroThumbGrid").append(json.data);
    retrieveMicroThumbs();
    $(".gMicroThumbContainer").click(onMicroThumbContainerClick);
    $(".gMicroThumbContainer").mousemove(onMicroThumbContainerMousemove);
    $(".gMicroThumbContainer").draggable(draggable);
  }
};

var operationCallback = function (data, textStatus) {
  var done = false;
  if (!paused) {
    createProgressDialog(data.runningMsg);
    task = data.task;
    task.pauseMsg = data.pauseMsg;
    task.resumeMsg = data.resumeMsg;
    done = data.task.done;
  }
  $(".gMicroThumbContainer").draggable("disable");
  paused = false;
  while (!done && !paused) {
    $.ajax({async: false,
      success: function(data, textStatus) {
        $(".gProgressBar").progressbar("value", data.task.percent_complete);
        done = data.task.done;
        if (data.task.post_process.reload) {
          $.each(data.task.post_process.reload, function() {
            var selector = "#gMicroThumb-" + this.id + " img";
            $(selector).attr("height", this.height);
            $(selector).attr("width", this.width);
            $(selector).attr("src", this.src);
            $(selector).css("margin-top", this.marginTop);
          });
        }
        if (data.task.post_process.remove) {
          $.each(data.task.post_process.remove, function() {
            $("#thumb_" + this.id).remove();
          });
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
        paused = true;
        displayAjaxError(XMLHttpRequest.responseText);
      },
      dataType: "json",
      type: "POST",
      url: get_url("organize/runTask", {task_id: task.id})
    });
  }
  if (!paused) {
    $("#gOrganizeProgressDialog").dialog("destroy").remove();
    $.ajax({async: false,
      success: function(data, textStatus) {
        setDrawerButtonState();
        task = null;
        $("#gOrganizeStatus").empty().append("<div class='gSuccess'>" + data.task.status + "</div>");
      },
      dataType: "json",
      type: "POST",
      url: get_url("organize/finishTask", {task_id: task.id})
    });
  }
  $(".gMicroThumbContainer").draggable("enable");
};

// **************************************************************************

/**
 * Dynamically initialize the organize dialog when it is displayed
 */
function organize_dialog_init() {
  var size = getViewportSize();
  height = size.height() - 100;
  var width = size.width() - 100;

  // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475
  $(".sf-menu li.sfHover ul").css("z-index", 70);

  $("#gDialog").dialog("option", "width", width);
  $("#gDialog").dialog("option", "height", height);

  $("#gDialog").dialog("open");
  if ($("#gDialog h1").length) {
    $("#gDialog").dialog('option', 'title', $("#gDialog h1:eq(0)").html());
  } else if ($("#gDialog fieldset legend").length) {
    $("#gDialog").dialog('option', 'title', $("#gDialog fieldset legend:eq(0)").html());
  }

  $("#gDialog").bind("organize_close", function(target) {
    document.location.reload();
  });

  height -= 2 * parseFloat($("#gDialog").css("padding-top"));
  height -= 2 * parseFloat($("#gDialog").css("padding-bottom"));
  height -= $("#gMicroThumbPanel").position().top;
  height -= $("#gDialog #ft").height();
  height = Math.round(height);

  $("#gMicroThumbPanel").height(height);
  $("#gOrganizeTreeContainer").height(height);

  $(".gOrganizeBranch .ui-icon").click(organizeToggleChildren);
  $(".gBranchText").droppable(treeDroppable);
  $(".gBranchText").click(organizeOpenFolder);
  retrieveMicroThumbs(item_id);
  //showLoading("#gDialog");

  $("#gMicroThumbPanel").droppable(thumbDroppable);
  $("#gMicroThumbGrid").selectable(selectable);
  $("#gOrganizeEditDrawerHandle a").click(drawerHandleButtonsClick);
}

function retrieveMicroThumbs() {
  var offset = $("#gMicroThumbGrid li").length;
  if (url == null) {
    var grid_width = $("#gMicroThumbPanel").width();
    url = $("#gMicroThumbPanel").attr("ref");
    url = url.replace("__WIDTH__", grid_width);
    url = url.replace("__HEIGHT__", height);
  }
  var url_data = url.replace("__OFFSET__", offset);
  url_data = url_data.replace("__ITEM_ID__", item_id);
  $.getJSON(url_data, getMicroThumbsCallback);
}

function organizeToggleChildren(event) {
  var id = $(this).attr("ref");
  var span_children = $("#gOrganizeChildren-" + id);
  if ($(this).hasClass("ui-icon-plus")) {
    $(this).removeClass("ui-icon-plus");
    $(this).addClass("ui-icon-minus");
    $("#gOrganizeChildren-" + id).removeClass("gBranchCollapsed");
  } else {
    $(this).removeClass("ui-icon-minus");
    $(this).addClass("ui-icon-plus");
    $("#gOrganizeChildren-" + id).addClass("gBranchCollapsed");
  }
  event.preventDefault();
}

function organizeOpenFolder(event) {
  var selected = $(".gBranchSelected");
  if ($(selected).attr("id") != $(this).attr("id")) {
    $(selected).removeClass("gBranchSelected");
    $(this).addClass("gBranchSelected");
    item_id = $(this).attr("ref");
    $("#gMicroThumbGrid").empty();
    retrieveMicroThumbs();
  }
  event.preventDefault();
}

function get_url(uri, parms) {
  var url = rearrangeUrl;
  url = url.replace("__URI__", uri);
  url = url.replace("__ITEM_ID__", !parms.item_id ? "" : parms.item_id);
  url += (parms.item_id && parms.task_id) ? "/" : "";
  url = url.replace("__TASK_ID__", !parms.task_id ? "" : parms.task_id);
  return url;
}

/**
 * Set the enabled/disabled state of the buttons.  The album cover is only enabled if
 * there is only 1 image selected
 */
function setDrawerButtonState() {
  switch ($("#gMicroThumbGrid li.ui-selected").length) {
  case 0:
    $("#gOrganizeEditHandleButtonsLeft a").attr("disabled", true);
    $("#gOrganizeEditHandleButtonsLeft a").addClass("ui-state-disabled");
    break;
  case 1:
    $("#gOrganizeEditHandleButtonsLeft a").removeAttr("disabled");
    $("#gOrganizeEditHandleButtonsLeft a").removeClass("ui-state-disabled");
   break;
  default:
    $("#gOrganizeEditHandleButtonsLeft a[ref='albumCover']").attr("disabled", true);
    $("#gOrganizeEditHandleButtonsLeft a[ref='albumCover']").addClass("ui-state-disabled");
  }
}

function serializeItemIds(selector) {
  var postData = "";
  $(selector).each(function(i) {
    postData += "&item[]=" + $(this).attr("ref");
  });

  return postData;
}

function createProgressDialog(title) {
  $("body").append("<div id='gOrganizeProgressDialog'>" +
      "<div class='gProgressBar'></div>" +
      "<button id='gOrganizeTaskPause' class='ui-state-default ui-corner-all'>" + PAUSE_BUTTON + "</button>" +
      "<button id='gOrganizeTaskResume' class='ui-state-default ui-corner-all' style='display: none'>" + RESUME_BUTTON + "</button>" +
      "<button id='gOrganizeTaskCancel' class='ui-state-default ui-corner-all' style='display: none'>" + CANCEL_BUTTON + "</button>" +
    "</div>");
  $("#gOrganizeProgressDialog").dialog({
    autoOpen: true,
    autoResize: false,
    modal: true,
    resizable: false,
    title: title
  });

  $(".gProgressBar").progressbar();
  $("#gOrganizeTaskPause").click(function(event) {
    paused = true;
    $("#gOrganizeTaskPause").hide();
    $("#gOrganizeTaskResume").show();
    $("#gOrganizeTaskCancel").show();
    $("#gOrganizeStatus").empty().append(task.pauseMsg);
  });
  $("#gOrganizeTaskResume").click(function(event) {
    $("#gOrganizeTaskPause").show();
    $("#gOrganizeTaskResume").hide();
    $("#gOrganizeTaskCancel").hide();
    $("#gOrganizeStatus").empty().append(task.resumeMsg);
    operationCallback();
    //startRearrangeCallback();
  });
  $("#gOrganizeTaskCancel").click(function(event) {
    $("#gOrganizeTaskPause").show();
    $("#gOrganizeTaskResume").hide();
    $("#gOrganizeTaskCancel").hide();

    $.ajax({async: false,
      success: function(data, textStatus) {
        task = null;
        paused = false;
        transitItems = [];
        $("#gOrganizeStatus").empty().append("<div class='gWarning'>" + data.task.status + "</div>");
        $("#gOrganizeProgressDialog").dialog("destroy").remove();
      },
      dataType: "json",
      type: "POST",
      url: get_url("organize/cancelTask", {task_id: task.id})
    });
  });
}

// **************************************************************************
// Functions that should probably be in a gallery namespace
function getViewportSize() {
  return {
      width : function() {
        return window.innerWidth
          || document.documentElement && document.documentElement.clientWidth
          || document.body.clientWidth;
      },
      height : function() {
        return window.innerHeight
          || document.documentElement && document.documentElement.clientHeight
          || document.body.clientHeight;
      }
  };
}

function displayAjaxError(error) {
  $("body").append("<div id=\"gAjaxError\" title=\"" + FATAL_ERROR + "\">" + error + "</div>");
  $("#gAjaxError").dialog({
      autoOpen: true,
      autoResize: false,
      modal: true,
      resizable: true,
      width: 610,
      height: $("#gDialog").height()
    });
}
