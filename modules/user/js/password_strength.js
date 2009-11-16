(function($) {
   // Based on the Password Strength Indictor By Benjamin Sterling
   // http://benjaminsterling.com/password-strength-indicator-and-generator/
   $.widget("ui.user_password_strength",  {
     _init: function() {
       var self = this;
       $(this.element).keyup(function() {
	 var strength = self.calculateStrength (this.value);
	 var index = Math.min(Math.floor( strength / 10 ), 10);
         $("#g-password-gauge")
           .removeAttr('class')
	   .addClass( "g-password-strength0" )
	   .addClass( self.options.classes[ index ] );
       }).after("<div id='g-password-gauge' class='g-password-strength0'></div>");
     },

     calculateStrength: function(value) {
       // Factor in the length of the password
       var strength = Math.min(5, value.length) * 10 - 20;
       // Factor in the number of numbers
       strength += Math.min(3, value.length - value.replace(/[0-9]/g,"").length) * 10;
       // Factor in the number of non word characters
       strength += Math.min(3, value.length - value.replace(/\W/g,"").length) * 15;
       // Factor in the number of Upper case letters
       strength += Math.min(3, value.length - value.replace(/[A-Z]/g,"").length) * 10;

       // Normalizxe between 0 and 100
       return Math.max(0, Math.min(100, strength));
     }
   });
   $.extend($.ui.user_password_strength,  {
     defaults: {
	classes : ['g-password-strength10', 'g-password-strength20', 'g-password-strength30',
                   'g-password-strength40', 'g-password-strength50', 'g-password-strength60',
                   'g-password-strength70',' g-password-strength80',' g-password-strength90',
                   'g-password-strength100']
     }
   });
 })(jQuery);
