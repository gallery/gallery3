(function($) {
  $.organize = {
    micro_thumb_draggable: {
      handle: ".ui-state-selected",
      distance: 10,
      cursorAt: { left: -10, top: -10},
      appendTo: "#gMicroThumbPanel",
      helper: function(event, ui) {
        var selected = $(".ui-draggable.ui-state-selected img");
        if (selected.length) {
          var set = $('<div class="gDragHelper"></div>')
		      .css({
			zIndex: 2000,
			width: 80,
			height: Math.ceil(selected.length / 5) * 16
		      });
          var offset = $(this).offset();
          var click = {left: event.pageX - offset.left, top: event.pageY - offset.top};

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
        }
        return null;
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
      }
    },

    content_droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
	var before_or_after = $(".currentDropTarget").css("borderLeftStyle") == "solid" ? "before" : "after";
        $.organize.do_drop({
          url: rearrange_url
	    .replace("__TARGET_ID__", $(".currentDropTarget").attr("ref"))
	    .replace("__ALBUM_ID__", $(".currentDropTarget").attr("ref"))
	    .replace("__BEFORE__", before_or_after),
          source: $(ui.helper).children("img")
        });
      }
    },

    branch_droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
        if ($(event.target).hasClass("gViewOnly")) {
          $(".ui-state-selected").show();
          $(".gMicroThumbGridCell").css("borderStyle", "none");
        } else {
          $.organize.do_drop({
            url: move_url.replace("__ALBUM_ID__", $(event.target).attr("ref")),
            source: $(ui.helper).children("img")
          });
        }
      }
    },

    do_drop: function(options) {
      $("#gMicroThumbPanel").selectable("destroy");
      var source_ids = [];
      $(options.source).each(function(i) {
        source_ids.push($(this).attr("ref"));
      });

      if (source_ids.length) {
	$.post(options.url,
	       { "source_ids[]": source_ids },
	       function(data) { $.organize._refresh(data); },
	      "json");
      }
    },

    _refresh: function(data) {
      if (data.tree) {
        $("#gOrganizeAlbumTree").html(data.tree);
      }
      if (data.grid) {
        $("#gMicroThumbGrid").html(data.grid);
        $("#gOrganizeSortColumn").attr("value", data.sort_column);
        $("#gOrganizeSortOrder").attr("value", data.sort_order);
      }
      $.organize.set_handlers();
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

      $("#gDialog #gOrganizeClose").click(function(event) {
        $("#gDialog").dialog("close");
      });

      $("#gOrganizeSortColumn,#gOrganizeSortOrder").change(function(event) {
	$.organize.resort($("#gOrganizeSortColumn").attr("value"), $("#gOrganizeSortOrder").attr("value"));
      });

      $.organize.set_handlers();
    },

    set_handlers: function() {
      $("#gMicroThumbPanel").selectable({filter: ".gMicroThumbGridCell"});
      $("#gMicroThumbPanel").droppable($.organize.content_droppable);

      $(".gMicroThumbGridCell").draggable($.organize.micro_thumb_draggable);
      $(".gMicroThumbGridCell").mousemove($.organize.mouse_move_handler);
      $(".gOrganizeAlbum").droppable($.organize.branch_droppable);
      $(".gAlbumText").click($.organize.show_album);
    },


    /**
     * When the text of a selection is clicked, then show that albums contents
     */
    show_album: function(event) {
      event.preventDefault();
      if ($(event.currentTarget).hasClass("selected")) {
        return;
      }
      var parent = $(event.currentTarget).parents(".gOrganizeBranch");
      if ($(parent).hasClass("gViewOnly")) {
        return;
      }
      $("#gMicroThumbPanel").selectable("destroy");
      var id = $(event.currentTarget).attr("ref");
      $("#gOrganizeAlbumTree .selected").removeClass("selected");
      $(".gAlbumText[ref=" + id + "]").addClass("selected");
      var url = $("#gMicroThumbPanel").attr("ref").replace("__ITEM_ID__", id).replace("__OFFSET__", 0);
      $.get(url, {},
	    function(data) {
	      $("#gMicroThumbGrid").html(data.grid);
	      $("#gOrganizeSortColumn").attr("value", data.sort_column);
              $("#gOrganizeSortOrder").attr("value", data.sort_order);
              $.organize.set_handlers();
	    },
	    "json");
    },

    /**
     * Change the sort order.
     */
    resort: function(column, dir) {
      var url = sort_order_url
        .replace("__ALBUM_ID__", $("#gOrganizeAlbumTree .selected").attr("ref"))
        .replace("__COL__", column)
        .replace("__DIR__", dir);
      $.get(url, {},
	    function(data) {
	      $("#gMicroThumbGrid").html(data.grid);
              $("#gOrganizeSortColumn").attr("value", data.sort_column);
              $("#gOrganizeSortOrder").attr("value", data.sort_order);
	      $.organize.set_handlers();
	    },
	    "json");
    }
  };
})(jQuery);
