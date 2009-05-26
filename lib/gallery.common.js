/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
(function () {
  $.fn.showMessage = function(message) {
    return this.each(function(i){
      $(this).effect("highlight", {"color": "white"}, 3000);
      $(this).animate({opacity: 1.0}, 6000);
      $(this).fadeOut("slow");
    });
  };
})(jQuery);

// Vertically align a block element's content
(function () {
  $.fn.vAlign = function(container) {
    return this.each(function(i){
      if (container == null) {
        container = 'div';
      }
      $(this).html("<" + container + ">" + $(this).html() + "</" + container + ">");
      var el = $(this).children(container + ":first");
      var elh = $(el).height();
      var ph = $(this).height();
      var nh = (ph - elh) / 2;
      $(el).css('margin-top', nh);
    });
  };
})(jQuery);
