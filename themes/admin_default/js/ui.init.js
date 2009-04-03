$(document).ready(function(){
  // Add Superfish menu class
  $("#gSiteAdminMenu ul.gMenu").addClass("sf-menu");
  $("ul.gMenu").addClass("sf-menu");

  // Superfish menu options
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity: 'show',
      height: 'show'
    },
    pathClass: 'current',
    speed: 'fast'
  });
  $("#gSiteAdminMenu").css("display", "block");

  // Apply modal dialogs
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", handleDialogEvent);
  }

  if ($("#gPhotoStream").length) {
    // Vertically align thumbs in photostream
    $('.gItem').vAlign();
  }

  // Apply jQuery UI button css to submit inputs
  $("input[type=submit]:not(.gShortForm input)").addClass("ui-state-default ui-corner-all");

  // Round view menu buttons
  if ($("#gAdminCommentsMenu").length) {
    $("#gAdminCommentsMenu ul").removeClass("gMenu").removeClass("sf-menu");
    $("#gAdminCommentsMenu").addClass("gButtonSet");
    $("#gAdminCommentsMenu a").addClass("gButtonLink ui-state-default");
    $("#gAdminCommentsMenu ul li:first a").addClass("ui-corner-left");
    $("#gAdminCommentsMenu ul li:last a").addClass("ui-corner-right");
  }

  // Apply hide/show functionality on user admin view
  var panelLinks = $(".gPanelLink");
  for (i=0; i<panelLinks.length; i++) {
    $(panelLinks[i]).bind("click", handlePanelEvent);
  }

  // Round corners
  $(".gSelected").addClass("ui-corner-all");
  $(".gAvailable .gBlock").addClass("ui-corner-all");
  $(".gUnavailable").addClass("ui-corner-all");

  // Add drop shadows
  $(".gSelected").dropShadow();

  // Add hover state for buttons
  $(".ui-state-default").hover(
    function() {
      $(this).addClass("ui-state-hover");
    },
    function() {
      $(this).removeClass("ui-state-hover");
    }
  );
});

function handlePanelEvent(event) {
  togglePanel(event.currentTarget);
  event.preventDefault();
}

function togglePanel(element, on_success) {
  var parent = $(element).parent().parent();
  var sHref = $(element).attr("href");
  var parentClass = $(parent).attr("class");
  var ePanel = '<tr id="gPanel"><td colspan="6"></td></tr>';

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

// Vertically align a block element's content
(function ($) {
  $.fn.vAlign = function(container) {
    return this.each(function(i){
      if (container == null) {
        container = 'div';
      }
      $(this).html("<" + container + ">" + $(this).html() + "</" + container + ">");
      var el = $(this).children(container + ":first");
      var elh = $(el).height();
      var ph = $(this).height();
      var nh = (ph - elh) / 2;
      $(el).css('margin-top', nh);
    });
  };
})(jQuery);



