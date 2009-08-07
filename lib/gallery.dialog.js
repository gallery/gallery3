(function($) {
   $.widget("ui.gallery_dialog",  {
     _init: function() {
       var self = this;
       this.element.click(function(event){
         event.preventDefault();
         var element = event.currentTarget;
         var sHref = $(element).attr("href");
         var sTitle = $(element).attr("title");
         var eDialog = '<div id="gDialog"></div>';

         $("body").append(eDialog);

         if (!self.options.close) {
           self.options.close = self.close_dialog;
         }
         $("#gDialog").dialog(self.options);

         $("#gDialog").showLoading();

         $.get(sHref, function(data) {
           $("#gDialog").html(data).showLoading();

           if ($("#gDialog form").length) {
             self._trigger("form_loaded", null, $("#gDialog form"));
           }
           self._layout();

           $("#gDialog").dialog("open");
           // Remove titlebar for progress dialogs or set title
           if ($("#gDialog #gProgress").length) {
             $(".ui-dialog-titlebar").remove();
           } else if ($("#gDialog h1").length) {
             $("#gDialog").dialog('option', 'title', $("#gDialog h1:eq(0)").html());
           } else if ($("#gDialog fieldset legend").length) {
             $("#gDialog").dialog('option', 'title', $("#gDialog fieldset legend:eq(0)").html());
           }

           if ($("#gDialog form").length) {
             self._ajaxify_dialog();
           }
         });
         $("#gDialog").dialog("option", "self", self);
         return false;
       });
     },

     destroy: function() {
       if ($("#gDialog form").length) {
         this._trigger("form_closing", null, $("#gDialog form"));
       }
       this._trigger("dialog_closing", null, $("#gDialog"));

       $("#gDialog").dialog("destroy").remove();
     },

     _layout: function() {
       var dialogWidth;
       var dialogHeight = $("#gDialog").height();
       var cssWidth = new String($("#gDialog form").css("width"));
       var childWidth = cssWidth.replace(/[^0-9]/g,"");
       if ($("#gDialog iframe").length) {
         dialogWidth = $(window).width() - 100;
         // Set the iframe width and height
         $("#gDialog iframe").width("100%").height($(window).height() - 100);
       } else if (childWidth == "" || childWidth > 300) {
         dialogWidth = 500;
       }
       $("#gDialog").dialog('option', 'width', dialogWidth);
     },

     form_loaded: function event(event, ui) {
       // Should be defined (and localized) in the theme
       MSG_CANCEL = MSG_CANCEL || 'Cancel';
       var eCancel = '<a href="#" class="gCancel">' + MSG_CANCEL + '</a>';
       if ($("#gDialog .submit").length) {
         $("#gDialog .submit").addClass("ui-state-default ui-corner-all");
         $("#gDialog .submit").parent().append(eCancel);
         $("#gDialog .gCancel").click(function(event) {
           $("gDialog").dialog("close");
           event.preventDefault();
         });
        }
       $("#gDialog .ui-state-default").hover(
         function() {
           $(this).addClass("ui-state-hover");
         },
         function() {
           $(this).removeClass("ui-state-hover");
         }
       );
     },

     close_dialog: function (event, ui) {
       var self = $("#gDialog").dialog("option", "self");
       self.destroy();
     },

     _ajaxify_dialog: function() {
       var self = this;
       $("#gDialog form").ajaxForm({
         dataType: "json",
         success: function(data) {
           if (data.form) {
             $("#gDialog form").replaceWith(data.form);
             self._ajaxify_dialog();
             self._trigger("form_loaded", null, $("#gDialog form"));
             if (typeof data.reset == 'function') {
               eval(data.reset + '()');
             }
           }
           if (data.result == "success") {
	     if (data.location) {
               window.location = data.location;
             } else {
               window.location.reload();
             }
           }
         }
       });
     },

     form_closing: function(event, ui) {},
     dialog_closing: function(event, ui) {}
   });

   $.extend($.ui.gallery_dialog,  {
     defaults: {
       autoOpen: false,
       autoResize: true,
       modal: true,
       resizable: false,
       position: "center"
     }
   });
})(jQuery);
