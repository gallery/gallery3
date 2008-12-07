/**
 * Initialize jQuery UI and Plugin elements
 */
$("document").ready(function() {

  /**
   * Reduce width of sized photo if it is wider than its parent container
   * 
   * @requires jquery.dimensions
   */
  if ($("#gItem").length) {
    var containerWidth = $("#gItem").width();
    var oPhoto = $("#gItem img").filter(function() {
      return this.id.match(/gPhotoID-/);
    })
    if (containerWidth < oPhoto.width()) {
      var proportion = containerWidth / oPhoto.width();
      oPhoto.width(containerWidth);
      oPhoto.height(proportion * oPhoto.height());
    }
  }

  /**
   * Attach event listeners to open modal dialogs
   */
  var dialogLinks = $(".gDialogLink");
  for (var i=0; i < dialogLinks.length; i++) {
    $(dialogLinks[i]).bind("click", {element: dialogLinks[i]}, handleDialogEvent);
  };

});

/**
 * Fire openDialog() and prevent links from opening
 * 
 * @see openDialog()
 */
function handleDialogEvent(event) {
  openDialog(event.data.element);
  event.preventDefault();
}

/**
 * Display modal dialogs, populate dialog with trigger link's title and href	
 * 
 * @requires ui.core
 * @requires ui.draggable
 * @requires ui.resizable
 * @requires ui.dialog
 * @see handleDialogEvent()
 * 
 * @todo Set dialog attributes dynamically (width, height, drag, resize)
 */
function openDialog(element) {
  var url = $(element).attr("href");
  var dialog = '<div id="gDialog"></div>';
  $("body").append(dialog);
  $("#gDialog").dialog({
    draggable: true,
    height: '400px',
    modal: true,
    overlay: {
      opacity: 0.7,
      background: "black"
    },
    resizable: true,
    title: $(element).attr("title"),
    width: '500px',
  });
  $("#gDialog").html(url);
  $.get(url, function(data) {
    $("#gDialog").html(data);
  });	
  return false;
}
