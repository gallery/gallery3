(function ($) {
  $.fn.gallery_show_message = function(message) {
    return this.each(function(i){
      $(this).effect("highlight", {"color": "white"}, 3000);
      $(this).animate({opacity: 1.0}, 6000);
    });
  };

  // Vertically align a block element's content
  $.fn.gallery_valign = function(container) {
    return this.each(function(i){
      if (container == null) {
        container = 'div';
      }
      $(this).html("<" + container + " class=\"gValign\">" + $(this).html() + "</" + container + ">");
      var el = $(this).children(container + ".gValign");
      var elh = $(el).height();
      var ph = $(this).height();
      var nh = (ph - elh) / 2;
      $(el).css('margin-top', nh);
    });
  };

  // Get the viewport size
  $.gallery_get_viewport_size = function() {
    return {
      width : function() {
        return $(window).width();
      },
      height : function() {
        return $(window).height();
      }
    };
  };

  /**
   * Toggle the processing indicator, both large and small
   * @param elementID Target ID, including #, to apply .gLoadingSize
   */
  $.fn.gallery_show_loading = function() {
    return this.each(function(i){
      var size;
      switch ($(this).attr("id")) {
      case "#gDialog":
        case "#gPanel":
          size = "Large";
        break;
      default:
        size = "Small";
        break;
      }
      $(this).toggleClass("gLoading" + size);
    });
  };

  /**
   * Reduce the width of an image if it's wider than its parent container
   * @param elementID The image container's ID
   */
  $.fn.gallery_fit_image = function() {
    var container_width = $(this).width();
    var photo = $(this).gallery_get_photo();
    var photo_width = photo.width();
    if (container_width < photo_width) {
      var proportion = container_width / photo_width;
      photo.width(container_width);
      photo.height(proportion * photo.height());
    }
  };

  /**
   * Get a thumbnail or resize photo within a container
   * @param elementID The image container's ID
   * @return object
   */
  $.fn.gallery_get_photo = function() {
    var photo = $(this).find("img").filter(function() {
      return this.id.match(/gPhotoId-\d+/);
    });
    return photo;
  }

  /**
   * Get the sum of an element's height, margin-top, and margin-bottom
   * @param elementID the element's ID
   * @return int
   */
  $.fn.gallery_height = function() {
    var ht = $(this).height();
    var mt = $(this).css("margin-top").replace("px","");
    var mb = $(this).css("margin-bottom").replace("px","");
    return ht + parseInt(mt) + parseInt(mb);
  };

})(jQuery);

