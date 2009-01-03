$(document).ready(function() {
  $("div.gQuickEdit").hover(show_quickedit, function() { });
});

var show_quickedit = function() {
  $("#gQuickEditPane").remove();
  $(this).append("<div id=\"gQuickEditPane\"></div>");
  var img = $(this).find("img");
  var pos = img.position();
  $("#gQuickEditPane").css({
    "position": "absolute",
    "top": pos.top,
    "left": pos.left,
    "width": img.innerWidth() + 1,
    "height": 32
  });
  $(this).hover(function() { }, hide_quickedit);
  $.get(
    $(this).attr("quickedit_link"),
    {},
    function(data, textStatus) {
      $("#gQuickEditPane").html(data);
      $("#gQuickEditPane div").click(function() {
	quickedit($(this).attr("quickedit_link"), img);
      });
    }
  );
};

var quickedit = function(url, img) {
  $.ajax({
    type: "GET",
    url: url,
    dataType: "json",
    success: function(data) {
      img.attr("width", data.width);
      img.attr("height", data.height);
      img.attr("src", data.src);
    }
  });
};

var hide_quickedit = function() {
  $("#gQuickEditPane").remove();
};
