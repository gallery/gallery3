<?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2009 Bharat Mediratta
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
 */
class google_analytics_theme
{
  static function page_bottom($theme)
  {
  $u_o = 1;
  if ( ($theme->item->owner_id != identity::active_user()->id) && (identity::active_user()->admin == 0) ) {
    $u_o = 0;
  }
  
  if ( $u_o == 0 || ( ($u_o == 1) && (module::get_var("google_analytics", "owneradmin_hidden") == 0) ) ) {
 	$google_code = '
  	<!-- Begin Google Analytics -->
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(["_setAccount", "'.module::get_var("google_analytics", "code").'"]);
      _gaq.push(["_trackPageview"]);

     (function() {
       var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;
       ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
       var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);
     })();

      </script>
	<!-- End Google Analytics -->';
  	
  	return $google_code;
   }
  
  }
  
}


