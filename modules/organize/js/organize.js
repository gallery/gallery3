/*
 * @todo Trap resize of dialog and resize the child areas (tree, grid and edit form)
 */
var url;
var height;

function organize_dialog_init() {
  var size = viewport_size();
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

  height -= 2 * parseFloat($("#gDialog").css("padding-top"));
  height -= 2 * parseFloat($("#gDialog").css("padding-bottom"));
  height -= $("#gMicroThumbPanel").position().top;
  height -= $("#gDialog #ft").height();
  height = Math.round(height);

  $("#gMicroThumbPanel").height(height);
  $("#gOrganizeTreeContainer").height(height);

  $(".gOrganizeBranch .ui-icon").click(organize_toggle_children);
  $(".gBranchText").click(organize_open_folder);
  retrieve_micro_thumbs(item_id);
  //showLoading("#gDialog");

  $("#gMicroThumbPanel").scroll(function() {
    get_more_data();
  });

  $("#gMicroThumbSelectAll").click(function(event) {
    select_all(true);
    event.preventDefault();
  });
  $("#gMicroThumbUnselectAll").click(function(event) {
    select_all(false);
    event.preventDefault();
  });

  $("#gMicroThumbGrid").selectable({
    count: 0,
    filter: ".gMicroThumbContainer",
    selected: function(event, ui) {
      /*
       * Count the number of selected items if it is greater than 1,
       * then click won't be called so we need to remove the gSelecting
       * class in the stop event.
       */
      var count = $("#gMicroThumbGrid").selectable("option", "count") + 1;
      $("#gMicroThumbGrid").selectable("option", "count", count);
      $(ui.selected).addClass("gSelecting");
    },
    stop: function(event) {
      var count = $("#gMicroThumbGrid").selectable("option", "count");
      if (count > 1) {
        $(".gMicroThumbContainer.gSelecting").removeClass("gSelecting");
      }
      $("#gMicroThumbGrid").selectable("option", "count", 0);
    }
  });
}

function get_album_content() {
  var grid_width = $("#.gMicroThumbPanel").width();
  url = $("#gMicroThumbPanel").attr("ref");
  url = url.replace("__WIDTH__", grid_width);
  url = url.replace("__HEIGHT__", height);

  retrieve_micro_thumbs(url);
}

function retrieve_micro_thumbs() {
  var offset = $("#gMicroThumbGrid li").length;
  if (url == null) {
    var grid_width = $("#gMicroThumbPanel").width();
    url = $("#gMicroThumbPanel").attr("ref");
    url = url.replace("__WIDTH__", grid_width);
    url = url.replace("__HEIGHT__", height);
  }
  var url_data = url.replace("__OFFSET__", offset);
  url_data = url.replace("__ITEM_ID__", item_id);
  $.get(url_data, function(data) {
    $("#gMicroThumbGrid").append(data);
    get_more_data();
    $(".gMicroThumbContainer").click(function(event) {
      if ($(this).hasClass("gSelecting")) {
        $(this).removeClass("gSelecting");
      } else {
        $(this).removeClass("ui-selected");
      }
    });
    $(".gMicroThumbContainer").draggable({
      cancel: ".gMicroThumbContainer:not(.ui-selected)",
      handle: ".gMicroThumbContainer.ui-selected",
      zindex: 2000,
      helper: function(event, ui) {
        $("body").append("<div id=\"gDragHelper\"><ul></ul></div>");
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
	  $("#gPlaceHolder").before($("#thumb_" + $(this).attr("ref")).show());
        });
        $(".gMicroThumbContainer.ui-selected").css("z-index", null);
        $("#gDragHelper").remove();
        $("#gPlaceHolder").remove();
      }
    });
    $(".gMicroThumbContainer").droppable( {
      tolerance: "pointer",
      over: function(event, ui) {
        $(this).after($("#gPlaceHolder"));
      },
      drop: function(event, ui) {
        $("#gDragHelper li").each(function(i) {
	  $("#gPlaceHolder").before($("#thumb_" + $(this).attr("ref")).show());
        });
        $(".gMicroThumbContainer.ui-selected").css("z-index", null);
        $("#gDragHelper").remove();
        $("#gPlaceHolder").remove();
      }
    });
  });
}

function get_more_data() {
  var element = $("#gMicroThumbPanel").get(0);
  var scrollHeight = element.scrollHeight;
  var scrollTop = element.scrollTop;
  var height = $("#gMicroThumbPanel").height();
  var scrollPosition = scrollHeight - (scrollTop + height);
  if (scrollPosition > 0 && scrollPosition <= 100) {
    retrieve_micro_thumbs();
  }
}

function organize_toggle_children(event) {
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

function organize_open_folder(event) {
  var selected = $(".gBranchSelected");
  if ($(selected).attr("id") != $(this).attr("id")) {
    $(selected).removeClass("gBranchSelected");
    $(this).addClass("gBranchSelected");
    item_id = $(this).attr("ref");
    $("#gMicroThumbGrid").empty();
    retrieve_micro_thumbs();
  }
  event.preventDefault();
}

function select_all(select) {
  if (select) {
    $(".gMicroThumbContainer").addClass("ui-selected");
    $("#gMicroThumbSelectAll").hide();
    $("#gMicroThumbUnselectAll").show();
  } else {
    $(".gMicroThumbContainer").removeClass("ui-selected");
    $("#gMicroThumbSelectAll").show();
    $("#gMicroThumbUnselectAll").hide();
  }
}

function viewport_size() {
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
