<?php defined("SYSPATH") or die("No direct script access.");
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
 */
class remote extends remote_Core {

  static function post($url, $post_data_array, $extra_headers=array()) {
    $post_data_raw = self::_encode_post_data($post_data_array, $extra_headers);
    
    /* Read the web page into a buffer */
    list ($response_status, $response_headers, $response_body) =
      self::do_request($url, 'POST', $extra_headers, $post_data_raw);
    
    return array($response_body, $response_status, $response_headers);
  }

  static function success($response_status) {
    return preg_match("/^HTTP\/\d+\.\d+\s2\d{2}(\s|$)/", trim($response_status));
  }

  /**
   * Encode the post data.  For each key/value pair, urlencode both the key and the value and then
   * concatenate together.  As per the specification, each key/value pair is separated with an
   * ampersand (&)
   * @param array $post_data_array the key/value post data
   * @param array $extra_headers extra headers to pass to the server
   * @return string the encoded post data
   */
  private static function _encode_post_data($post_data_array, &$extra_headers) {
    $post_data_raw = '';
    foreach ($post_data_array as $key => $value) {
      if (!empty($post_data_raw)) {
        $post_data_raw .= '&';
      }
      $post_data_raw .= urlencode($key) . '=' . urlencode($value);
    }
    
    $extra_headers['Content-Type'] = 'application/x-www-form-urlencoded';
    $extra_headers['Content-Length'] = strlen($post_data_raw);
    
    return $post_data_raw;
  }

  /**
   * A single request, without following redirects
   *
   * @todo: Handle redirects? If so, only for GET (i.e. not for POST), and use G2's WebHelper_simple::_parseLocation logic.
   */
  static function do_request($url, $method='GET', $headers=array(), $body='') {
    /* Convert illegal characters */
    $url = str_replace(' ', '%20', $url);
    
    $url_components = self::_parse_url_for_fsockopen($url);
    $handle = fsockopen(
      $url_components['fsockhost'], $url_components['port'], $errno, $errstr, 5);
    if (empty($handle)) {
      // log "Error $errno: '$errstr' requesting $url";
      return array(null, null, null);
    }
    
    $header_lines = array('Host: ' . $url_components['host']);
    foreach ($headers as $key => $value) {
      $header_lines[] = $key . ': ' . $value;
    }
    
    $success = fwrite($handle, sprintf("%s %s HTTP/1.0\r\n%s\r\n\r\n%s",
                                       $method,
                                       $url_components['uri'],
                                       implode("\r\n", $header_lines),
                                       $body));
    if (!$success) {
      // Zero bytes written or false was returned
      // log "fwrite failed in requestWebPage($url)" . ($success === false ? ' - false' : ''
      return array(null, null, null);
    }
    fflush($handle);
    
    /*
     * Read the status line.  fgets stops after newlines.  The first line is the protocol
     * version followed by a numeric status code and its associated textual phrase.
     */
    $response_status = trim(fgets($handle, 4096));
    if (empty($response_status)) {
      // 'Empty http response code, maybe timeout'
      return array(null, null, null);
    }
    
    /* Read the headers */
    $response_headers = array();
    while (!feof($handle)) {
      $line = trim(fgets($handle, 4096));
      if (empty($line)) {
        break;
      }
      
      /* Normalize the line endings */
      $line = str_replace("\r", '', $line);
      
      list ($key, $value) = explode(':', $line, 2);
      if (isset($response_headers[$key])) {
        if (!is_array($response_headers[$key])) {
          $response_headers[$key] = array($response_headers[$key]);
        }
        $response_headers[$key][] = trim($value);
      } else {
        $response_headers[$key] = trim($value);
      }
    }
    
    /* Read the body */
    $response_body = '';
    while (!feof($handle)) {
      $response_body .= fread($handle, 4096);
    }
    fclose($handle);

    return array($response_status, $response_headers, $response_body);
  }

  /**
   * Prepare for fsockopen call.
   * @param string $url
   * @return array url components
   * @access private
   */
  private static function _parse_url_for_fsockopen($url) {
    $url_components = parse_url($url);
    if (strtolower($url_components['scheme']) == 'https') {
      $url_components['fsockhost'] = 'ssl://' . $url_components['host'];
      $default_port = 443;
    } else {
      $url_components['fsockhost'] = $url_components['host'];
      $default_port = 80;
    }
    if (empty($url_components['port'])) {
      $url_components['port'] = $default_port;
    }
    if (empty($url_components['path'])) {
      $url_components['path'] = '/';
    }
    $uri = $url_components['path']
      . (empty($url_components['query']) ? '' : '?' . $url_components['query']);
    /* Unescape ampersands, since if the url comes from form input it will be escaped */
    $url_components['uri'] = str_replace('&amp;', '&', $uri);
    return $url_components;
  }
}

