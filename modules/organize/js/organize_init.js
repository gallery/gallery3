$("document").ready(function() {
  $("#gOrganizeLink").click(function(event) {
    event.preventDefault();
    var href = event.target.href;

    $("body").append('<div id="gDialog"></div>');

    $("#gDialog").dialog({
      autoOpen: false,
      autoResize: false,
      modal: true,
      resizable: false,
      close: function () {
        $("#gDialog").trigger("organize_close");
        $("#gDialog").dialog("destroy").remove();
      },
      zIndex: 75
    });

    //$.gallery_show_loading("#gDialog");

    $.get(href, function(data) {
      $("#gDialog").html(data);
    });
    return false;
  });
});


