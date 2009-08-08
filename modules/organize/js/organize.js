(function($) {
  $.organize = {
    /**
     * Dynamically initialize the organize dialog when it is displayed
     */
    init: function(data) {
      // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475 (target 1.8?)
      $(".sf-menu li.sfHover ul").css("z-index", 70);

      $("#gDialog").bind("dialogopen", function(event, ui) {
        $("#gOrganize").height($("#gDialog").innerHeight() - 20);
        $("#gMicroThumbPanel").height($("#gDialog").innerHeight() - 90);
      });

      $("#gDialog").bind("dialogclose", function(event, ui) {
        window.location.reload();
      });

      $("#gDialog #gMicroThumbDone").click(function(event) {
        $("#gDialog").dialog("close");
      });

      $(".gBranchText span").click($.organize.collapse_or_expand_tree);
      $(".gBranchText").click($.organize.show_album);
    },

    /**
     * Open or close a branch.
     */
    collapse_or_expand_tree: function(event) {
      event.stopPropagation();
      $(event.currentTarget).toggleClass("ui-icon-minus").toggleClass("ui-icon-plus");
      $("#gOrganizeChildren-" + $(event.currentTarget).attr("ref")).toggle();
    },

    /**
     * When the text of a selection is clicked, then show that albums contents
     */
    show_album: function(event) {
      event.preventDefault();
      if ($(event.currentTarget).hasClass("gBranchSelected")) {
        return;
      }
      var id = $(event.currentTarget).attr("ref");
      $(".gBranchSelected").removeClass("gBranchSelected");
      $("#gOrganizeBranch-" + id).addClass("gBranchSelected");
      var url = $("#gMicroThumbPanel").attr("ref").replace("__ITEM_ID__", id).replace("__OFFSET__", 0);
      $.get(url, function(data) {
        $("#gMicroThumbGrid").html(data);
      });
    }
  };
})(jQuery);
