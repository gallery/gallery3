$(document).ready(function() {
  $(".gDigibugPrintButton a").click(function(e) {
    e.preventDefault();
    queue_print(e);
  });
});

function queue_print(e) {
  var parent = e.currentTarget.parentNode;
  $(parent).addClass("gLoadingLarge");
  $.ajax({
    type: "GET",
    url: e.currentTarget.href,
    dataType: "json",
    success: function(data) {
      $(parent).removeClass("gLoadingLarge");
      if (data.location) {
        window.location = data.location;
      } else if (data.reload) {
        window.location.reload();
      }
    }
  });
};
