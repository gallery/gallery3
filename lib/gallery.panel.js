/**
 * Fire togglePanel() and prevent links from opening
 * @see openDialog()
 */
function handlePanelEvent(event) {
  togglePanel(event.currentTarget);
  event.preventDefault();
}

function togglePanel(element, on_success) {
  var parent = $(element).parent().parent();
  var sHref = $(element).attr("href");
  var parentClass = $(parent).attr("class");
  var ePanel = "<tr id=\"gPanel\"><td colspan=\"6\"></td></tr>";

  if ($("#gPanel").length) {
    $("#gPanel").slideUp("slow");
    $("#gPanel *").remove();
    $("#gPanel").remove();
    if ($(element).attr("orig_text")) {
       $(element).children(".gButtonText").text($(element).attr("orig_text"));
    }
    console.log("Removing existing #gPanel");
    //togglePanel(element, on_success);
  } else {
    console.log("Adding #gPanel");
    $(parent).after(ePanel);
    //showLoading("#here");
    $("#gPanel td").html(sHref);
    $("#gPanel").addClass(parentClass).show().slideDown("slow");
    $.get(sHref, function(data) {
      $("#gPanel td").html(data);
      ajaxify_panel = function() {
        $("#gPanel td form").ajaxForm({
          dataType: "json",
          success: function(data) {
            if (data.form) {
              $("#gPanel td form").replaceWith(data.form);
              ajaxify_panel();
            }
            if (data.result == "success") {
              if (on_success) {
                on_success();
              } else if (data.location) {
                window.location = data.location;
              } else {
                window.location.reload();
              }
            }
          }
        });
        if ($("#gPanel td").hasClass("gLoadingLarge")) {
          showLoading("#gPanel td");
        }
      };
      ajaxify_panel();
      if ($(element).attr("open_text")) {
        $(element).attr("orig_text", $(element).children(".gButtonText").text());
        $(element).children(".gButtonText").text($(element).attr("open_text"));
      }
    });
  }
  return false;
}
