(function($) {
  $.fn.organize = function() {
    var size = $.getViewportSize();
    var height = size.height() - 100;     // Leave 50 pixels on the top and bottom of the dialog
    var width = size.width() - 100;       // Leave 50 pixels on the left and right of the dialog
    return this.each(function() {
      $(this).click(function(event) {
        var href = event.target.href;

        $("body").append('<div id="gOrganizeDialog"></div>');

        $("#gOrganizeDialog").dialog({
          autoOpen: false,
          modal: true,
          resizable: false,
          width: width,
          height: height,
          position: "center",
          close: function () {
            $("#gOrganizeDialog").dialog("destroy").remove();
            document.location.reload();
         },
          zIndex: 75
        });
        $.get(href, _init);
        return false;
      });
    });
  };

  /**
   * Dynamically initialize the organize dialog when it is displayed
   */
  function _init(data) {
    // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475 (target 1.8?)
    $(".sf-menu li.sfHover ul").css("z-index", 70);

    $("#gOrganizeDialog").html(data);
    $("#gOrganizeDialog").dialog("open");

    var height = $("#gOrganizeDetail").innerHeight();
    $("#gMicroThumbPanel").height(height - $("#gOrganizeEditDrawerHandle").outerHeight());

    if ($("#gOrganizeDialog h1").length) {
      $("#gOrganizeDialog").dialog('option', 'title', $("#gOrganizeDialog h1:eq(0)").html());
    } else if ($("#gOrganizeDialog fieldset legend").length) {
      $("#gOrganizeDialog").dialog('option', 'title', $("#gOrganizeDialog fieldset legend:eq(0)").html());
    }

    $("#gOrganizeDialog #gMicroThumbDone").click(function(event) {
      $("#gOrganizeDialog").dialog("close");
    });

    $(".gBranchText span").click(_collapse_or_expanded_tree);
    $(".gBranchText").click(_setContents);
  };

  /**
   * Open or close a branch. If the children is a div placeholder, replace with <ul>
   */
  function _collapse_or_expanded_tree(event) {
    event.stopPropagation();
    if ($(event.currentTarget).hasClass("ui-icon-minus")) {
      $(event.currentTarget).removeClass("ui-icon-minus").addClass("ui-icon-plus");
    } else {
      $(event.currentTarget).removeClass("ui-icon-plus").addClass("ui-icon-minus");
    }
    $("#gOrganizeChildren-" + $(event.currentTarget).attr("ref")).toggle();
  }

  /**
   * When the text of a selection is clicked, then show that albums contents
   */
  function _setContents(event) {
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
})(jQuery);

$("document").ready(function() {
  $("#gOrganizeLink").organize();
});
