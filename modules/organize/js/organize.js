(function($) {
  $.organize = {
    /**
     * Dynamically initialize the organize dialog when it is displayed
     */
    init: function(data) {
      // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475 (target 1.8?)
      $(".sf-menu li.sfHover ul").css("z-index", 70);

      var height = $("#gOrganizeDetail").innerHeight();
      $("#gMicroThumbPanel").height(height - $("#gOrganizeEditDrawerHandle").outerHeight());

      $("#gDialog #gMicroThumbDone").click(function(event) {
        $("#gDialog").dialog("close");
        window.location.reload();
      });

      $(".gBranchText span").click($.organize.collapse_or_expand_tree);
      $(".gBranchText").click($.organize.setContents);
    },

    /**
     * Open or close a branch. If the children is a div placeholder, replace with <ul>
     */
    collapse_or_expand_tree: function (event) {
      event.stopPropagation();
      if ($(event.currentTarget).hasClass("ui-icon-minus")) {
        $(event.currentTarget).removeClass("ui-icon-minus").addClass("ui-icon-plus");
      } else {
        $(event.currentTarget).removeClass("ui-icon-plus").addClass("ui-icon-minus");
      }
      $("#gOrganizeChildren-" + $(event.currentTarget).attr("ref")).toggle();
    },

    /**
     * When the text of a selection is clicked, then show that albums contents
     */
    setContents: function(event) {
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
