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
         var ePanel = "<tr id=\"g-panel\"><td colspan=\"6\"></td></tr>";

	 // We keep track of the open vs. closed state by looking to see if there'
	 // an orig_text attr.  If that attr is missing, then the panel is closed
	 // and we want to open it
	 var should_open = !$(element).attr("orig_text");

	 // Close any open panels and reset their button text
         if ($("#g-panel").length) {
           $("#g-panel").slideUp("slow").remove();
	   $.each($(".g-panel-link"),
		  function() {
		    if ($(this).attr("orig_text")) {
		      $(this).children(".g-button-text").text($(this).attr("orig_text"));
		      $(this).attr("orig_text", "");
		    }
		  }
	   );
         }

	 if (should_open) {
	   $(parent).after(ePanel);
	   $("#g-panel td").html(sHref);
	   $.get(sHref, function(data) {
	     $("#g-panel td").html(data);
	     self._ajaxify_panel();
	     if ($(element).attr("open_text")) {
	       $(element).attr("orig_text", $(element).children(".g-button-text").text());
	       $(element).children(".g-button-text").text($(element).attr("open_text"));
	     }
	     $("#g-panel").addClass(parentClass).show().slideDown("slow");
	   });
	 }

         return false;
       });
     },

     _ajaxify_panel: function () {
       var self = this;
       $("#g-panel td form").ajaxForm({
         dataType: "json",
         beforeSubmit: function(formData, form, options) {
           form.find(":submit")
             .addClass("ui-state-disabled")
             .attr("disabled", "disabled");
           return true;
         },
         success: function(data) {
           if (data.form) {
             $("#g-panel td form").replaceWith(data.form);
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
