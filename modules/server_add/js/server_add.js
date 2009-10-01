(function($) {
   $.widget("ui.gallery_server_add",  {
     _init: function() {
       var self = this;
       $("#gServerAddAddButton", this.element).click(function(event) {
         event.preventDefault();
         $(".gProgressBar", this.element).
           progressbar().
           progressbar("value", 0);
         $("#gServerAddProgress", this.element).slideDown("fast", function() { self.start_add(); });
       });
       $("#gServerAddPauseButton", this.element).click(function(event) {
         self.pause = true;
         $("#gServerAddPauseButton", this.element).hide();
         $("#gServerAddContinueButton", this.element).show();
       });
       $("#gServerAddContinueButton", this.element).click(function(event) {
         self.pause = false;
         $("#gServerAddPauseButton", this.element).show();
         $("#gServerAddContinueButton", this.element).hide();
         self.run_add();
       });
       $("#gServerAddCloseButton", this.element).click(function(event) {
         $("#g-dialog").dialog("close");
         window.location.reload();
       });
       $("#gServerAddTree span.gDirectory", this.element).dblclick(function(event) {
         self.open_dir(event);
       });
       $("#gServerAddTree span.gFile, #gServerAddTree span.gDirectory", this.element).click(function(event) {
         self.select_file(event);
       });
       $("#gServerAddTree span.gDirectory", this.element).dblclick(function(event) {
         self.open_dir(event);
       });
       $("#g-dialog").bind("dialogclose", function(event, ui) {
         window.location.reload();
       });
     },

     taskURL: null,
     pause: false,

     start_add: function() {
       var self = this;
       var paths = [];
       $.each($("span.selected", self.element), function () {
	 paths.push($(this).attr("ref"));
       });

       $("#gServerAddAddButton", this.element).hide();
       $("#gServerAddPauseButton", this.element).show();

       $.ajax({
         url: START_URL,
         type: "POST",
         async: false,
         data: { "paths[]": paths },
         dataType: "json",
         success: function(data, textStatus) {
           $("#gStatus").html(data.status);
           $(".gProgressBar", self.element).progressbar("value", data.percent_complete);
           self.taskURL = data.url;
           setTimeout(function() { self.run_add(); }, 25);
         }
       });
       return false;
     },

     run_add: function () {
       var self = this;
       $.ajax({
         url: self.taskURL,
         async: false,
         dataType: "json",
         success: function(data, textStatus) {
           $("#gStatus").html(data.status);
           $(".gProgressBar", self.element).progressbar("value", data.percent_complete);
           if (data.done) {
	     $("#gServerAddProgress", this.element).slideUp();
             $("#gServerAddAddButton", this.element).show();
             $("#gServerAddPauseButton", this.element).hide();
             $("#gServerAddContinueButton", this.element).hide();
           } else {
             if (!self.pause) {
               setTimeout(function() { self.run_add(); }, 25);
             }
           }
         }
       });
     },

     /**
      * Load a new directory
      */
     open_dir: function(event) {
       var self = this;
       var path = $(event.target).attr("ref");
       $.ajax({
         url: GET_CHILDREN_URL.replace("__PATH__", path),
         success: function(data, textStatus) {
           $("#gServerAddTree", self.element).html(data);
           $("#gServerAddTree span.gDirectory", self.element).dblclick(function(event) {
             self.open_dir(event);
           });
           $("#gServerAddTree span.gFile, #gServerAddTree span.gDirectory", this.element).click(function(event) {
             self.select_file(event);
           });
         }
       });
     },

     /**
      * Manage file selection state.
      */
     select_file:  function (event) {
       $(event.target).toggleClass("selected");
       if ($("#gServerAdd span.selected").length) {
         $("#gServerAddAddButton").enable(true).removeClass("ui-state-disabled");
       } else {
         $("#gServerAddAddButton").enable(false).addClass("ui-state-disabled");
       }
     }
  });
})(jQuery);
