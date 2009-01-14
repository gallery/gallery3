$(document).ready(function() {
  $(".gItem").hover(show_quick, function() {});
});

var show_quick = function() {
  var cont = $(this);
  var quick = $(this).find(".gQuick");
  $("#gQuickPane").remove();
  cont.append("<div id=\"gQuickPane\"></div>");
  var img = cont.find(".gThumbnail");
  var pos = cont.position();
  $("#gQuickPane").css({
    "position": "absolute",
    "top": pos.top,
    "left": pos.left,
    "width": cont.innerWidth(),
    "height": 32
  });
  cont.hover(function() { }, hide_quick);
  $.get(
    quick.attr("href"),
    {},
    function(data, textStatus) {
      $("#gQuickPane").html(data);
      $("#gQuickPane a").click(function(e) {
        e.preventDefault();
        quick_do(cont, $(this), img);
      });
    }
  );
};

var quick_do = function(cont, pane, img) {
  if (pane.hasClass("gDialogLink")) {
    openDialog(pane, function() { window.location.reload(); });
  } else {
    img.css("opacity", "0.1");
    cont.addClass("gLoadingLarge");
    $.ajax({
      type: "GET",
      url: pane.attr("href"),
      dataType: "json",
      success: function(data) {
		img.css("opacity", "1");
		img.attr("width", data.width);
		img.attr("height", data.height);
		img.attr("src", data.src);
		if (data.height > data.width) {
		  img.css("margin-top", -$("#gQuickPane").height());
		} else {
		  img.css("margin-top", 0);			
		}
		cont.removeClass("gLoadingLarge");
      }
    });
  }
  return false;
};

var hide_quick = function() {
  $("#gQuickPane").remove();
};
