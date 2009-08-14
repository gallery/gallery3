(function($) {
  $.organize = {
    micro_thumb_draggable: {
      distance: 10,
      cursorAt: { left: -10, top: -10},
      appendTo: "#gMicroThumbPanel",
      helper: function(event, ui) {
        var selected = $(".ui-draggable.ui-state-selected img"),
            set = $('<div class="gDragHelper"></div>').css({zIndex: 2000, width: 80, height: Math.ceil(selected.length / 5) * 16 }),
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
        $("#gMicroThumbPanel .ui-state-selected").hide();
      },
      drag: function(event, ui) {
        var top = $("#gMicroThumbPanel").offset().top;
        var height = $("#gMicroThumbPanel").height();
        if (ui.offset.top > height + top - 20) {
          $("#gMicroThumbPanel").get(0).scrollTop += 100;
        } else if (ui.offset.top < top + 20) {
          $("#gMicroThumbPanel").get(0).scrollTop = Math.max(0, $("#gMicroThumbPanel").get(0).scrollTop - 100);
        }
      },
      // @todo delete this method when drop is implemented
      stop: function(event, ui) {
        $(".ui-state-selected").show();
        $(".gMicroThumbGridCell").css("borderStyle", "none");
      }
    },

    content_droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
        $.organize.do_drop({
          parent_id: $(".gBranchSelected").attr("ref"),
          target_id: $(".currentDropTarget").attr("ref"),
          position: $(".currentDropTarget").css("borderLeftStyle") == "solid" ? "before" : "after",
          source: $(ui.helper).children("img")
        });
      }
    },

    branch_droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
        $.organize.do_drop({
          parent_id: $(event.target).attr("ref"),
          target_id: -1,
          position: "after",
          source: $(ui.helper).children("img")
        });
      }
    },

    do_drop:function(drop_parms) {
      var source_ids = "";
      $(drop_parms.source).each(function(i) {
        source_ids += (source_ids.length ? "&" : "") + "source_id[" + i + "]=" + $(this).attr("ref");
      });
      var url = drop_url.replace("__PARENT_ID__", drop_parms.parent_id)
        .replace("__POSITION__", drop_parms.position)
        .replace("__TARGET_ID__", drop_parms.target_id);

      console.group("do_drop");
      console.log("Generated url: " + url);
      console.log("Post data(ids to move): " + source_ids);
      console.groupEnd();
      // @todo do a ajax call to send the rearrange request to the server
      // organize/move/parent_id/before|after/-1|target_id
      // post parameters
      //  source=[id1, id2, ...]
      //  before or after not supplied then append to end
      // return: json {
      //   result: success | msg,
      //   tree: null | new tree,
      //   content: new thumbgrid
      // }
      // do forget to reset all the stuff in init when updating the content
    },

    mouse_move_handler: function(event) {
      if ($(".gDragHelper").length) {
        $(".gMicroThumbGridCell").css("borderStyle", "hidden");
        $(".currentDropTarget").removeClass("currentDropTarget");
        var borderStyle = event.pageX < $(this).offset().left + $(this).width() / 2 ?
          "borderLeftStyle" : "borderRightStyle";
        $(this).css(borderStyle, "solid");
        $(this).addClass("currentDropTarget");
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

      $("#gMicroThumbPanel").selectable({filter: ".gMicroThumbGridCell"});
      $("#gMicroThumbPanel").droppable($.organize.content_droppable);

      $.organize.set_handlers();
    },

    set_handlers: function() {
      $(".gMicroThumbGridCell").draggable($.organize.micro_thumb_draggable);
      $(".gMicroThumbGridCell").mousemove($.organize.mouse_move_handler);
      $(".gOrganizeBranch").droppable($.organize.branch_droppable);
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
        $.organize.set_handlers();
      });
    }
  };
})(jQuery);
