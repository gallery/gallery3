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
  $.fn.gallery_fit_photo = function() {
    return this.each(function(i) {
      var container_width = $(this).width();
      var photo = $(this).gallery_get_photo();
      var photo_width = photo.width();
      if (container_width < photo_width) {
        var proportion = container_width / photo_width;
        photo.width(container_width);
        photo.height(proportion * photo.height());
      }
    });
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
  };

  /**
   * Get the sum of an element's height, margin-top, and margin-bottom
   * @param elementID the element's ID
   * @return int
   */
  $.fn.gallery_height = function() {
    var ht = $(this).height();
    var mt = parseInt($(this).css("margin-top"));
    var mb = parseInt($(this).css("margin-bottom"));
    return ht + parseInt(mt) + parseInt(mb);
  };

  // Add hover state to buttons
  $.fn.gallery_hover_init = function() {
    $(".ui-state-default").hover(
      function(){
        $(this).addClass("ui-state-hover");
      },
      function(){
        $(this).removeClass("ui-state-hover");
      }
    );
  };

  // Ajax handler for replacing an image, used in Ajax thumbnail rotation
  $.gallery_replace_image = function(data, thumb) {
    $(thumb).attr({src: data.src, width: data.width, height: data.height});
  };

  $.fn.gallery_context_menu = function() {
    var in_progress = 0;
    $(".gContextMenu *").removeAttr('title');
    $(".gContextMenu ul").hide();
    $(".gContextMenu").hover(
      function() {
        if (in_progress == 0) {
          $(this).find("ul").slideDown("fast", function() { in_progress = 1; });
          $(this).find(".gDialogLink").gallery_dialog();
          $(this).find(".gAjaxLink").gallery_ajax();
        }
      },
      function() {
        $(this).find("ul").slideUp("slow", function() { in_progress = 0; });
      }
    );
  };

  $.gallery_auto_fit_window = function(imageWidth, imageHeight) {
    var size = $.gallery_get_viewport_size();
    var width = size.width() - 6,
        height = size.height() - 6;

      var ratio = width / imageWidth;
      imageWidth *= ratio;
      imageHeight *= ratio;

      /* after scaling the width, check that the height fits */
      if (imageHeight > height) {
        ratio = height / imageHeight;
        imageWidth *= ratio;
        imageHeight *= ratio;
      }

      // handle the case where the calculation is almost zero (2.14e-14)
      return {
        top: Number((height - imageHeight) / 2),
        left: Number((width - imageWidth) / 2),
        width: Number(imageWidth),
        height: Number(imageHeight)
      };
  };

})(jQuery);
