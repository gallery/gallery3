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
      if( o.root == undefined ) o.root = '/1';
      if( o.script == undefined ) o.script = 'rearrange';
      if( o.folderEvent == undefined ) o.folderEvent = 'click';
      if( o.expandSpeed == undefined ) o.expandSpeed= 500;
      if( o.collapseSpeed == undefined ) o.collapseSpeed= 500;
      if( o.expandEasing == undefined ) o.expandEasing = null;
      if( o.collapseEasing == undefined ) o.collapseEasing = null;
      if( o.multiFolder == undefined ) o.multiFolder = true;
      if( o.loadMessage == undefined ) o.loadMessage = 'Loading...';
      
      $(this).each( function() {
        
        function showTree(c, t) {
          $(c).addClass('wait');
          $(".jqueryFileTree.start").remove();
          $.get(o.script + "/" + t, {}, function(data) {
            $(c).find('.start').html('');
            $(c).removeClass('wait').append(data);
            if( o.root == t ) $(c).find('UL:hidden').show(); else $(c).find('UL:hidden').slideDown({ duration: o.expandSpeed, easing: o.expandEasing });
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
                $(this).parent().find('UL').slideUp({ duration: o.collapseSpeed, easing: o.collapseEasing });
                $(this).parent().removeClass('expanded').addClass('collapsed');
              }
            } else {
              h($(this).attr('rel'));
            }
            return false;
          });
          // Prevent A from triggering the # on non-click events
          if( o.folderEvent.toLowerCase != 'click' ) $(t).find('LI A').bind('click', function() { return false; });
        }
        // Loading message
        $(this).html('<ul class="jqueryFileTree start"><li class="wait">' + o.loadMessage + '<li></ul>');
        // Get the initial file list
        showTree( $(this), "" );
      });
    }
  });
  
})(jQuery);