(function($) {
  $.gallery_slideshow = {
    interval: 4000,
    timer: null,
    images: [],
    current: -1,
    init: function(data) {
      var self = this;
      var size = $.gallery_get_viewport_size();
      $("body").append(
        '<div id="gSlideshowOverlay" class="ui-dialog-overlay"></div>' +
        '<div class="gLoadingLarge" style="z-index; 2005; width:100%; height:100%">&nbsp</div>' +
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
        $("#gSlideShowImages").append(
            '<img id="img_' + i + '" src="' + data[i].url + '" width="' + data[i].width + 'px" ' +
            'height="' + data[i].height + 'px"  />');
        $("#gSlideShowImages #img_" + i).load(function() {
          $.gallery_slideshow.images.push("#" +$(this).attr("id"));
          if ($.gallery_slideshow.images.length == 1) {
            $.gallery_slideshow._show_image(null);
            setTimeout(function() {$("#gSlideshowButtonPanel").hide();},
                       $.gallery_slideshow.interval);
          }
        });
      };
      $(window).resize(function() {
        var size = $.gallery_get_viewport_size();
        $("#gSlideshowOverlay").width(size.width()).height(size.height());
        var current = $(".gSlideCurrent");
        var position = $.gallery_auto_fit_window(current.width(), current.height());
        $($.gallery_slideshow.images[$.gallery_slideshow.current].replace("img", "clone"))
          .height(position.height).width(position.width).css({
            top: position.top,
            left: position.left
          });
        });
      },

    close: function(event) {
      $.gallery_slideshow.pause();
      $($.gallery_slideshow.images[$.gallery_slideshow.current].replace("img", "clone")).remove();
      $("#gSlideshowOverlay").remove();
      $("#gSlideShowImages").remove();
      $("#gSlideshowButtonPanel").remove();
      $().unbind("mousemove", $.gallery_slideshow._mouse_over);
    },

    previous: function() {
      $.gallery_slideshow.pause();
      $.gallery_slideshow.current--;

      $.gallery_slideshow.current = --$.gallery_slideshow.current < 0 ?
        $.gallery_slideshow.images.length - 1 : $.gallery_slideshow.current;
      var next_image = $($.gallery_slideshow.images[$.gallery_slideshow.current]);
      $.gallery_slideshow._show_image(next_image);
    },

    next: function() {
      $.gallery_slideshow.pause();
      $.gallery_slideshow.current = ++$.gallery_slideshow.current % $.gallery_slideshow.images.length;
      var next_image = $($.gallery_slideshow.images[$.gallery_slideshow.current]);
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

    // If next_image is not null, then this a call from prev or next
    _show_image: function(next_image) {
      var reset_timer = false;
      var previous = $.gallery_slideshow.current;
      if (next_image == null) {
        $.gallery_slideshow.current = ++$.gallery_slideshow.current % $.gallery_slideshow.images.length;
        next_image = $($.gallery_slideshow.images[$.gallery_slideshow.current]);
        reset_timer = true;
      }
      var zIndex = parseInt(next_image.css("zIndex"));
      var position = $.gallery_auto_fit_window(next_image.width(), next_image.height());
      var clone = next_image.clone();

      clone.attr("id", next_image.attr("id").replace("img", "clone"));
      clone.height(position.height).width(position.width).css({
        marginTop: 0, marginLeft: 0, marginBottom: 0, marginRight: 0,
        display: "none",
        position: "absolute",
        zIndex: zIndex + 1,
        top: position.top + "px",
        left: position.left + "px"
      });

      $("body").append(clone);
      clone.fadeIn("slow", function() {
        if (previous >= 0) {
          $($.gallery_slideshow.images[previous].replace("img", "clone")).remove();
        }
        clone.css("zIndex", zIndex);
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
