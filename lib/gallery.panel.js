(function($) {
   $.widget("ui.gallery_panel", {
     _init: function() {
       var self = this;
       this.element.click(function(event) {
         event.preventDefault();
         var element = event.currentTarget;
         var parent = $(element).parent().parent();
         var sHref = $(element).attr("href");
         var parentClass = $(parent).attr("class");
         var ePanel = "<tr id=\"gPanel\"><td colspan=\"6\"></td></tr>";

         if ($("#gPanel").length) {
           $("#gPanel").slideUp("slow").remove();
           if ($(element).attr("orig_text")) {
             $(element).children(".gButtonText").text($(element).attr("orig_text"));
           }
         } else {
           $(parent).after(ePanel);
           $("#gPanel td").html(sHref);
           $.get(sHref, function(data) {
             $("#gPanel td").html(data);
             self._ajaxify_panel();
             if ($(element).attr("open_text")) {
               $(element).attr("orig_text", $(element).children(".gButtonText").text());
               $(element).children(".gButtonText").text($(element).attr("open_text"));
             }
             $("#gPanel").addClass(parentClass).show().slideDown("slow");
           });
         }
         return false;
       });
     },

     _ajaxify_panel: function () {
       var self = this;
       $("#gPanel td form").ajaxForm({
         dataType: "json",
         success: function(data) {
           if (data.form) {
             $("#gPanel td form").replaceWith(data.form);
             self._ajaxify_panel();
           }
           if (data.result == "success") {
             self._trigger("success", null, {});
             if (data.location) {
               window.location = data.location;
             } else {
               window.location.reload();
             }
           }
         }
       });
     },

     success: function(event, ui) {}
   });
 })(jQuery);
