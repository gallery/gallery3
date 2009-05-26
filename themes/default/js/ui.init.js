/**
 * Initialize jQuery UI and Plugin elements
 *
 * @todo Standardize how elements requiring listeners are handled
 *        http://docs.jquery.com/Events/live
 */

var shortForms = new Array(
  "#gQuickSearchForm",
  "#gAddTagForm",
  "#gSearchForm"
);

$(document).ready(function() {
  
  // Initialize Superfish menus
  $("ul.gMenu").addClass("sf-menu");
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity:'show',
      height:'show'
    },
    speed: 'fast'
  });
  $("#gSiteMenu").css("display", "block");

  // Initialize status message effects
  $("#gMessage li").showMessage();

  // Initialize dialogs
  $(".gMenuLink").addClass("gDialogLink");
  $("#gLoginLink").addClass("gDialogLink");
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", handleDialogEvent);
  }

  // Initialize view menu
  if ($("#gViewMenu").length) {
    $("#gViewMenu ul").removeClass("gMenu").removeClass("sf-menu");
    $("#gViewMenu a").addClass("ui-icon");
  }

  // Initialize short forms
  handleShortFormEvent(shortForms);
  $(".gShortForm input[type=text]").addClass("ui-corner-left");
  $(".gShortForm input[type=submit]").addClass("ui-state-default ui-corner-right");

  // Apply jQuery UI button css to submit inputs
  $("input[type=submit]:not(.gShortForm input)").addClass("ui-state-default ui-corner-all");

  // Album view only
  if ($("#gAlbumGrid").length) {
    // Vertical align thumbnails/metadata in album grid
    $(".gItem").vAlign();
  }

  // Photo/Item item view only
  if ($("#gItem").length) {
    // Ensure that sized image versions
    // fit inside their container
    sizedImage();

    // Collapse comments form, insert button to expand
    if ($("#gAddCommentForm").length) {
      var showCommentForm = '<a href="#add_comment_form" class="showCommentForm gButtonLink ui-corner-all ui-icon-left ui-state-default right"><span class="ui-icon ui-icon-comment"></span>Add a comment</a>';
      $("#gAddCommentForm").hide();
      $("#gComments").prepend(showCommentForm);
      $(".showCommentForm").click(function(){
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
