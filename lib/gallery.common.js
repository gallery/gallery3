(function ($) {

  // Fade in action status message background color
  $.fn.gallery_show_message = function() {
    return this.each(function(i){
      $(this).hide().fadeIn(3000)
    });
  };

  // Make the height of all items the same as the tallest item  within the set
  $.fn.equal_heights = function() {
    var tallest_height = 0;
    $(this).each(function(){
      if ($(this).height() > tallest_height) {
        tallest_height = $(this).height();
      }
    });
    return $(this).height(tallest_height);
  };

  // Vertically align a block element's content
  $.fn.gallery_valign = function(container) {
    return this.each(function(i){
      if (container == null) {
        container = 'div';
      }
      var el = $(this).find(".g-valign");
      if (!el.length) {
	$(this).html("<" + container + " class=\"g-valign\">" + $(this).html() +
		     "</" + container + ">");
	el = $(this).children(container + ".g-valign");
      }
      var elh = $(el).height();
      var ph = $(this).height();
      var nh = (ph - elh) / 2;
      if (nh < 1) { var nh = 0; }
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
   * @param elementID Target ID, including #, to apply .g-loading-size
   */
  $.fn.gallery_show_loading = function() {
    return this.each(function(i){
      var size;
      switch ($(this).attr("id")) {
      case "#g-dialog":
        case "#g-panel":
          size = "large";
        break;
      default:
        size = "small";
        break;
      }
      $(this).toggleClass("g-loading-" + size);
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
    var photo = $(this).find("img,object").filter(function() {
      return this.id.match(/g-(photo|movie)-id-\d+/);
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
  $.gallery_replace_image = function(data, img_selector) {
    $(img_selector).attr({src: data.src, width: data.width, height: data.height});
    $(img_selector).trigger("gallery.change");
  };

  // Initialize context menus
  $.fn.gallery_context_menu = function() {
    if ($(".g-context-menu li").length) {
      var hover_target = $(this).find(".g-context-menu");
      if (hover_target.attr("context_menu_initialized")) {
	return;
      }
      var list = $(hover_target).find("ul");
      hover_target.find("*").removeAttr('title');
      list.hide();
      hover_target.hover(
        function() {
          list.stop(false, true).slideDown("fast");
          $(this).find(".g-dialog-link").gallery_dialog();
          $(this).find(".g-ajax-link").gallery_ajax();
        },
        function() {
          list.stop(true, true).slideUp("slow");
        }
      );
      hover_target.attr("context_menu_initialized", 1);
    }
  };

  // Size a container to fit within the browser window
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
      top: Math.round((height - imageHeight) / 2),
      left: Math.round((width - imageWidth) / 2),
      width: Math.round(imageWidth),
      height: Math.round(imageHeight)
    };
  };

  // Initialize a short form. Short forms may contain only one text input.
  $.fn.gallery_short_form = function() {
    return this.each(function(i){
      var label = $(this).find("label:first");
      var input = $(this).find("input[type=text]:first");
      var button = $(this).find("input[type=submit]");

      $(".g-short-form").addClass("ui-helper-clearfix");

      // Place button's on the left for RTL languages
      if ($(".rtl").length) {
        $(".g-short-form input[type=text]").addClass("ui-corner-right");
        $(".g-short-form input[type=submit]").addClass("ui-state-default ui-corner-left");
      } else {
        $(".g-short-form input[type=text]").addClass("ui-corner-left");
        $(".g-short-form input[type=submit]").addClass("ui-state-default ui-corner-right");
      }

      // Set the input value equal to label text
      if (input.val() == "") {
        input.val(label.html());
        button.enable(false);
      }

      // Attach event listeners to the input
      input.bind("focus", function(e) {
        // Empty input value if it equals it's label
        if ($(this).val() == label.html()) {
          $(this).val("");
        }
        button.enable(true);
      });

      input.bind("blur", function(e){
        // Reset the input value if it's empty
        if ($(this).val() == "") {
          $(this).val(label.html());
          button.enable(false);
        }
      });
    });
  };

  // Augment jQuery autocomplete to expect the first response line to
  // be a <meta> tag that protects against UTF-7 attacks.
  $.fn.gallery_autocomplete = function(url, options) {
    // Drop the first response - it should be a meta tag
    options.parse = function(data) {
      var parsed = [];
      var rows = data.split("\n");
      if (rows[0].indexOf("<meta") == -1) {
        throw 'Missing <meta> tag in first line of autocomplete response';
      }
      rows.shift();  // drop <META> tag
      for (var i=0; i < rows.length; i++) {
        var row = $.trim(rows[i]);
        if (row) {
          row = row.split("|");
          parsed[parsed.length] = {
            data: row,
            value: row[0],
            result: row[0]
          };
        }
      }
      return parsed;
    };

    $(this).autocomplete(url, options);
  };

})(jQuery);
