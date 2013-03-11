(function($) {
  $.gallery_show_full_size = function(image_url, image_width, image_height) {
    $("body").append('<div id="g-fullsize-overlay" class="ui-widget-overlay ui-front"></div>' +
                     '<div id="g-fullsize" class="ui-dialog ui-widget ui-front">' +
                       '<img id="g-fullsize-image" src="' + image_url + '"/>' +
                     '</div>');

    $(document).on("click keypress", function() {
      $("#g-fullsize-overlay*").remove();
      $("#g-fullsize").remove();
    });

    var size = $.gallery_get_viewport_size();
    var image_size;

    function update_image_size() {
      if (image_width >= size.width() - 6 || image_height >= size.height() - 6) {
        image_size = $.gallery_auto_fit_window(image_width, image_height);
      } else {
        image_size = {
          top: 12,
          left: Math.round((size.width() - image_width) / 2),
          width: Math.round(image_width),
          height: Math.round(image_height)
        };
      }
      $("#g-fullsize").height(image_size.height).width(image_size.width)
                      .css("top", image_size.top).css("left", image_size.left);
      $("#g-fullsize-image").height(image_size.height).width(image_size.width);
    }

    $(document).ready(update_image_size);
    $(window).resize(update_image_size);
  };
})(jQuery);
