$(document).ready(function(){

  // Add Superfish menu class
  $("#gSiteAdminMenu ul.gMenu").addClass("sf-menu sf-navbar");
  
  // Superfish menu options
  $('ul.sf-menu').superfish({
    delay: 500,
    animation: {
      opacity:'show',
      height:'show'
    },
    pathClass:  'current',
    speed: 'fast'
  });

});
