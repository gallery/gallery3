/**
 * Initialize UI elements
 * 
 * @todo Write helpers to grab all jQuery UI components by class and initialize
 */

$("document").ready(function() {

  /**
   * Reset width of sized photos wider than their 
   * parent container so that they fit
   */
  if ($("#gItem").innerWidth()) {
    var containerWidth = $("#gItem").innerWidth();
    var oPhoto = $("#gItem img").filter(function() {
      return this.id.match(/gPhotoID-/);
    })
    if (containerWidth < oPhoto.width()) {
      var proportion = containerWidth / oPhoto.width();
      oPhoto.width(containerWidth);
      oPhoto.height(proportion * oPhoto.height());
    }
  }

});
