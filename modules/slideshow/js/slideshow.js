(function($) {
  $.gallery_slideshow = {
    interval: 4000,
    timer: null,
    init: function(data) {
      var self = this;
      var size = $.gallery_get_viewport_size();
      $("body").append(
        '<div id="gSlideshowOverlay" class="ui-dialog-overlay"></div>' +
        '<div id="gSlideshowButtonPanel"><div class="ui-corner-all">' +
        '<a id="gSlideshowPrevious" href="javascript: $.gallery_slideshow.previous()" />' +
        '<a id="gSlideshowPause" href="javascript: $.gallery_slideshow.pause()" />' +
        '<a id="gSlideshowResume" href="javascript: $.gallery_slideshow.resume()" />' +
        '<a id="gSlideshowNext" href="javascript: $.gallery_slideshow.next()" />' +
        '<a id="gSlideshowClose" href="javascript: $.gallery_slideshow.close()" />' +
        '</div></div>' +
        '<div id="gSlideShowImages"></div>');
      $().bind("mousemove", $.gallery_slideshow._mouse_over);

      // array of {url: xxx, width: nnn, height: nnn}
      for (var i=0; i < data.length; i++) {
        var position = $.gallery_slideshow._autofit_image(data[i].width, data[i].height);
        $("#gSlideShowImages").append(
            '<img id="img_' + i + '" src="' + data[i].url + '" width="' + position.width + 'px" ' +
            'height="' + position.height + 'px" style="position: absolute; top: ' + position.top +
            'px; left: ' + position.left + 'px;" />');
        if (i == 0) {
          $("#gSlideShowImages #img_0").load(function() {
            $.gallery_slideshow._show_image(null);
            setTimeout(function() {$("#gSlideshowButtonPanel").hide();},
                       $.gallery_slideshow.interval);
          });
        }
      }
    },

    close: function(event) {
      $("#gSlideShowImages img.gSlideShowing").toggle();
      $.gallery_slideshow.pause();
      $("#gSlideshowOverlay").remove();
      $("#gSlideShowImages").remove();
      $("#gSlideshowButtonPanel").remove();
      $().unbind("mousemove", $.gallery_slideshow._mouse_over);
    },

    previous: function() {
      $.gallery_slideshow.pause();
      var next_image = $("#gSlideShowImages img.gSlideShowing").prev();
      if (next_image.length == 0) {
        next_image = $("#gSlideShowImages img:last");
      }
      $.gallery_slideshow._show_image(next_image);
    },

    next: function() {
      $.gallery_slideshow.pause();
      var next_image = $("#gSlideShowImages img.gSlideShowing").next();
      if (next_image.length == 0) {
        next_image = $("#gSlideShowImages img:first");
      }
      $.gallery_slideshow._show_image(next_image);
    },

    pause: function() {
      if ($.gallery_slideshow.timer) {
        $("#gSlideshowPause").toggle();
        $("#gSlideshowResume").toggle();
        clearTimeout($.gallery_slideshow.timer);
        $.gallery_slideshow.timer = null;
      }
    },

    resume: function() {
      $("#gSlideshowPause").toggle();
      $("#gSlideshowResume").toggle();
      $.gallery_slideshow._show_image(null);
    },

    _mouse_over: function(event) {
      var size = $.gallery_get_viewport_size();
      if (event.pageY < size.height() && size.height() - 100 <= event.pageY) {
        $("#gSlideshowButtonPanel").show();
      } else {
        $("#gSlideshowButtonPanel").hide();
      }
    },

    _autofit_image: function(imageWidth, imageHeight) {
      var size = $.gallery_get_viewport_size();

      var ratio = size.width() / imageWidth;
      imageWidth *= ratio;
      imageHeight *= ratio;

      /* after scaling the width, check that the height fits */
      if (imageHeight > size.height()) {
	ratio = size.height() / imageHeight;
	imageWidth *= ratio;
	imageHeight *= ratio;
      }

      // handle the case where the calculation is almost zero (2.14e-14)
      return {
	top: Number((size.height() - imageHeight) / 2),
	left: Number((size.width() - imageWidth) / 2),
	width: Number(imageWidth),
	height: Number(imageHeight)
      };
    },

    // If next_image is not null, then this a call from prev or next
    _show_image: function(next_image) {
      var reset_timer = false;
      if (next_image == null) {
        next_image = $("#gSlideShowImages img.gSlideShowing").next();
        if (next_image.length == 0) {
          next_image = $("#gSlideShowImages img:first");
        }
        reset_timer = true;
      }
      next_image.addClass("gSlideShowNextImage");

      var zIndex = parseInt(next_image.css("zIndex"));
      next_image.css("zIndex", zIndex + 1);
      next_image.fadeIn("slow", function() {
        var current = $("#gSlideShowImages img.gSlideShowing");
        if (current.length) {
          current.hide();
          current.removeClass("gSlideShowing");
        }
        $(".gSlideShowNextImage").addClass("gSlideShowing").removeClass("gSlideShowNextImage")
          .css("zIndex", zIndex);
        if (reset_timer) {
          $.gallery_slideshow.timer =
            setTimeout(function() {$.gallery_slideshow._show_image(null);},
                       $.gallery_slideshow.interval);
        }
      });
    }
  };
})(jQuery);

$(document).ready(function() {
  $("#gSlideshowLink").click(function(event) {
    event.preventDefault();
    $.get($(event.currentTarget).attr("href"), {}, $.gallery_slideshow.init, "json");
    return false;
  });
});
