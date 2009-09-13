(function($) {
   /**
    * @todo Move inline CSS out to external style sheet (theme style sheet)
    */
   $.gallery_show_full_size = function(image_url, image_width, image_height) {
     var width = $(document).width();
     var height = $(document).height();
     var size = $.gallery_get_viewport_size();

     $("body").append('<div id="gFullsizeOverlay" class="ui-dialog-overlay" ' +
		      'style="border: none; margin: 0; padding: 0; background-color: #000; ' +
		      'position: absolute; top: 0px; left: 0px; ' +
		      'width: ' + width + 'px; height: ' + height + 'px;' +
                      ' opacity: 0.7; filter: alpha(opacity=70);' +
		      '-moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; ' +
		      '-moz-background-inline-policy: -moz-initial; z-index: 1001;"> </div>');

     var image_size = $.gallery_auto_fit_window(image_width, image_height);

     $("body").append('<div id="gFullsize" class="ui-dialog ui-widget" ' +
		      'style="overflow: hidden; display: block; ' +
		      'position: absolute; z-index: 1002; outline-color: -moz-use-text-color; ' +
		      'outline-style: none; outline-width: 0px; ' +
		      'height: ' + image_size.height + 'px; ' +
		      'width: ' + image_size.width + 'px; ' +
		      'top: ' + image_size.top + 'px; left: ' + image_size.left + 'px;">' +
		      '<img id="gFullSizeImage" src="' + image_url + '"' +
		      'height="' + image_size.height + '" width="' + image_size.width + '"/></div>');

     $().click(function() {
       $("#gFullsizeOverlay*").remove();
       $("#gFullsize").remove();
     });
     $().bind("keypress", function() {
       $("#gFullsizeOverlay*").remove();
       $("#gFullsize").remove();
     });
     $(window).resize(function() {
       $("#gFullsizeOverlay").width($(document).width()).height($(document).height());
       image_size = $.gallery_auto_fit_window(image_width, image_height);
       $("#gFullsize").height(image_size.height)
	 .width(image_size.width)
	 .css("top", image_size.top)
	 .css("left", image_size.left);
       $("#gFullSizeImage").height(image_size.height).width(image_size.width);
     });
   };
})(jQuery);
