/**
 * Initialize jQuery UI and Plugin elements
 *
 * @todo Standardize how elements requiring listeners are identified (class or id)
 */

var shortForms = new Array(
  "#gQuickSearchForm",
  "#gAddTagForm",
  "#gSearchForm"
);

$(document).ready(function() {
  // Initialize menus
  $("ul.gMenu").addClass("sf-menu");

  // Superfish menu options
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity:'show',
      height:'show'
    },
    speed: 'fast'
  });

  // Apply jQuery UI button css to submit inputs
  $("input[type=submit]").addClass("ui-state-default ui-corner-all");
  
  // Round view menu buttons
  if ($("#gViewMenu").length) {
    $("#gViewMenu ul").removeClass("gMenu").removeClass("sf-menu");
    $("#gViewMenu a").addClass("ui-icon ui-state-default");
    $("#gViewMenu ul li:first a").addClass("ui-corner-left");
    $("#gViewMenu ul li:last a").addClass("ui-corner-right");
  }

  // Short forms
  handleShortFormEvent(shortForms);
  if ($(".gShortForm").length) {
    $(".gShortForm input[type=text]").addClass("ui-corner-left");
    $(".gShortForm input[type=submit]").addClass("ui-corner-right");
  }
  
  // Album view only
  if ($("#gAlbumGrid").length) {
    // Vertical align thumbnails/metadata in album grid
    $('.gItem').vAlign();
  }

  // Photo/Item item view only
  if ($("#gItem").length) {
    // Ensure that sized image versions
    // fit inside their container
    sizedImage();

    // Collapse comments form, insert button to expand
    if ($("#gAddCommentForm").length) {
      var showCommentForm = '<a href="#add_comment_form" id="showCommentForm" class="gButtonLink ui-corner-all ui-icon-left ui-state-default right"><span class="ui-icon ui-icon-comment"></span>Add a comment</a>';
      $("#gAddCommentForm").hide();
      $("#gComments").prepend(showCommentForm);
      $("#showCommentForm").click(function(){
        $("#gAddCommentForm").show(1000);
      });
    }

    // Add scroll effect for links to named anchors
    $.localScroll({
      queue: true,
      duration: 1000,
      hash: true
    });

  }

  // Apply modal dialogs
  $(".gMenuLink").addClass("gDialogLink");
  $("#gLoginLink").addClass("gDialogLink");
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", handleDialogEvent);
  }

  // Add hover state for buttons
  $(".ui-state-default").hover(
	  function(){
		  $(this).addClass("ui-state-hover");
	  },
	  function(){
		  $(this).removeClass("ui-state-hover");
	  }
  );

});

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

/**
 * Reduce width of sized photo if it's wider than its parent container
 */
function sizedImage() {
  var containerWidth = $("#gItem").width();
  var oPhoto = $("#gItem img").filter(function() {
    return this.id.match(/gPhotoId-/);
  });
  if (containerWidth < oPhoto.width()) {
    var proportion = containerWidth / oPhoto.width();
    oPhoto.width(containerWidth);
    oPhoto.height(proportion * oPhoto.height());
  }
}

/**
 * Handle initialization of all short forms
 *
 * @param shortForms array Array of short form IDs
 */
function handleShortFormEvent(shortForms) {
  for (var i in shortForms) {
    shortFormInit(shortForms[i]);
  }
}

/**
 * Initialize a short form. Short forms may contain only one text input.
 *
 * @param formID string The form's ID, including #
 */
function shortFormInit(formID) {
  $(formID).addClass("gShortForm");

  // Get the input ID and it's label text
  var labelValue = $(formID + " label:first").html();
  var inputID = "#" + $(formID + " input[type='text']:first").attr("id");

  // Set the input value equal to label text
  $(inputID).val(labelValue);

  // Attach event listeners to the input
  $(inputID).bind("focus blur", function(e){
    var eLabelVal = $(this).siblings("label").html();
    var eInputVal = $(this).val();

    // Empty input value if it equals it's label
    if (eLabelVal == eInputVal) {
        $(this).val("");
    // Reset the input value if it's empty
    } else if ($(this).val() == "") {
      $(this).val(eLabelVal);
    }
  });
}
