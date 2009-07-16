$(document).ready(function() {
  $(".gDigibugPrintButton a").click(function(e) {
    e.preventDefault();
    return digibug_popup(e.currentTarget.href, { width: 800, height: 600 } );
  });

  $("#gDigibugLink").click(function(e) {
    e.preventDefault();
    return digibug_popup(e.currentTarget.href, { width: 800, height: 600 } );
  });
});

function digibug_popup(url, options) {
  options = $.extend({
    /* default options */
    width:      '800',
    height:     '600',
    target:     'dbPopWin',
    scrollbars: 'yes',
    resizable:  'no',
    menuBar:    'no',
    addressBar: 'yes'
  }, options);

  // center the window by default.
  if (!options.winY) {
    options.winY = screen.height / 2 - options.height / 2;
  };
  if (!options.winX) {
    options.winX = screen.width / 2 - options.width / 2;
  };

  open(
    url,
    options['target'],
    'width= '      + options.width +
    ',height='     + options.height +
    ',top='        + options.winY +
    ',left='       + options.winX +
    ',scrollbars=' + options.scrollbars +
    ',resizable='  + options.resizable +
    ',menubar='    + options.menuBar +
    ',location='   + options.addressBar
    );

  return false;

}
