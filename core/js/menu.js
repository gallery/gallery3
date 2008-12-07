$("document").ready(function() {
  $("#gSiteMenu ul:not(:first)").css("display", "none");
  $("#gSiteMenu li").mouseover(function (ev) {
    $(this).children("ul").css("display", "block");
    $(this).children("ul").find("li").css("clear", "both");

    this.dropdown_open = true;
  });
  $("#gSiteMenu li").mouseout(function (ev) {
    $(this).children("ul").css("display", "none");
    this.dropdown_open = false;
  });

  $("#gSiteMenu li a").click(function () {
    var href = $(this).attr("href");
    if (href == "#") {
      return false;
    } else if (href.match("^#") == "#") {
      alert("Display href: " + href.substring(1) + "in a popup");
      return false;
    }
    return true;
  });
});
