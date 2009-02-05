$(document).ready(function() {
  $("#gFullsizeLink").click(function() {
    var width = $(document).width();
    var height = $(document).height();

    $("body").append('<div id="gFullsizeOverlay" class="ui-dialog-overlay" ' +
		     'style="border-width: 0px; margin: 0px; padding: 0px; background: black ' +
		     'none repeat scroll 0% 0%; position: absolute; top: 0px; left: 0px; ' +
		     'width: ' + width + 'px; height: ' + height + 'px; opacity: 0.7; '  +
		     '-moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; ' +
		     '-moz-background-inline-policy: -moz-initial; z-index: 1001;"> </div>');

    var image_size = _auto_fit(fullsize_detail.width, fullsize_detail.height);

    $("body").append("<div id='gFullsize' class='ui-dialog ui-widget' " +
		     "style='overflow: hidden; display: block; " +
		     "position: absolute; z-index: 1002; outline-color: -moz-use-text-color; " +
		     "outline-style: none; outline-width: 0px; " +
		     "height: " + image_size.height + "px; " +
		     "width: " + image_size.width + "px; " +
		     "top: " + image_size.top + "px; left: " + image_size.left + "px;'>" +
		     "<img id='gFullSizeImage' src='" + fullsize_detail.url + "'" +
		     "height='" + image_size.height + "' width='" + image_size.width + "'/></div");

    $("#gFullsize").append("<div id='gFullsizeClose' style='z-index: 1003; position: absolute; right: 1em; top: 1em;'><img src='" + fullsize_detail.close + "' /></div>");
    $("#gFullsizeClose").click(function() {
      $("#gFullsizeOverlay*").remove();
      $("#gFullsize").remove();
    });
  });
});

/*
 * Calculate the size of the image panel based on the size of the image and the size of the
 * window.  Scale the image so the entire panel fits in the view port.
 */
function _auto_fit(imageWidth, imageHeight) {
  // ui-dialog gives a padding of 2 pixels
  var windowWidth = $(window).width() - 10;
  var windowHeight = $(window).height() - 10;

  /* If the width is greater then scale the image width first */
  if (imageWidth > windowWidth) {
    var ratio = windowWidth / imageWidth;
    imageWidth *= ratio;
    imageHeight *= ratio;
  }
  /* after scaling the width, check that the height fits */
  if (imageHeight > windowHeight) {
    var ratio = windowHeight / imageHeight;
    imageWidth *= ratio;
    imageHeight *= ratio;
  }

  return {top: (windowHeight - imageHeight) / 2, left: (windowWidth - imageWidth) / 2,
	  width: imageWidth, height: imageHeight};
}
