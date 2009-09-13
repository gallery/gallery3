(function($) {
  $.organize = {
    micro_thumb_draggable: {
      handle: ".ui-selected",
      distance: 10,
      cursorAt: { left: -10, top: -10},
      appendTo: "#gOrganizeMicroThumbPanel",
      helper: function(event, ui) {
        var selected = $(".ui-draggable.ui-selected img");
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
        $("#gOrganizeMicroThumbPanel .ui-selected").hide();
      },

      drag: function(event, ui) {
        var top = $("#gOrganizeMicroThumbPanel").offset().top;
        var height = $("#gOrganizeMicroThumbPanel").height();
        if (ui.offset.top > height + top - 20) {
          $("#gOrganizeMicroThumbPanel").get(0).scrollTop += 100;
        } else if (ui.offset.top < top + 20) {
          $("#gOrganizeMicroThumbPanel").get(0).scrollTop = Math.max(0, $("#gOrganizeMicroThumbPanel").get(0).scrollTop - 100);
        }
      }
    },

    content_droppable: {
      accept: "*",
      tolerance: "pointer",
      greedy: true,
      drop: function(event, ui) {
        var before_or_after = null;
        var target_id = null;
        if ($(".currentDropTarget").length) {
          before_or_after = $(".currentDropTarget").css("borderLeftStyle") == "solid" ? "before" : "after";
          target_id = $(".currentDropTarget").attr("ref");
        } else {
          before_or_after = "after";
          target_id = $("#gOrganizeMicroThumbGrid li:last").attr("ref");
        }
        $.organize.do_drop({
          url: rearrange_url
	    .replace("__TARGET_ID__", target_id)
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
          $(".ui-selected").show();
          $(".gOrganizeMicroThumbGridCell").css("borderStyle", "none");
        } else {
          $.organize.do_drop({
            url: move_url.replace("__ALBUM_ID__", $(event.target).attr("ref")),
            source: $(ui.helper).children("img")
          });
        }
      }
    },

    do_drop: function(options) {
      $("#gOrganizeMicroThumbPanel").selectable("destroy");
      var source_ids = [];
      $(options.source).each(function(i) {
        source_ids.push($(this).attr("ref"));
      });

      if (source_ids.length) {
	$.post(options.url,
	       { "source_ids[]": source_ids },
	       function(data) {
		 $.organize._refresh(data);
	       },
	      "json");
      }
    },

    _refresh: function(data) {
      if (data.tree) {
        $("#gOrganizeAlbumTree").html(data.tree);
      }
      if (data.grid) {
        $("#gOrganizeMicroThumbGrid").html(data.grid);
        $("#gOrganizeSortColumn").attr("value", data.sort_column);
        $("#gOrganizeSortOrder").attr("value", data.sort_order);
      }
      $.organize.set_handlers();
    },

    mouse_move_handler: function(event) {
      if ($(".gDragHelper").length) {
        $(".gOrganizeMicroThumbGridCell").css({borderStyle: "hidden", margin: "4px"});
        $(".currentDropTarget").removeClass("currentDropTarget");
        var borderStyle = event.pageX < $(this).offset().left + $(this).width() / 2 ?
          {borderLeftStyle: "solid", marginLeft: "2px"} : {borderRightStyle: "solid", marginRight: "2px"};
        $(this).addClass("currentDropTarget")
          .css(borderStyle);
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
        $("#gOrganizeMicroThumbPanel").height($("#gDialog").innerHeight() - 90);
        $("#gOrganizeTreeContainer").height($("#gDialog").innerHeight() - 59);
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
      $("#gOrganizeMicroThumbPanel")
	.selectable({filter: ".gOrganizeMicroThumbGridCell"})
	.droppable($.organize.content_droppable);
      $(".gOrganizeMicroThumbGridCell")
	.draggable($.organize.micro_thumb_draggable)
        .mouseleave($.organize.mouse_leave_handler)
	.mousemove($.organize.mouse_move_handler);
      $(".gOrganizeAlbum").droppable($.organize.branch_droppable);
      $(".gOrganizeAlbumText").click($.organize.show_album);
      $("#gOrganizeAlbumTree .ui-icon-plus,#gOrganizeAlbumTree .ui-icon-minus").click($.organize.toggle_branch);
    },

    toggle_branch: function(event) {
      event.preventDefault();
      var target = $(event.currentTarget);
      var branch = $(target).parent();
      var id = $(event.currentTarget).parent().attr("ref");

      if ($(target).hasClass("ui-icon-plus")) {
	// Expanding
	if (!branch.find("ul").length) {
	  $.get(tree_url.replace("__ALBUM_ID__", id), { },
	    function(data) {
	      branch.replaceWith(data);
	      $.organize.set_handlers();
	    }
	  );
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
      if ($(event.currentTarget).hasClass("selected")) {
        return;
      }
      var parent = $(event.currentTarget).parents(".gOrganizeBranch");
      if ($(parent).hasClass("gViewOnly")) {
        return;
      }
      $("#gOrganizeMicroThumbPanel").selectable("destroy");
      var id = $(event.currentTarget).attr("ref");
      $("#gOrganizeAlbumTree .selected").removeClass("selected");
      $(".gOrganizeAlbumText[ref=" + id + "]").addClass("selected");
      var url = $("#gOrganizeMicroThumbPanel").attr("ref").replace("__ITEM_ID__", id).replace("__OFFSET__", 0);
      $.get(url, {},
	    function(data) {
	      $("#gOrganizeMicroThumbGrid").html(data.grid);
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
	      $("#gOrganizeMicroThumbGrid").html(data.grid);
              $("#gOrganizeSortColumn").attr("value", data.sort_column);
              $("#gOrganizeSortOrder").attr("value", data.sort_order);
	      $.organize.set_handlers();
	    },
	    "json");
    }
  };
})(jQuery);
