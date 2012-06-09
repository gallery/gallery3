
(function($) {
   $.widget("ui.gallery_dialog",  {
     _init: function() {
       var self = this;
       if (!self.options.immediate) {
         this.element.click(function(event) {
           event.preventDefault();
           self._show($(event.currentTarget).attr("href"));
           return false;
         });
       } else {
         self._show(this.element.attr("href"));
       }
     },

     _show: function(sHref) {
       var self = this;
       var eDialog = '<div id="g-dialog"></div>';

       if ($("#g-dialog").length) {
         $("#g-dialog").dialog("close");
       }
       $("body").append(eDialog);

       if (!self.options.close) {
         self.options.close = self.close_dialog;
       }
       $("#g-dialog").dialog(self.options);

       $("#g-dialog").gallery_show_loading();

       $.ajax({
         url: sHref,
         type: "GET",
         beforeSend: function(xhr) {
           // Until we convert to jquery 1.4, we need to save the XMLHttpRequest object so that we
           // can detect the mime type of the reply
           this.xhrData = xhr;
         },
         success: function(data, textStatus, xhr) {
           // Pre jquery 1.4, get the saved XMLHttpRequest object
           if (xhr == undefined) {
             xhr = this.xhrData;
           }
           var mimeType = /^(\w+\/\w+)\;?/.exec(xhr.getResponseHeader("Content-Type"));

           var content = "";
           if (mimeType[1] == "application/json") {
             data = JSON.parse(data);
	     content = data.html;
           } else {
             content = data;
           }

           $("#g-dialog").html(content).gallery_show_loading();

           if ($("#g-dialog form").length) {
             self.form_loaded(null, $("#g-dialog form"));
           }
           self._layout();

           $("#g-dialog").dialog("open");
           self._set_title();

           if ($("#g-dialog form").length) {
             self._ajaxify_dialog();
           }
         }
       });
       $("#g-dialog").dialog("option", "self", self);
     },

     error: function(xhr, textStatus, errorThrown) {
	 $("#g-dialog").html(xhr.responseText);
	 self._set_title();
	 self._layout();
     },

     _layout: function() {
       var dialogWidth;
       var dialogHeight = $("#g-dialog").height();
       var cssWidth = new String($("#g-dialog form").css("width"));
       var childWidth = cssWidth.replace(/[^0-9]/g,"");
       var size = $.gallery_get_viewport_size();
       if ($("#g-dialog iframe").length) {
         dialogWidth = size.width() - 100;
         // Set the iframe width and height
         $("#g-dialog iframe").width("100%").height(size.height() - 100);
       } else if ($("#g-dialog .g-dialog-panel").length) {
         dialogWidth = size.width() - 100;
         $("#g-dialog").dialog("option", "height", size.height() - 100);
       } else if (childWidth == "" || childWidth > 300) {
         dialogWidth = 500;
       }
       $("#g-dialog").dialog('option', 'width', dialogWidth);
     },

     form_loaded: function(event, ui) {
       // Should be defined (and localized) in the theme
       MSG_CANCEL = MSG_CANCEL || 'Cancel';
       var eCancel = '<a href="#" class="g-cancel g-left">' + MSG_CANCEL + '</a>';
       if ($("#g-dialog .submit").length) {
         $("#g-dialog .submit").addClass("ui-state-default ui-corner-all");
         $.fn.gallery_hover_init();
         $("#g-dialog .submit").parent().append(eCancel);
         $("#g-dialog .g-cancel").click(function(event) {
           $("#g-dialog").dialog("close");
           event.preventDefault();
         });
        }
       $("#g-dialog .ui-state-default").hover(
         function() {
           $(this).addClass("ui-state-hover");
         },
         function() {
           $(this).removeClass("ui-state-hover");
         }
       );
     },

     close_dialog: function(event, ui) {
       var self = $("#g-dialog").dialog("option", "self");
       if ($("#g-dialog form").length) {
         self._trigger("form_closing", null, $("#g-dialog form"));
       }
       self._trigger("dialog_closing", null, $("#g-dialog"));
       $("#g-dialog").dialog("destroy").remove();
     },

     _ajaxify_dialog: function() {
       var self = this;
       $("#g-dialog form").ajaxForm({
         beforeSubmit: function(formData, form, options) {
           form.find(":submit")
             .addClass("ui-state-disabled")
             .attr("disabled", "disabled");
           return true;
         },
         beforeSend: function(xhr) {
           // Until we convert to jquery 1.4, we need to save the XMLHttpRequest object so that we
           // can detect the mime type of the reply
           this.xhrData = xhr;
         },
         success: function(data) {
           // Pre jquery 1.4, get the saved XMLHttpRequest object
           xhr = this.xhrData;
           if (xhr) {
             var mimeType = /^(\w+\/\w+)\;?/.exec(xhr.getResponseHeader("Content-Type"));

             var content = "";
             if (mimeType[1] == "application/json") {
               data = JSON.parse(data);
             } else {
               data = {"html": escape(data)};
             }
           } else {
             // Uploading files (eg: watermark) uses a fake xhr in jquery.form.js so
             // all we have is in the data field, which should be some very simple JSON.
             // Weirdly enough in Chrome the result gets wrapped in a <pre> element and
             // looks like this:
             //   <pre style="word-wrap: break-word; white-space: pre-wrap;">{"result":"success",
             //   "location":"\/~bharat\/gallery3\/index.php\/admin\/watermarks"}</pre>
             // bizarre.  Strip that off before parsing.
             data = JSON.parse(data.match("({.*})")[0]);
           }

           if (data.html) {
             $("#g-dialog").html(data.html);
             $("#g-dialog").dialog("option", "position", "center");
             $("#g-dialog form :submit").removeClass("ui-state-disabled")
               .attr("disabled", null);
             self._set_title();
             self._ajaxify_dialog();
             self.form_loaded(null, $("#g-dialog form"));
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
         },
	 error: function(xhr, textStatus, errorThrown) {
	     $("#g-dialog").html(xhr.responseText);
	     self._set_title();
	     self._layout();
	 }
       });
     },

     _set_title: function() {
       // Remove titlebar for progress dialogs or set title
       if ($("#g-dialog #g-progress").length) {
         $(".ui-dialog-titlebar").remove();
       } else if ($("#g-dialog h1").length) {
         $("#g-dialog").dialog('option', 'title', $("#g-dialog h1:eq(0)").html());
         $("#g-dialog h1:eq(0)").hide();
       } else if ($("#g-dialog fieldset legend").length) {
         $("#g-dialog").dialog('option', 'title', $("#g-dialog fieldset legend:eq(0)").html());
       }
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
