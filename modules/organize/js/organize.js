(function($) {
  $.organize = {
    micro_thumb_draggable: {
      handle: ".ui-selected",
      distance: 10,
      cursorAt: { left: -10, top: -10},
      appendTo: "#g-organize-content-pane",
      helper: function(event, ui) {
        var selected = $(".ui-draggable.ui-selected img");
        var set = $('<div class="g-drag-helper"></div>')
          .css({
            zIndex: 2000,
            width: 80,
            height: Math.ceil(selected.length / 5) * 16
	  });

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
            .animate({ width: 10, height: 10, outlineWidth: 1, margin: 1,
              left: (20 * j), top: (row * 20) }, 500);
        });
        return set;
      },

      start: function(event, ui) {
        $("#g-organize-microthumb-grid .ui-selected").hide();
      },

      drag: function(event, ui) {
        var top = $("#g-organize-microthumb-grid").offset().top;
        var height = $("#g-organize-microthumb-grid").height();
        var scrollTop = $("#g-organize-microthumb-grid").scrollTop();
        if (ui.offset.top > height + top - 20) {
          scrollTop += 100;
        } else if (ui.offset.top < top + 20) {
          scrollTop = Math.max(0,  scrollTop - 100);
        }
        $("#g-organize-microthumb-grid").scrollTop(scrollTop);
      }
    },

    content_droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
        $(".g-mouse-drag-over").removeClass("g-mouse-drag-over");
        var target = $("#g-organize-drop-target-marker").data("drop_position");
        if (target == null) {
          target = {
            position: false,
            id: $(".g-organize-microthumb-grid-cell:visible:last").attr("ref")
          };
        }
        $.organize.do_drop({
          url: rearrange_url
            .replace("__TARGET_ID__", target.id)
            .replace("__BEFORE__", target.position ? "before" : "after"),
          source: $(ui.helper).children("img")
        });
      }
    },

    branch_droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
        if ($(event.target).hasClass("g-view-only")) {
          $("#g-organize-drop-target-marker").remove();
          $(".ui-selected").show();
        } else {
          $.organize.do_drop({
            url: move_url.replace("__ALBUM_ID__", $(event.target).attr("ref")),
            source: $(ui.helper).children("img")
          });
        }
      }
    },

    do_drop: function(options) {
      $("#g-organize-microthumb-grid").selectable("destroy");
      var source_ids = [];
      $(options.source).each(function(i) {
        source_ids.push($(this).attr("ref"));
      });

      if (source_ids.length) {
        var loading = $('<div class="g-dialog-loading-large">&nbsp;</div>')
	  .css({bottom: 5,
                opacity: .5,
                left: 0,
                position: "absolute",
                right: 0,
                top: 0,
                zIndex: 2000
	    });
        $("#g-organize-microthumb-grid").append(loading);

        $.post(options.url,
               { "source_ids[]": source_ids },
	       function(data) {
                 $.organize._refresh(data);
                 $(".g-dialog-loading-large").remove();
	       },
	       "json");
      }
    },

    _refresh: function(data) {
      if (data.tree) {
        $("#g-organize-album-tree").html(data.tree);
      }
      if (data.grid) {
        $("#g-organize-microthumb-grid").html(data.grid);
        $("#g-organize-sort-column").attr("value", data.sort_column);
        $("#g-organize-sort-order").attr("value", data.sort_order);
      }
      $.organize.set_handlers();
    },

    grid_mouse_leave_handler: function(event) {
      if ($(".g-drag-helper").length && $("#g-organize-drop-target-marker").length) {
        $("#g-organize-drop-target-marker").remove();
      }
    },

    grid_mouse_move_handler: function(event) {
      if ($(".g-drag-helper").length) {
        var organizeData = $("#g-organize").data("organizeData");
        var thumbGrid = $("#g-organize-microthumb-grid");
        var visibleCells = $(".g-organize-microthumb-grid-cell:visible");
        var scrollTop = thumbGrid.scrollTop();

        var item = $(".g-mouse-drag-over");
        if (item.length == 0) {
          var itemColumn = Math.floor((event.pageX - thumbGrid.offset().left) / organizeData.width);
          itemColumn = organizeData.rtl ? organizeData.width - itemColumn : itemColumn;
          var itemRow = Math.floor((event.pageY + scrollTop - thumbGrid.offset().top) / organizeData.height);
          var itemIndex = Math.min(itemRow * organizeData.columns + itemColumn, visibleCells.length - 1);
          item = visibleCells.get(itemIndex);
        }

        var before = event.pageX < ($(item).offset().left + $(item).width() / 2);
        var left = $(item).position().left + (before ? 0 : organizeData.width) - 3;
        var top = $(item).position().top + 6 + scrollTop;

        if ($("#g-organize-drop-target-marker").length) {
          $("#g-organize-drop-target-marker").remove();
        }

        var set = $('<div id="g-organize-drop-target-marker"></div>')
	  .css({zIndex: 2000,
                width: 2,
                height: 112,
                borderWidth: 1,
                borderStyle: "solid",
                position: "absolute",
                top: top, left: left
	       })
          .data("drop_position", {id: $(item).attr("ref"),
                                  position: organizeData.rtl ? !before : before});
        thumbGrid.append(set);
      }
      return true;
    },

    /**
     * Dynamically initialize the organize dialog when it is displayed
     */
    init: function(data) {
      var self = this;
      // Deal with ui.jquery bug: http://dev.jqueryui.com/ticket/4475 (target 1.8?)
      $(".sf-menu li.sfHover ul").css("z-index", 68);
      $("#g-dialog").dialog("option", "zIndex", 70);
      $("#g-dialog").bind("dialogopen", function(event, ui) {
      var outerHeight = $(".g-organize-microthumb-grid-cell").outerHeight(true);
      var outerWidth = $(".g-organize-microthumb-grid-cell").outerWidth(true);
      var gridInnerWidth = $("#g-organize-microthumb-grid").innerWidth() - 2 * parseFloat($("#g-organize-microthumb-grid").css("paddingLeft"));
      $("#g-organize")
        .height($("#g-dialog").innerHeight() - 20)
        .data("organizeData", {
          rtl: $("body").hasClass("rtl"),
          height: outerHeight,
          width: outerWidth,
          columns: Math.floor(gridInnerWidth / outerWidth)
        });
      });

      $("#g-dialog").bind("dialogclose", function(event, ui) {
        window.location.reload();
      });

      $("#g-organize-close").click(function(event) {
        $("#g-dialog").dialog("close");
      });

      $("#g-organize-sort-column,#g-organize-sort-order").change(function(event) {
        $.organize.resort($("#g-organize-sort-column").attr("value"),
            $("#g-organize-sort-order").attr("value"));
      });

      $.organize.set_handlers();
    },

    set_handlers: function() {
      $("#g-organize-microthumb-grid")
        .selectable({filter: ".g-organize-microthumb-grid-cell"})
        .mousemove($.organize.grid_mouse_move_handler)
        .mouseleave($.organize.grid_mouse_leave_handler)
        .droppable($.organize.content_droppable);
      $(".g-organize-microthumb-grid-cell")
        // need to manually add this class in case we care calling with additional elements
        .addClass("ui-selectee")
        .mouseleave(function(event) {
          if ($(".g-drag-helper").length) {
            $(this).removeClass("g-mouse-drag-over");
          }
        })
        .mouseenter(function(event) {
          $(".g-mouse-drag-over").removeClass("g-mouse-drag-over");
          if ($(".g-drag-helper").length) {
            $(this).addClass("g-mouse-drag-over");
          }
        })
        .draggable($.organize.micro_thumb_draggable);
      $(".g-organize-album").droppable($.organize.branch_droppable);
      $(".g-organize-album-text").click($.organize.show_album);
      $("#g-organize-album-tree .ui-icon-plus,#g-organize-album-tree .ui-icon-minus").click($.organize.toggle_branch);
    },

    toggle_branch: function(event) {
      event.preventDefault();
      var target = $(event.currentTarget);
      var branch = $(target).parent();
      var id = $(event.currentTarget).parent().attr("ref");

      if ($(target).hasClass("ui-icon-plus")) {
        // Expanding
        if (!branch.find("ul").length) {
          $.get(tree_url.replace("__ALBUM_ID__", id), { }, function(data) {
            branch.replaceWith(data);
            $.organize.set_handlers();
          });
        } else {
          branch.find("ul:eq(0)").slideDown();
        }
      } else {
        // Contracting
        branch.find("ul:eq(0)").slideUp();
      }
      $(target).toggleClass("ui-icon-plus");
      $(target).toggleClass("ui-icon-minus");
    },

    /**
     * When the text of a selection is clicked, then show that albums contents
     */
    show_album: function(event) {
      event.preventDefault();
      if ($(event.currentTarget).hasClass("ui-state-focus")) {
        return;
      }
      var parent = $(event.currentTarget).parents(".g-organize-branch");
      if ($(parent).hasClass("g-view-only")) {
        return;
      }
      $("#g-organize-microthumb-grid").selectable("destroy");
      var id = $(event.currentTarget).attr("ref");
      $(".g-organize-album-text.ui-state-focus").removeClass("ui-state-focus");
      $(".g-organize-album-text[ref=" + id + "]").addClass("ui-state-focus");
      var url = $("#g-organize-microthumb-grid").attr("ref").replace("__ITEM_ID__", id).replace("__OFFSET__", 0);
      $.get(url, {},
            function(data) {
              $("#g-organize-microthumb-grid").html(data.grid);
              $("#g-organize-sort-column").attr("value", data.sort_column);
              $("#g-organize-sort-order").attr("value", data.sort_order);
              $.organize.set_handlers();
            },
	    "json");
    },

    /**
     * Change the sort order.
     */
    resort: function(column, dir) {
      var url = sort_order_url
        .replace("__ALBUM_ID__", $("#g-organize-album-tree .ui-state-focus").attr("ref"))
        .replace("__COL__", column)
        .replace("__DIR__", dir);
      $.get(url, {},
        function(data) {
          $("#g-organize-microthumb-grid").html(data.grid);
          $("#g-organize-sort-column").attr("value", data.sort_column);
          $("#g-organize-sort-order").attr("value", data.sort_order);
          $.organize.set_handlers();
        },
	    "json");
    }
  };
})(jQuery);
