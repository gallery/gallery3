(function($) {
  $.organize = {
    micro_thumb_draggable: {
      distance: 10,
      cursorAt: { left: -10, top: -10},
      appendTo: "#gOrganizeContentPane",
      helper: function(event, ui) {
        var selected = $("li.ui-state-selected img"),
            set = $('<div class="temp"></div>').css({zIndex: 2000, width: 80, height: Math.ceil(selected.length / 5) * 16 }),
            offset = $(this).offset(),
            click = { left: event.pageX - offset.left, top: event.pageY - offset.top };

        selected.each(function(i) {
          var row = parseInt(i / 5);
          var j = i - (row * 5);

          var o = $(this).offset();

          var copy = $(this).clone()
            .css({
              width: $(this).width(), height: $(this).height(), display: "block",
              margin: 0, position: 'absolute', outline: '5px solid #fff',
              left: o.left - event.pageX, top: o.top - event.pageY
            })
            .appendTo(set)
            .animate({width: 10, height: 10, outlineWidth: 1, margin: 1, left: (20 * j), top: (row * 20)}, 500);
        });
        return set;
      },
      start: function(event, ui) {
        $("#gMicroThumbPanel").prepend("<div id=\"gPlaceHolder\"></div>");

        $("#gMicroThumbPanel li.ui-state-selected").hide();
      },
      drag: function(event, ui) {
        var container = $("#gMicroThumbPanel").get(0);
        var scrollTop = container.scrollTop;
        var height = $(container).height();
        if (event.pageY > height + scrollTop) {
          container.scrollTop += height;
        } else if (event.pageY < scrollTop) {
          container.scrollTop -= height;
        }
      },
      stop: function(event, ui) {
        $("li.ui-state-selected").show();
      }
    },

    droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
        // @todo do a ajax call to send the rearrange request to the zerver
        // organize/move/target_id/
        // post parameters
        //  before=id|after=id
        //  source=[id1, id2, ...]
        //  before or after not supplied then append to end
        // return: json {
        //   result: success | msg,
        //   tree: null | new tree,
        //   content: new thumbgrid
        // }
      }
    },

    /**
     * Dynamically initialize the organize dialog when it is displayed
     */
    init: function(data) {
      var self = this;
      // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475 (target 1.8?)
      $(".sf-menu li.sfHover ul").css("z-index", 68);
      $("#gDialog").dialog("option", "zIndex", 70);
      $("#gDialog").bind("dialogopen", function(event, ui) {
        $("#gOrganize").height($("#gDialog").innerHeight() - 20);
        $("#gMicroThumbPanel").height($("#gDialog").innerHeight() - 90);
        $("#gOrganizeAlbumTree").height($("#gDialog").innerHeight() - 59);
      });

      $("#gDialog").bind("dialogclose", function(event, ui) {
        window.location.reload();
      });

      $("#gDialog #gMicroThumbDone").click(function(event) {
        $("#gDialog").dialog("close");
      });

      $(".gBranchText span").click($.organize.collapse_or_expand_tree);
      $(".gBranchText").click($.organize.show_album);

      $("#gMicroThumbPanel").selectable({filter: "li"});
      $("#gMicroThumbPanel img").draggable($.organize.micro_thumb_draggable);
      $(".gOrganizeBranch").droppable($.organize.droppable);
      $("#gMicroThumbPanel").droppable($.organize.droppable);
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
