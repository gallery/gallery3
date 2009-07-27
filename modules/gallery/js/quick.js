$(document).ready(function() {
  if ($("#gAlbumGrid").length) {
    // @todo Add quick edit pane for album (meta, move, permissions, delete)
    $(".gItem").hover(show_quick, function() {});
  }
  if ($("#gPhoto").length) {
    $("#gPhoto").hover(show_quick, function() {});
  }
});

var show_quick = function() {
  var cont = $(this);
  var quick = $(this).find(".gQuick");
  var img = cont.find(".gThumbnail,.gResize");
  cont.find(".gQuickPane").remove();
  cont.append("<div class=\"gQuickPane\"></div>");
  cont.find(".gQuickPane").hide();
  cont.hover(function() {}, function() { cont.find(".gQuickPane").remove(); });
  $.get(
    quick.attr("href"),
    {},
    function(data, textStatus) {
      cont.find(".gQuickPane").html(data).slideDown("fast");
      $(".ui-state-default").hover(
        function() {
          $(this).addClass("ui-state-hover");
        },
        function() {
          $(this).removeClass("ui-state-hover");
        }
      );
      cont.find(".gQuickPane a:not(.options)").click(function(e) {
        e.preventDefault();
        quick_do(cont, $(this), img);
      });
      cont.find(".gQuickPane a.options").click(function(e) {
        e.preventDefault();
        cont.find(".gQuickPaneOptions").slideToggle("fast");
      });
    }
  );
};

var quick_do = function(cont, pane, img) {
  if (pane.hasClass("ui-state-disabled")) {
    return false;
  }
  if (pane.hasClass("gDialogLink")) {
    openDialog(pane);
  } else {
    img.css("opacity", "0.1");
    cont.addClass("gLoadingLarge");
    $.ajax({
      type: "GET",
      url: pane.attr("href"),
      dataType: "json",
      success: function(data) {
        img.css("opacity", "1");
        cont.removeClass("gLoadingLarge");
        if (data.src) {
          img.attr("width", data.width);
          img.attr("height", data.height);
          img.attr("src", data.src);
          if (data.height > data.width) {
            img.css("margin-top", -32);
          } else {
            img.css("margin-top", 0);
          }
        } else if (data.location) {
          $.gallery_location(data.location);
        } else if (data.reload) {
          $.gallery_reload();
        }
      }
    });
  }
  return false;
};
