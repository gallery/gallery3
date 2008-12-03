/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This script is based on the 
 *  jQuery File Tree Plugin, 
 *  developed by Cory S.N. LaViska
 *  A Beautiful Site (http://abeautifulsite.net/)
 *  Originally released under  a Creative Commons License and is copyrighted 
 *    (C)2008 by Cory S.N. LaViska.
 *    For details, visit http://creativecommons.org/licenses/by/3.0/us/
 *
 */
if(jQuery) (function($){
  
  $.extend($.fn, {

    RearrangeTree: function(o, h) {
      // Defaults
      if( !o ) var o = {};
      if( o.script == undefined ) o.script = 'rearrange';
      if( o.folderEvent == undefined ) o.folderEvent = 'click';
      if( o.expandSpeed == undefined ) o.expandSpeed= 500;
      if( o.collapseSpeed == undefined ) o.collapseSpeed= 500;
      if( o.expandEasing == undefined ) o.expandEasing = null;
      if( o.collapseEasing == undefined ) o.collapseEasing = null;
      if( o.multiFolder == undefined ) o.multiFolder = true;
      if( o.loadMessage == undefined ) o.loadMessage = 'Loading...';

      function showTree(c, t) {
        $(c).addClass('wait');
        $(".jqueryFileTree.start").remove();
        $.get(o.script + "/" + t, {}, function(data) {
          $(c).find('.start').html('');
          $(c).removeClass('wait').append(data);
          $(c).find('UL:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });
          $(c).find('UL:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });
          $(c).find('li.directory').droppable({
            accept: "#gAddAlbum",
            drop: function(ev, ui) {
              addAlbum(ui.element);
            }
          });
          if ($(c).hasClass('treeitem')) {
            $(c).find("li", this.element).draggable({
              helper: 'clone',
              containment: "#gRearrange",
              opacity: .6,
              revert: "invalid"
            });
          }

          $(c).find('.directory').droppable({
            accept: "li",
            greedy: true,
            hoverClass: "droppable-hover",
            drop: function(ev, ui) {
              source_element = ui.draggable;
              source_parent = source_element.parent();
              target = ui.element;
              alert(source_element.attr("id") + "\n" + target.attr("id"));
            }
          });
          bindTree(c);
        });
      }

      function bindTree(t) {
        $(t).find('LI A').bind(o.folderEvent, function() {
          if( $(this).parent().hasClass('directory') ) {
            if( $(this).parent().hasClass('collapsed') ) {
              // Expand
              if( !o.multiFolder ) {
                $(this).parent().parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
                $(this).parent().parent().find('LI.directory').removeClass('expanded').addClass('collapsed');
              }
              $(this).parent().find('UL').remove(); // cleanup
              showTree( $(this).parent(), escape($(this).attr('rel')) );
              $(this).parent().removeClass('collapsed').addClass('expanded');
            } else {
              // Collapse
              collapse($(this).parent());
            }
          } else {
            h($(this).attr('rel'));
          }
          return false;
        });
        // Prevent A from triggering the # on non-click events
        if( o.folderEvent.toLowerCase != 'click' ) $(t).find('LI A').bind('click', function() { return false; });
      }

      function addAlbum(parent) {
        $("#gAddAlbumPopupClose").click(function() {  
          $("#gAddAlbumPopup").css({"display": "none"});
        });

        $.get("form/add/albums/" + parent.attr("id"), {}, function(data) {
          $("#gAddAlbumArea").html(data);
          $("#gAddAlbumForm").ajaxForm({
            complete: function(xhr, statusText) {
              if (xhr.status == 200) {
                $("#gAddAlbumPopup").css({"display": "none"});
                collapse(parent);
                showTree(parent, parent.attr("id"));
              }
            }
          });
          $("#gAddAlbumPopup").css({
            "display": "block",
            "top": $("#gAddAlbum").offsetTop + $("#gAddAlbum").offsetHeight,
            "left":$("#gAddAlbum").offsetLeft
          });
        });
      }

      function collapse(parent) {
        parent.find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
        parent.removeClass('expanded').addClass('collapsed');
      }

      function getParent(element) {
        parent = element.parent();
        while(parent != null) {
          if ($(parent).hasClass("treeitem")) {
            return parent;
          }
          parent = parent.parent();
        }
        return null;
      }

      $("#gAddAlbum").draggable({
        helper: 'clone',
        containment: "#gRearrange",
        opacity: .6,
        revert: "invalid"
      });

      $("#gDeleteItem").droppable({
        accept: "li",
        tolerance: "pointer",
        drop: function(ev, ui) {
          var element = ui.draggable;
          var parent = getParent(element);

          var id = element.attr("id");
          var anchor = $(element).find("a");  
          if (confirm("Do you really want to delete " + $(element).find("a")[0].textContent)) {
            $.ajax({
              url: "items/" + id,
              success: function(data, textStatus) {
                collapse(parent);
                showTree(parent, parent.attr("id"));
              },
              error: function(xhr, textStatus, errorThrown) {
                alert(xhr.status);
                alert(textStatus);
                alert(errorThrown);
              },
              type: "DELETE"
            });
          }
        }
      });

      $(this).each( function() {
        // Loading message
        $(this).html('<ul class="jqueryFileTree start"><li class="wait">' + o.loadMessage + '<li></ul>');
        // Get the initial file list
        showTree( $(this), "" );
      });
    }
  });
  
})(jQuery);