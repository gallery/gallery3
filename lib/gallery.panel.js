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

	 // We keep track of the open vs. closed state by looking to see if there'
	 // an orig_text attr.  If that attr is missing, then the panel is closed
	 // and we want to open it
	 var should_open = !$(element).attr("orig_text");

	 // Close any open panels and reset their button text
         if ($("#gPanel").length) {
           $("#gPanel").slideUp("slow").remove();
	   $.each($(".gPanelLink"),
		  function() {
		    if ($(this).attr("orig_text")) {
		      $(this).children(".gButtonText").text($(this).attr("orig_text"));
		      $(this).attr("orig_text", "");
		    }
		  }
	   );
         }

	 if (should_open) {
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
