<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
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

/**
 * This is the API for handling exif data.
 */
class Exif_Exif {

  protected static $exif_keys;

  static function extract($item) {
    $keys = array();
    // Only try to extract EXIF from JPEG photos
    if ($item->is_photo() && $item->mime_type == "image/jpeg") {
      $data = array();
      require_once(MODPATH . "exif/vendor/exifer/exif.php");
      $exif_raw = read_exif_data_raw($item->file_path(), false);
      if (isset($exif_raw['ValidEXIFData'])) {
        foreach(static::_keys() as $field => $exifvar) {
          if (isset($exif_raw[$exifvar[0]][$exifvar[1]])) {
            $value = $exif_raw[$exifvar[0]][$exifvar[1]];
            $keys[$field] = Encoding::convert_to_utf8($value);

            if ($field == "DateTime") {
              $time = strtotime($value);
              if ($time > 0) {
                $item->captured = $time;
              }
            } else if ($field == "Caption" && !$item->description) {
              $item->description = $value;
            }
          }
        }
      }
    }

    // Also try to extract IPTC from photos (not just JPEG)
    if ($item->is_photo()) {
      $iptc = Photo::get_file_iptc($item->file_path());

      if (!empty($iptc["Caption"])) {
        $keys["Caption"] = implode(" ", $iptc["Caption"]); // implode with space
        if (!$item->description) {
          $item->description = $keys["Caption"];
        }
      }

      if (!empty($iptc["Keywords"])) {
        $keys["Keywords"] = implode(",", $iptc["Keywords"]); // implode with comma
      }
    }
    $item->save();

    $record = $item->exif_record;
    if (!$record->loaded()) {
      $record->item_id = $item->id;
    }
    $record->data = serialize($keys);
    $record->key_count = count($keys);
    $record->dirty = 0;
    $record->save();
  }

  static function get($item) {
    $exif = array();
    $record = $item->exif_record;
    if (!$record->loaded()) {
      return array();
    }

    $definitions = static::_keys();
    $keys = unserialize($record->data);
    foreach ($keys as $key => $value) {
      $exif[] = array("caption" => $definitions[$key][2], "value" => $value);
    }

    return $exif;
  }

  protected static function _keys() {
    if (!isset(static::$exif_keys)) {
      static::$exif_keys = array(
        "Make"            => array("IFD0",   "Make",              t("Camera Maker"),     ),
        "Model"           => array("IFD0",   "Model",             t("Camera Model"),     ),
        "Aperture"        => array("SubIFD", "FNumber",           t("Aperture"),         ),
        "ColorSpace"      => array("SubIFD", "ColorSpace",        t("Color Space"),      ),
        "ExposureBias"    => array("SubIFD", "ExposureBiasValue", t("Exposure Value"),   ),
        "ExposureProgram" => array("SubIFD", "ExposureProgram",   t("Exposure Program"), ),
        "ExposureTime"    => array("SubIFD", "ExposureTime",      t("Exposure Time"),    ),
        "Flash"           => array("SubIFD", "Flash",             t("Flash"),            ),
        "FocalLength"     => array("SubIFD", "FocalLength",       t("Focal Length"),     ),
        "ISO"             => array("SubIFD", "ISOSpeedRatings",   t("ISO"),              ),
        "MeteringMode"    => array("SubIFD", "MeteringMode",      t("Metering Mode"),    ),
        "DateTime"        => array("SubIFD", "DateTimeOriginal",  t("Date/Time"),        ),
        "Copyright"       => array("IFD0",   "Copyright",         t("Copyright"),        ),
        "ImageType"       => array("IFD0",   "ImageType",         t("Image Type"),       ),
        "Orientation"     => array("IFD0",   "Orientation",       t("Orientation"),      ),
        "ResolutionUnit"  => array("IFD0",   "ResolutionUnit",    t("Resolution Unit"),  ),
        "xResolution"     => array("IFD0",   "xResolution",       t("X Resolution"),     ),
        "yResolution"     => array("IFD0",   "yResolution",       t("Y Resolution"),     ),
        "Compression"     => array("IFD1",   "Compression",       t("Compression"),      ),
        "BrightnessValue" => array("SubIFD", "BrightnessValue",   t("Brightness Value"), ),
        "Contrast"        => array("SubIFD", "Contrast",          t("Contrast"),         ),
        "ExposureMode"    => array("SubIFD", "ExposureMode",      t("Exposure Mode"),    ),
        "FlashEnergy"     => array("SubIFD", "FlashEnergy",       t("Flash Energy"),     ),
        "Saturation"      => array("SubIFD", "Saturation",        t("Saturation"),       ),
        "SceneType"       => array("SubIFD", "SceneType",         t("Scene Type"),       ),
        "Sharpness"       => array("SubIFD", "Sharpness",         t("Sharpness"),        ),
        "SubjectDistance" => array("SubIFD", "SubjectDistance",   t("Subject Distance"), ),
        "Caption"         => array("IPTC",   "Caption",           t("Caption"),          ),
        "Keywords"        => array("IPTC",   "Keywords",          t("Keywords"),         )
      );
    }
    return static::$exif_keys;
  }

  static function stats() {
    $missing_exif = ORM::factory("Item")
      ->with("exif_record")
      ->where("item.type", "=", "photo")
      ->and_where_open()
      ->where("exif_record.item_id", "IS", null)
      ->or_where("exif_record.dirty", "=", 1)
      ->and_where_close()
      ->count_all();

    $total_items = ORM::factory("Item")->where("type", "=", "photo")->count_all();
    if (!$total_items) {
      return array(0, 0, 0);
    }
    return array($missing_exif, $total_items,
                 round(100 * (($total_items - $missing_exif) / $total_items)));
  }

  static function check_index() {
    list ($remaining) = Exif::stats();
    if ($remaining) {
      SiteStatus::warning(
        t('Your Exif index needs to be updated.  <a href="%url" class="g-dialog-link">Fix this now</a>',
          array("url" => HTML::mark_clean(URL::site("admin/maintenance/start/Hook_ExifTask::update_index?csrf=__CSRF__")))),
        "exif_index_out_of_date");
    }
  }
}
