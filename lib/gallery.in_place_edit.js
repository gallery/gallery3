(function($) {
   $.widget("ui.gallery_in_place_edit",  {
     _init: function() {
       var self = this;
       this.element.click(function(event) {
         event.preventDefault();
         self._show(event.currentTarget);
         return false;
       });
     },

     _show: function(target) {
       if ($(target).data("gallery_in_place_edit") == true) {
         return;
       }
       $(target).data("gallery_in_place_edit", true);
       var self = this;
       var tag_width = $(target).width();
       $(self).data("tag_width", tag_width);

       var form = $("#g-in-place-edit-form");
       if (form.length > 0) {
         self._cancel();
       }

       $.get(self.options.form_url.replace("__ID__", $(target).attr('rel')), function(data) {
         var parent = $(target).parent();
         parent.children().hide();
         parent.append(data);
         self._setup_form(parent.find("form"));
       });
     },

     _setup_form: function(form) {
       var self = this;
       var width = $(self).data("tag_width");
       form.find(":text").width(width).focus();
       form.find(".g-cancel").click(function(event) {
         self._cancel();
         event.preventDefault();
         return false;
       });
       $(".g-short-form").gallery_short_form();
       this._ajaxify_edit();
     },

     _cancel: function() {
       var parent = $("#g-in-place-edit-form").parent();
       $("#g-in-place-edit-form").remove();
       $(parent).children().show();
       $(parent).find(".g-editable").data("gallery_in_place_edit", false);
     },

     _ajaxify_edit: function() {
       var self = this;
       var form = $("#g-in-place-edit-form");
       $(form).ajaxForm({
         dataType: "json",
         success: function(data) {
           if (data.result == "success") {
             window.location.reload();
           } else {
             var parent = $(form).parent();
             $(form).replaceWith(data.form);
             self._setup_form(parent.find("form"));
           }
         }
       });
     }
   });

   $.extend($.ui.gallery_in_place_edit,  {
     defaults: {}
   });
})(jQuery);
