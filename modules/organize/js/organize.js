/*
 * @todo Trap resize of dialog and resize the child areas (tree, grid and edit form)
 */
var url;
var height;

function get_album_content() {
  var grid_width = $("#gMicroThumbContainer").width();
  url = $("#gMicroThumbContainer").attr("ref");
  url = url.replace("__WIDTH__", grid_width);
  url = url.replace("__HEIGHT__", height);

  retrieve_micro_thumbs(url);
}

function retrieve_micro_thumbs() {
  var offset = $("#gMicroThumbGrid li").length;
  if (url == null) {
    var grid_width = $("#gMicroThumbContainer").width();
    url = $("#gMicroThumbContainer").attr("ref");
    url = url.replace("__WIDTH__", grid_width);
    url = url.replace("__HEIGHT__", height);
  }
  var url_data = url.replace("__OFFSET__", offset);
  url_data = url.replace("__ITEM_ID__", item_id);
  $.get(url_data, function(data) {
    $("#gMicroThumbGrid").append(data);
    get_more_data();
  });
}

function get_more_data() {
  var element = $("#gMicroThumbContainer").get(0);
  var scrollHeight = element.scrollHeight;
  var scrollTop = element.scrollTop;
  var height = $("#gMicroThumbContainer").height();
  var scrollPosition = scrollHeight - (scrollTop + height);
  if (scrollPosition > 0 && scrollPosition <= 100) {
    retrieve_micro_thumbs();
  }
}

function toggle_select(event) {
  var clone = null;
  var id = $(this).attr("id").replace(/[^0-9]/g, "");

  if ($(this).hasClass("gThumbSelected")) {
    $(this).removeClass("gThumbSelected");
    var newSelect = $(".gThumbSelected");
    if (newSelect.size() == 1) {
      clone = $(".gThumbSelected").children("img").clone(true);
      id = $(".gThumbSelected").attr("id").replace(/[^0-9]/g, "");
    } else {
      clone = null;
    }
  } else {
    clone = $(this).children("img").clone(true);
    $(this).addClass("gThumbSelected");
  }
  switch ($(".gThumbSelected").size()) {
  case 0:
    reset_edit_select();
    break;
  case 1:
    $("#gOrganizeFormThumb").empty();
    $("#gOrganizeFormThumb").append(clone);
    $("#gOrganizeFormNoImage").hide();
    $("#gOrganizeFormMultipleImages").hide();
    $("#gOrganizeFormThumb").show();
    $("#gOrganizeButtonPane").show();
    $.getJSON($("#gOrganizeFormInfo").attr("ref").replace("__ITEM_ID__", id), function(data) {
      $("#gOrganizeFormTitle").text(data.title);
      $("#gOrganizeFormOwner").text(data.owner);
      $("#gOrganizeFormDate").text(data.date);
      $("#gOrganizeFormDescription").text(data.description);
      $("#gOrganizeFormInfo").show();
    });
    break;
  default:
    $("#gOrganizeFormThumb").hide();
    $("#gOrganizeFormInfo").hide();
    $("#gOrganizeFormMultipleImages").show();
  }
  event.preventDefault();
}

function reset_edit_select() {
  $("#gOrganizeFormNoImage").show();
  $("#gOrganizeFormThumb").hide();
  $("#gOrganizeFormMultipleImages").hide();
  $("#gOrganizeButtonPane").hide();
  $("#gOrganizeFormInfo").hide();
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
    var url_header = $("#gDialog #hd").attr("ref").replace("__ITEM_ID__", item_id);
    $.getJSON(url_header, function(data) {
      $("#gOrganizeAlbumTitle").text(data.title);
      $("#gOrganizeAlbumDescription").text(data.description);
    });
    reset_edit_select();
    retrieve_micro_thumbs();
  }
  event.preventDefault();
}

function organize_dialog_init() {
  var size = viewport_size();
  height = size.height() - 100;
  var width = size.width() - 100;

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
  height -= $("#gMicroThumbContainer").position().top;
  height -= $("#gDialog #ft").height();
  height = Math.round(height);

  $("#gMicroThumbContainer").height(height);
  $("#gOrganizeTreeContainer").height(height);
  $("#gOrganizeEditContainer").height(height);

  $(".gOrganizeBranch .ui-icon").click(organize_toggle_children);
  $(".gBranchText").click(organize_open_folder);
  retrieve_micro_thumbs(item_id);
  //showLoading("#gDialog");
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
