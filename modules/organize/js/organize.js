/*
 * @todo Trap resize of dialog and resize the child areas (tree, grid and edit form)
 */
var url;
var paused = false;
var task = null;
var transitItems = [];
var heightMicroThumbPanel;

// **************************************************************************
// JQuery UI Widgets
// Draggable
var draggable = {
  handle: ".gMicroThumbContainer.ui-selected",
  revert: true,
  zindex: 2000,
  distance: 10,
  helper: function(event, ui) {
    if (!$(event.currentTarget).hasClass("ui-selected")) {
      $(event.currentTarget).addClass("ui-selected");
      setDrawerButtonState();
    }
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
      var height = new String(children.css("height")).replace(/[^0-9]/g,"") * .5;
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
      $("#gMessage").empty().append(INVALID_DROP_TARGET);
      ui.draggable.trigger("stop", event);
      return false;
    }
    var postData = serializeItemIds("#gDragHelper li");
    var okToMove = true;
    $("#gDragHelper li").each(function(i) {
      okToMove &= targetItemId != $(this).attr("ref");
    });
    if (!okToMove) {
      $("#gMessage").empty().append(INVALID_DROP_TARGET);
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
    return true;
  }
};

// Selectable
var selectable = {
  filter: ".gMicroThumbContainer",
  selected: function(event, ui) {
    setDrawerButtonState();
  },
  unselected: function(event, ui) {
    setDrawerButtonState();
  },
  stop: function(event, ui) {
    getEditForm();
  }
};

