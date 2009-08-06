(function($) {
  $.fn.organize = function(options) {
    var size = $.getViewportSize();
    var height = size.height() - 100;     // Leave 50 pixels on the top and bottom of the dialog
    var width = size.width() - 100;       // Leave 50 pixels on the left and right of the dialog
    var opts = $.extend({}, $.fn.organize.defaults, {width: width, height: height}, options);
    return this.each(function() {
      $(this).click(function(event) {
        var href = event.target.href;
        var size = $.getViewportSize();

        $("body").append('<div id="gOrganizeDialog"></div>');

        $("#gOrganizeDialog").dialog(opts);
        // Pass the approx height and width of the thumb grid to optimize thumb retrieval
        $.get(href, _init);
        return false;
      });
    });
  };

  $.fn.organize.defaults = {
    autoOpen: false,
    modal: true,
    resizable: false,
    minWidth: 600,
    minHeight: 500,
    position: "center",
    close: function () {
      $("#gOrganizeDialog").trigger("organize_close");
      $("#gOrganizeDialog").dialog("destroy").remove();
    },
    zIndex: 75
  };

  /**
   * Dynamically initialize the organize dialog when it is displayed
   */
  function _init(data) {
    // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475
    $(".sf-menu li.sfHover ul").css("z-index", 70);

    $("#gOrganizeDialog").html(data);
    $("#gOrganizeDialog").dialog("open");

    var heightMicroThumbPanel = $("#gOrganizeDialog").innerHeight();
    heightMicroThumbPanel -= 2 * parseFloat($("#gOrganizeDialog").css("padding-bottom"));
    heightMicroThumbPanel -= $("#gMessage").outerHeight();
    heightMicroThumbPanel = Math.floor(heightMicroThumbPanel);
    $("#gOrganizeTreeContainer").height(heightMicroThumbPanel);

    heightMicroThumbPanel -= $("#gOrganizeEditDrawerHandle").outerHeight();
    $("#gMicroThumbPanel").height(heightMicroThumbPanel);

    if ($("#gOrganizeDialog h1").length) {
      $("#gOrganizeDialog").dialog('option', 'title', $("#gOrganizeDialog h1:eq(0)").html());
    } else if ($("#gOrganizeDialog fieldset legend").length) {
      $("#gOrganizeDialog").dialog('option', 'title', $("#gOrganizeDialog fieldset legend:eq(0)").html());
    }

    $("#gOrganizeDialog #gMicroThumbDone").click(_dialog_close);
    $("#gOrganizeDialog").bind("organize_close", function(target) {
      $.gallery_reload();
    });

    $(".gBranchText span").click(_collapse_or_expanded_tree);
    $(".gBranchText").click(_setContents);
    $(".gAlbum img.gThumbnail").live("dblclick", _openAlbum);

    //$(".gOrganizeBranch .ui-icon").click(organizeToggleChildren);
    //$(".gBranchText").droppable(treeDroppable);

    //$("#gMicroThumbPanel").droppable(thumbDroppable);
    //$("#gMicroThumbPanel").selectable(selectable);
    //$("#gOrganizeEditDrawerHandle a").click(drawerHandleButtonsClick);

  };

  function _dialog_close(event) {
    event.preventDefault();
    $("#gOrganizeDialog").dialog("close");
  };

  /**
   * Open or close a branch. If the children is a div placeholder, replace with <ul>
   */
  function _collapse_or_expanded_tree(event) {
    event.stopPropagation();
    var id = $(event.currentTarget).attr("ref");
    if ($(event.currentTarget).hasClass("ui-icon-minus")) {
      $(event.currentTarget).removeClass("ui-icon-minus");
      $(event.currentTarget).addClass("ui-icon-plus");
      $("#gOrganizeChildren-" + id).hide();
    } else {
      if ($("#gOrganizeChildren-" + id).is("div")) {
        $("#gOrganizeChildren-" + id).remove();
        $("#gOrganizeBranch-" + id).after("<ul id=\"gOrganizeChildren-" + id + "></ul>");
        var url = $("#gOrganizeAlbumTree").attr("ref").replace("__ITEM_ID__", id);
        $.get(url, function(data) {
          $("#gOrganizeChildren-" + id).html(data);
          $(".gBranchText span").click(_collapse_or_expanded_tree);
          $(".gBranchText").click(_setContents);
        });
      }
      $("#gOrganizeChildren-" + id).show();
      $(event.currentTarget).removeClass("ui-icon-plus");
      $(event.currentTarget).addClass("ui-icon-minus");
    }
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
    $(event.currentTarget).addClass("gBranchSelected");
    var url = $("#gMicroThumbPanel").attr("ref").replace("__ITEM_ID__", id).replace("__OFFSET__", 0);
    $.get(url, function(data) {
      $("#gMicroThumbGrid").html(data);
    });
  }

  function _openAlbum(event) {
    event.preventDefault();
    var id = $(event.target).parent().attr("ref");
    $(".gBranchSelected").removeClass("gBranchSelected");
    $("#gOrganizeBranch-" + id).addClass("gBranchSelected");
    var url = $("#gMicroThumbPanel").attr("ref").replace("__ITEM_ID__", id).replace("__OFFSET__", 0);
    // @todo load the branch elements if required.
    $.get(url, function(data) {
      $("#gMicroThumbGrid").html(data);
    //  $(".gAlbum img.gThumbnail").dblclick(_openAlbum);
    });
  }

})(jQuery);

$("document").ready(function() {
  $("#gOrganizeLink").organize();
});
