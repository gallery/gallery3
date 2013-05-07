(function($) {
  $.widget("ui.gallery_panel", {
    _create: function() {
      var self = this;
      this.element.click(function(event) {
        event.preventDefault();
        var element = event.currentTarget;
        var parent = $(element).parent().parent();
        var sHref = $(element).attr("href");
        var parentClass = $(parent).attr("class");
        var ePanel = "<tr id=\"g-panel\"><td colspan=\"6\"></td></tr>";

        // We keep track of the open vs. closed state by looking to see if there's
        // a data-orig-text attr.  If that attr is missing, then the panel is closed
        // and we want to open it
        var should_open = !$(element).attr("data-orig-text");

        // Close any open panels and reset their button text
        if ($("#g-panel").length) {
          $("#g-panel").slideUp("slow").remove();
          $.each($(".g-panel-link"),
            function() {
              if ($(this).attr("data-orig-text")) {
                $(this).children(".g-button-text").text($(this).attr("data-orig-text"));
                $(this).attr("data-orig-text", "");
              }
            }
          );
        }

        if (should_open) {
          $(parent).after(ePanel);
          $("#g-panel td").html(sHref);
          $.ajax({
            url: sHref,
            type: "GET",
            success: function(data, textStatus, xhr) {
              var mimeType = /^(\w+\/\w+)\;?/.exec(xhr.getResponseHeader("Content-Type"));
              var content = "";
              if (mimeType[1] == "application/json") {
                content = unescape(data.html);
              } else {
                content = data;
              }

              $("#g-panel td").html(content);
              self._ajaxify_panel();
              if ($(element).attr("data-open-text")) {
                $(element).attr("data-orig-text", $(element).children(".g-button-text").text());
                $(element).children(".g-button-text").text($(element).attr("data-open-text"));
              }
              $("#g-panel").addClass(parentClass).show().slideDown("slow");
            }
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
          if (data.html) {
            $("#g-panel td").html(data.html);
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
