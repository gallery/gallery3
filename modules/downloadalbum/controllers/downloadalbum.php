<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
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
class downloadalbum_Controller extends Controller {

  /**
   * Generate a ZIP on-the-fly.
   */
  public function zip($container_type, $id) {
    switch($container_type) {
      case "album":
        $container = ORM::factory("item", $id);
        if (!$container->is_album()) {
          throw new Kohana_Exception('container is not an album: '.$container->relative_path());
        }

        $zipname = (empty($container->name))
            ? 'Gallery.zip' // @todo purified_version_of($container->title).'.zip'
            : $container->name.'.zip';
        break;

     case "tag":
        // @todo: if the module is not installed, it crash
        $container = ORM::factory("tag", $id);
        if (is_null($container->name)) {
          throw new Kohana_Exception('container is not a tag: '.$id);
        }

        $zipname = $container->name.'.zip';
        break;

     default:
       throw new Kohana_Exception('unhandled container type: '.$container_type);
   }

   $files = $this->getFilesList($container);

    // Calculate ZIP size (look behind for details)
    $zipsize = 22;
    foreach($files as $f_name => $f_path) {
      $zipsize += 76 + 2*strlen($f_name) + filesize($f_path);
    }

    // Send headers
    $this->prepareOutput();
    $this->sendHeaders($zipname, $zipsize);

    // Generate and send ZIP file
    // http://www.pkware.com/documents/casestudies/APPNOTE.TXT (v6.3.2)
    $lfh_offset = 0;
    $cds = '';
    $cds_offset = 0;
    foreach($files as $f_name => $f_path) {
      $f_namelen = strlen($f_name);
      $f_size = filesize($f_path);
      $f_mtime = $this->unix2dostime(filemtime($f_path));
      $f_crc32 = $this->fixBug45028(hexdec(hash_file('crc32b', $f_path, false)));

      // Local file header
      echo pack('VvvvVVVVvva' . $f_namelen,
          0x04034b50,         // local file header signature (4 bytes)
          0x0a,               // version needed to extract (2 bytes) => 1.0
          0x0800,             // general purpose bit flag (2 bytes) => UTF-8
          0x00,               // compression method (2 bytes) => store
          $f_mtime,           // last mod file time and date (4 bytes)
          $f_crc32,           // crc-32 (4 bytes)
          $f_size,            // compressed size (4 bytes)
          $f_size,            // uncompressed size (4 bytes)
          $f_namelen,         // file name length (2 bytes)
          0,                  // extra field length (2 bytes)

          $f_name             // file name (variable size)
                              // extra field (variable size) => n/a
      );

      // File data
      readfile($f_path);

      // Data descriptor (n/a)

      // Central directory structure: File header
      $cds .= pack('VvvvvVVVVvvvvvVVa' . $f_namelen,
          0x02014b50,         // central file header signature (4 bytes)
          0x031e,             // version made by (2 bytes) => v3 / Unix
          0x0a,               // version needed to extract (2 bytes) => 1.0
          0x0800,             // general purpose bit flag (2 bytes) => UTF-8
          0x00,               // compression method (2 bytes) => store
          $f_mtime,           // last mod file time and date (4 bytes)
          $f_crc32,           // crc-32 (4 bytes)
          $f_size,            // compressed size (4 bytes)
          $f_size,            // uncompressed size (4 bytes)
          $f_namelen,         // file name length (2 bytes)
          0,                  // extra field length (2 bytes)
          0,                  // file comment length (2 bytes)
          0,                  // disk number start (2 bytes)
          0,                  // internal file attributes (2 bytes)
          0x81b40000,         // external file attributes (4 bytes) => chmod 664
          $lfh_offset,        // relative offset of local header (4 bytes)

          $f_name             // file name (variable size)
                              // extra field (variable size) => n/a
                              // file comment (variable size) => n/a
      );

      // Update local file header/central directory structure offset
      $cds_offset = $lfh_offset += 30 + $f_namelen + $f_size;
    }

    // Archive decryption header (n/a)
    // Archive extra data record (n/a)

    // Central directory structure: Digital signature (n/a)
    echo $cds; // send Central directory structure

    // Zip64 end of central directory record (n/a)
    // Zip64 end of central directory locator (n/a)

    // End of central directory record
    $numfile = count($files);
    $cds_len = strlen($cds);
    echo pack('VvvvvVVv',
        0x06054b50,             // end of central dir signature (4 bytes)
        0,                      // number of this disk (2 bytes)
        0,                      // number of the disk with the start of
                                // the central directory (2 bytes)
        $numfile,               // total number of entries in the
                                // central directory on this disk (2 bytes)
        $numfile,               // total number of entries in the
                                // central directory (2 bytes)
        $cds_len,               // size of the central directory (4 bytes)
        $cds_offset,            // offset of start of central directory
                                // with respect to the
                                // starting disk number (4 bytes)
        0                       // .ZIP file comment length (2 bytes)
                                // .ZIP file comment (variable size)
    );
  }


