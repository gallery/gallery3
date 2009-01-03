$(document).ready(function() {
  $("div.gQuickEdit").hover(show_quickedit, function() { });
});

var show_quickedit = function() {
  var quick_edit = $(this);
  $("#gQuickEditPane").remove();
  quick_edit.append("<div id=\"gQuickEditPane\"></div>");
  var img = quick_edit.find("img");
  var pos = img.position();
  $("#gQuickEditPane").css({
    "position": "absolute",
    "top": pos.top,
    "left": pos.left,
    "width": img.innerWidth() + 1,
    "height": 32
  });
  quick_edit.hover(function() { }, hide_quickedit);
  $.get(
    quick_edit.attr("quickedit_link"),
    {},
    function(data, textStatus) {
      $("#gQuickEditPane").html(data);
      $("#gQuickEditPane div").click(function() {
        quickedit(quick_edit, $(this), img);
      });
    }
  );
};

var quickedit = function(quick_edit, pane, img) {
  img.css("opacity", "0.2");
  quick_edit.addClass("gLoadingLarge");
  $.ajax({
    type: "GET",
    url: pane.attr("quickedit_link"),
    dataType: "json",
    success: function(data) {
      img.css("opacity", "1");
      img.attr("width", data.width);
      img.attr("height", data.height);
      img.attr("src", data.src);
      var pos = img.position();
      quick_edit.removeClass("gLoadingLarge");
      $("#gQuickEditPane").css({
	"position": "absolute",
	"top": pos.top,
	"left": pos.left,
	"width": img.innerWidth() + 1,
	"height": 32
      });
    }
  });
};

var hide_quickedit = function() {
  $("#gQuickEditPane").remove();
};