// **************************************************************************
// Event Handlers
// MicroThumbContainer mouseup
var onMicroThumbContainerMouseup = function(event) {
  // For simplicity always remove the ui-selected class.  If it was unselected
  // it will get added back
  $(this).toggleClass("ui-selected");

  setDrawerButtonState();
  if ($("#gMicroThumbGrid li.ui-selected").length > 0) {
    getEditForm();
  }
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
    case "close":
      $("#gOrganizeEditDrawerPanel").animate(
        {"height": "toggle", "display": "block"},
        {duration: "fast",
         complete: function() {
           setSelectedThumbs();
           if (operation == "close") {
             $("#gOrganizeEditHandleButtonsLeft a[ref='edit']").css("display", "inline-block");
             $("#gOrganizeEditHandleButtonsLeft a[ref='close']").css("display", "none");
             $("#gOrganizeEditHandleButtonsMiddle a").css("display", "none");
           } else {
             $("#gOrganizeEditHandleButtonsLeft a[ref='edit']").css("display", "none");
             $("#gOrganizeEditHandleButtonsLeft a[ref='close']").css("display", "inline-block");
             $("#gOrganizeEditHandleButtonsMiddle a").css("display", "inline-block");
           }
         },
         step: function() {
           $("#gMicroThumbPanel").height(heightMicroThumbPanel - $(this).height());
         }
      });
      break;
    case "select-all":
      $("#gMicroThumbGrid li").addClass("ui-selected");
      $("#gMicroThumbSelectAll").hide();
      $("#gMicroThumbUnselectAll").show();
      setDrawerButtonState();
      getEditForm();
      break;
    case "unselect-all":
      $("#gMicroThumbGrid li").removeClass("ui-selected");
      $("#gMicroThumbSelectAll").show();
      $("#gMicroThumbUnselectAll").hide();
      setDrawerButtonState();
      break;
    case "done":
      $("#gDialog").dialog("close");
      break;
    case "submit":
      var currentTab = $("#gOrganizeEditForm").tabs("option", "selected");
      var form = $("#pane-"+currentTab+" form");
      var url = $(form).attr("action")
        .replace("__FUNCTION__", $(form).attr("ref"));
      $.ajax({
        data: $(form).serialize(),
        dataType: "json",
        success: function (data, textStatus) {
          $("#pane-"+currentTab).children("form").replaceWith(data.form);
          if (data.message) {
            $("#gMessage").empty().append("<div class='gSuccess'>" + data.message + "</div>");
          }
        },
        type: "POST",
        url: url
      });
      break;
    case "reset":
      currentTab = $("#gOrganizeEditForm").tabs("option", "selected");
      form = $("#pane-"+currentTab+" form");
      $.ajax({
        data: serializeItemIds("#gMicroThumbPanel li.ui-selected"),
        dataType: "html",
        success: function (data, textStatus) {
          $("#pane-"+currentTab + " form").replaceWith(data);
        },
        type: "GET",
        url: $(form).attr("action").replace("__FUNCTION__", "reset_" + $(form).attr("ref"))
      });
      break;
    case "delete":
      if (!confirm(CONFIRM_DELETE)) {
        break;
      }
    default:
      $.ajax({
        data: serializeItemIds("#gMicroThumbPanel li.ui-selected"),
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
    $(".gMicroThumbContainer").mouseup(onMicroThumbContainerMouseup);
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
        $("#gMessage").empty().append("<div class='gSuccess'>" + data.task.status + "</div>");
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
  heightMicroThumbPanel = size.height() - 100;
  var width = size.width() - 100;

  // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475
  $(".sf-menu li.sfHover ul").css("z-index", 70);

  $("#gDialog").dialog("option", "width", width);
  $("#gDialog").dialog("option", "height", heightMicroThumbPanel);

  $("#gDialog").dialog("open");
  if ($("#gDialog h1").length) {
    $("#gDialog").dialog('option', 'title', $("#gDialog h1:eq(0)").html());
  } else if ($("#gDialog fieldset legend").length) {
    $("#gDialog").dialog('option', 'title', $("#gDialog fieldset legend:eq(0)").html());
  }

  $("#gDialog").bind("organize_close", function(target) {
    document.location.reload();
  });

  heightMicroThumbPanel -= 2 * parseFloat($("#gDialog").css("padding-top"));
  heightMicroThumbPanel -= 2 * parseFloat($("#gDialog").css("padding-bottom"));
  heightMicroThumbPanel -= $("#gMicroThumbPanel").position().top;
  heightMicroThumbPanel -= $("#gDialog #ft").height();
  heightMicroThumbPanel -= $("#gOrganizeEditDrawerHandle").height();
  heightMicroThumbPanel = Math.round(heightMicroThumbPanel);

  $("#gMicroThumbPanel").height(heightMicroThumbPanel);
  $("#gOrganizeTreeContainer").height(heightMicroThumbPanel);

  $(".gOrganizeBranch .ui-icon").click(organizeToggleChildren);
  $(".gBranchText").droppable(treeDroppable);
  $(".gBranchText").click(organizeOpenFolder);
  retrieveMicroThumbs(item_id);
  //showLoading("#gDialog");

  $("#gMicroThumbPanel").droppable(thumbDroppable);
  $("#gMicroThumbPanel").selectable(selectable);
  $("#gOrganizeEditDrawerHandle a").click(drawerHandleButtonsClick);
}

function retrieveMicroThumbs() {
  var offset = $("#gMicroThumbGrid li").length;
  if (url == null) {
    var grid_width = $("#gMicroThumbPanel").width();
    url = $("#gMicroThumbPanel").attr("ref");
    url = url.replace("__WIDTH__", grid_width);
    url = url.replace("__HEIGHT__", heightMicroThumbPanel);
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
  $("#gOrganizeFormThumbStack").empty();
  $("#gOrganizeEditForm").empty();
  var selectedCount = $("#gMicroThumbGrid li.ui-selected").length;
  if (selectedCount) {
    $("#gOrganizeEditHandleButtonsLeft a").removeAttr("disabled");
    $("#gOrganizeEditHandleButtonsLeft a").removeClass("ui-state-disabled");

    if (selectedCount > 1) {
      $("#gOrganizeEditHandleButtonsLeft a[ref='albumCover']").attr("disabled", true);
      $("#gOrganizeEditHandleButtonsLeft a[ref='albumCover']").addClass("ui-state-disabled");
    }
    setSelectedThumbs();
  } else {
    if ($("#gOrganizeEditDrawerPanel::visible").length) {
      $("#gOrganizeEditHandleButtonsLeft a[ref='close']").trigger("click");
    }
    $("#gOrganizeEditHandleButtonsLeft a").attr("disabled", true);
    $("#gOrganizeEditHandleButtonsLeft a").addClass("ui-state-disabled");
  }
}

function setSelectedThumbs() {
  if (!$("#gOrganizeEditDrawerPanel::visible").length) {
    return;
  }
  var position = $("#gOrganizeFormThumbStack").position();
  var beginLeft = position.left;
  var beginTop = 50;
  var zindex = 2000;
  $("li.ui-selected").each(function(i) {
    var clone = $(this).clone();
    $(clone).attr("id", "edit_clone_" + $(this).attr("ref"));
    $("#gOrganizeFormThumbStack").append(clone);
    $(clone).removeClass("ui-draggable");
    $(clone).removeClass("ui-selected");
    $(clone).css("margin-top", beginTop);
    $(clone).css("left", beginLeft);
    $(clone).css("z-index", zindex--);

    if (i < 9) {
      beginTop -= 5;
      beginLeft += 5;
    }
  });
}

function getEditForm() {
  if ($("#gMicroThumbGrid li.ui-selected").length > 0) {
    var postData = serializeItemIds("li.ui-selected");
    var url_data = get_url("organize/editForm", {}) + postData;
    $.get(url_data, function(data, textStatus) {
      $("#gOrganizeEditForm").tabs("destroy");
      $("#gOrganizeEditForm").html(data);
      if ($("#gOrganizeEditForm ul li").length) {
        $("#gOrganizeEditForm").tabs();
        $("#gOrganizeEditHandleButtonsMiddle a").removeAttr("disabled");
        $("#gOrganizeEditHandleButtonsMiddle a").removeClass("ui-state-disabled");
      } else {
        $("#gOrganizeEditHandleButtonsMiddle a").attr("disabled", true);
        $("#gOrganizeEditHandleButtonsMiddle a").addClass("ui-state-disabled");
      }
    });
  } else {
    $("#gOrganizeEditForm").tabs("destroy");
    $("#gOrganizeEditForm").empty();
  }
}

function serializeItemIds(selector) {
  var postData = "";
  $(selector).each(function(i) {
    postData += "&item[]=" + $(this).attr("ref");
  });

  return postData;
}

function submitCurrentForm(event) {
  console.log("submitCurrentForm");
  return false;
}

function resetCurrentForm(event) {
  console.log("resetCurrentForm");
  return false;
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
    $("#gMessage").empty().append(task.pauseMsg);
  });
  $("#gOrganizeTaskResume").click(function(event) {
    $("#gOrganizeTaskPause").show();
    $("#gOrganizeTaskResume").hide();
    $("#gOrganizeTaskCancel").hide();
    $("#gMessage").empty().append(task.resumeMsg);
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
        $("#gMessage").empty().append("<div class='gWarning'>" + data.task.status + "</div>");
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
