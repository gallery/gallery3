(function($) {
  $.widget("ui.gallery_ajax",  {
    _init: function() {
      this.element.click(function(event) {
        eval("var ajax_handler = " + $(event.currentTarget).attr("ajax_handler"));
        $.get($(event.currentTarget).attr("href"), function(data) {
          eval("var data = " + data);
          ajax_handler(data);
	});
        event.preventDefault();
        return false;
      });
    }
  });
})(jQuery);
