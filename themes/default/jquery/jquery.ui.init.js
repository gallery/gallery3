/**
 * Apply jQuery UI components
 * 
 * @todo Write helpers to grab all jQuery UI components by class and initialize
 */

$(function(){

  //accordion
  $('#gSettingsGroup-1').accordion({
    header: ".ui-accordion-header",
    clearStyle: true
  });
  
  //tabs
  $('#gSettings ul').tabs();

});
