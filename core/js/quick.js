$(document).ready(function() {
  $(".gQuick").hover(show_quick, function() {});
});

var show_quick = function() {
  var quick = $(this);
  $("#gQuickPane").remove();
  quick.append("<div id=\"gQuickPane\"></div>");
  var img = quick.find("img");
  var pos = img.position();
  $("#gQuickPane").css({
    "position": "absolute",
    "top": pos.top,
    "left": pos.left,
    "width": img.innerWidth() + 1,
    "height": 32
  });
  quick.hover(function() { }, hide_quick);
  $.get(
    quick.attr("href"),
    {},
    function(data, textStatus) {
      $("#gQuickPane").html(data);
      $("#gQuickPane div").click(function() {
        quick_do(quick, $(this), img);
      });
    }
  );
};

var quick_do = function(quick, pane, img) {
  img.css("opacity", "0.2");
  if (pane.hasClass("gDialogLink")) {
    openDialog(pane);
  } else {
    quick.addClass("gLoadingLarge");
    $.ajax({
      type: "GET",
      url: pane.attr("href"),
      dataType: "json",
      success: function(data) {
	img.css("opacity", "1");
	img.attr("width", data.width);
	img.attr("height", data.height);
	img.attr("src", data.src);
	var pos = img.position();
	quick.removeClass("gLoadingLarge");
	$("#gQuickPane").css({
	  "position": "absolute",
	  "top": pos.top,
	  "left": pos.left,
	  "width": img.innerWidth() + 1,
	  "height": 32
	});
      }
    });
  }
};

var hide_quick = function() {
  $("#gQuickPane").remove();
};