  /**
   * Return the files that must be included in the archive.
   */
  private function getFilesList($container) {
    $files = array();

    if( $container instanceof Item_Model && $container->is_album() ) {
      $container_realpath = realpath($container->file_path().'/../');

      $items = $container->viewable()
          ->descendants(null, null, array(array("type", "<>", "album")));
      foreach($items as $i) {
        if (!access::can('view_full', $i)) {
          continue;
        }

        $i_realpath = realpath($i->file_path());
        if (!is_readable($i_realpath)) {
          continue;
        }

        $i_relative_path = str_replace($container_realpath.DIRECTORY_SEPARATOR, '', $i_realpath);
        $i_relative_path = str_replace(DIRECTORY_SEPARATOR, '/', $i_relative_path);
        $files[$i_relative_path] = $i_realpath;
      }

    } else if( $container instanceof Tag_Model ) {
      $items = $container->items();
      foreach($items as $i) {
        if (!access::can('view_full', $i)) {
          continue;
        }

        if( $i->is_album() ) {
          foreach($this->getFilesList($i) as $f_name => $f_path) {
            $files[$container->name.'/'.$f_name] = $f_path;
          }

        } else {
          $i_realpath = realpath($i->file_path());
          if (!is_readable($i_realpath)) {
            continue;
          }

          $i_relative_path = $container->name.'/'.$i->name;
          $files[$i_relative_path] = $i_realpath;
        }
      }
    }

    if (count($files) === 0) {
      throw new Kohana_Exception('no zippable files in ['.$container->name.']');
    }

    return $files;
  }


  /**
   * See system/helpers/download.php
   */
  private function prepareOutput() {
    // Close output buffers
    Kohana::close_buffers(FALSE);
    // Clear any output
    Event::add('system.display', create_function('', 'Kohana::$output = "";'));
  }

  /**
   * See system/helpers/download.php
   */
  private function sendHeaders($filename, $filesize = null) {
    if (!is_null($filesize)) {
      header('Content-Length: '.$filesize);
    }

    // Retrieve MIME type by extension
    $mime = Kohana::config('mimes.'.strtolower(substr(strrchr($filename, '.'), 1)));
    $mime = empty($mime) ? 'application/octet-stream' : $mime[0];
    header("Content-Type: $mime");
    header('Content-Transfer-Encoding: binary');

    // Send headers necessary to invoke a "Save As" dialog
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    // Prevent caching
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    $pragma = 'no-cache';
    $cachecontrol = 'no-cache, max-age=0';

    // request::user_agent('browser') seems bugged
    if (request::user_agent('browser') === 'Internet Explorer'
        || stripos(request::user_agent(), 'msie') !== false
        || stripos(request::user_agent(), 'internet explorer') !== false)
    {
      if (request::protocol() === 'https') {
        // See http://support.microsoft.com/kb/323308/en-us
        $pragma = 'cache';
        $cachecontrol = 'private';

      } else if (request::user_agent('version') <= '6.0') {
        $pragma = '';
        $cachecontrol = 'must-revalidate, post-check=0, pre-check=0';
      }
    }

    header('Pragma: '.$pragma);
    header('Cache-Control: '.$cachecontrol);
  }

  /**
   * @return integer             DOS date and time
   * @param  integer _timestamp  Unix timestamp
   * @desc                       returns DOS date and time of the timestamp
   */
  private function unix2dostime($timestamp)
  {
    $timebit = getdate($timestamp);

    if ($timebit['year'] < 1980) {
      return (1 << 21 | 1 << 16);
    }

    $timebit['year'] -= 1980;

    return ($timebit['year']    << 25 | $timebit['mon']     << 21 |
            $timebit['mday']    << 16 | $timebit['hours']   << 11 |
            $timebit['minutes'] << 5  | $timebit['seconds'] >> 1);
  }

  /**
   * See http://bugs.php.net/bug.php?id=45028
   */
  private function fixBug45028($hash) {
    $output = $hash;

    if( version_compare(PHP_VERSION, '5.2.7', '<') ) {
      $str = str_pad(dechex($hash), 8, '0', STR_PAD_LEFT);
      $output = hexdec($str{6}.$str{7}.$str{4}.$str{5}.$str{2}.$str{3}.$str{0}.$str{1});
    }

    return $output;
  }
}
