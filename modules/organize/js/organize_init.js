$("document").ready(function() {
  $("#gOrganizeLink").click(function(event) {
    event.preventDefault();
    var href = event.target.href;

    $("body").append('<div id="gDialog"></div>');

    $("#gDialog").dialog({
      autoOpen: false,
      autoResize: false,
      modal: true,
      resizable: true,
      close: closeDialog
    });

    //showLoading("#gDialog");

    $.get(href, function(data) {
      $("#gDialog").html(data);
    });
    return false;
  });
});

