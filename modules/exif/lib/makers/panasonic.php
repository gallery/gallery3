<?php defined("SYSPATH") or die("No direct script access.");
//================================================================================================
//================================================================================================
//================================================================================================
/*
	Exifer
	Extracts EXIF information from digital photos.
	
	Copyright � 2003 Jake Olefsky
	http://www.offsky.com/software/exif/index.php
	jake@olefsky.com
	
	Please see exif.php for the complete information about this software.
	
	------------
	
	This program is free software; you can redistribute it and/or modify it under the terms of 
	the GNU General Public License as published by the Free Software Foundation; either version 2 
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
	See the GNU General Public License for more details. http://www.gnu.org/copyleft/gpl.html
*/
//================================================================================================
//================================================================================================
//================================================================================================


//=================
// Looks up the name of the tag for the MakerNote (Depends on Manufacturer)
//====================================================================
function lookup_Panasonic_tag($tag) {
	
	switch($tag) {
		case "0001": $tag = "Quality";break;
		case "0002": $tag = "FirmwareVersion";break;
		case "0003": $tag = "WhiteBalance";break;
		case "0007": $tag = "FocusMode";break;
		case "000f": $tag = "AFMode";break;	
		case "001a": $tag = "ImageStabilizer";break;	
		case "001c": $tag = "MacroMode";break;	
		case "001f": $tag = "ShootingMode";break;	
		case "0020": $tag = "Audio";break;	
		case "0021": $tag = "DataDump";break;
		case "0023": $tag = "WhiteBalanceBias";break;	
		case "0024": $tag = "FlashBias";break;	
		case "0025": $tag = "SerialNumber";break;	
		case "0028": $tag = "ColourEffect";break;	
		case "002a": $tag = "BurstMode";break;	
		case "002b": $tag = "SequenceNumber";break;	
		case "002c": $tag = "Contrast";break;	
		case "002d": $tag = "NoiseReduction";break;	
		case "002e": $tag = "SelfTimer";break;	
		case "0030": $tag = "Rotation";break;	
		case "0032": $tag = "ColorMode";break;	
		case "0036": $tag = "TravelDay";break;	
		
		default: $tag = "unknown:".$tag;break;
	}
	
	return $tag;
}

//=================
// Formats Data for the data type
//====================================================================
function formatPanasonicData($type,$tag,$intel,$data) {

	if($type=="ASCII") {

	} else if($type=="UBYTE" || $type=="SBYTE") {
		$data = bin2hex($data);
		if($intel==1) $data = intel2Moto($data);
		$data=hexdec($data);

		if($tag=="000f") { //AFMode
			if($data == 256) $data = "9-area-focusing";
			else if($data == 16) $data = "1-area-focusing";
      else if($data == 4096) $data = (string) t("3-area-focusing (High speed)");
			else if($data == 4112) $data = (string) t("1-area-focusing (High speed)");
			else if($data == 16) $data = (string) t("1-area-focusing");
			else if($data == 1) $data = (string) t("Spot-focusing");
			else $data = "Unknown (".$data.")";
		} 
	
	} else if($type=="URATIONAL" || $type=="SRATIONAL") {
		$data = unRational($data,$type,$intel);

	} else if($type=="USHORT" || $type=="SSHORT" || $type=="ULONG" || $type=="SLONG" || $type=="FLOAT" || $type=="DOUBLE") {
		$data = rational($data,$type,$intel);
		
		if($tag=="0001") { //Image Quality
			if($data == 2) $data = (string) t("High");
			else if($data == 3) $data = (string) t("Standard");
			else if($data == 6) $data = (string) t("Very High");
			else if($data == 7) $data = (string) t("RAW");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="0003") { //White Balance
			if($data == 1) $data = (string) t("Auto");
			else if($data == 2) $data = (string) t("Daylight");
			else if($data == 3) $data = (string) t("Cloudy");
			else if($data == 4) $data = (string) t("Halogen");
			else if($data == 5) $data = (string) t("Manual");
			else if($data == 8) $data = (string) t("Flash");
			else if($data == 10) $data = (string) t("Black and White");
			else if($data == 11) $data = (string) t("Manual");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="0007") { //Focus Mode
			if($data == 1) $data = (string) t("Auto");
			else if($data == 2) $data = (string) t("Manual");
			else if($data == 4) $data = (string) t("Auto, Focus button");
			else if($data == 5) $data = (string) t("Auto, Continuous");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="001a") { //Image Stabilizer
			if($data == 2) $data = (string) t("Mode 1");
			else if($data == 3) $data = (string) t("Off");
			else if($data == 4) $data = (string) t("Mode 2");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="001c") { //Macro mode
			if($data == 1) $data = (string) t("On");
			else if($data == 2) $data = (string) t("Off");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="001f") { //Shooting Mode
			if($data == 1) $data = (string) t("Normal");
			else if($data == 2) $data = (string) t("Portrait");
			else if($data == 3) $data = (string) t("Scenery");
			else if($data == 4) $data = (string) t("Sports");
			else if($data == 5) $data = (string) t("Night Portrait");
			else if($data == 6) $data = (string) t("Program");
			else if($data == 7) $data = (string) t("Aperture Priority");
			else if($data == 8) $data = (string) t("Shutter Priority");
			else if($data == 9) $data = (string) t("Macro");
			else if($data == 11) $data = (string) t("Manual");
			else if($data == 13) $data = (string) t("Panning");
			else if($data == 14) $data = (string) t("Simple");
			else if($data == 18) $data = (string) t("Fireworks");
			else if($data == 19) $data = (string) t("Party");
			else if($data == 20) $data = (string) t("Snow");
			else if($data == 21) $data = (string) t("Night Scenery");
			else if($data == 22) $data = (string) t("Food");
			else if($data == 23) $data = (string) t("Baby");
			else if($data == 27) $data = (string) t("High Sensitivity");
			else if($data == 29) $data = (string) t("Underwater");
			else if($data == 33) $data = (string) t("Pet");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="0020") { //Audio
			if($data == 1) $data = (string) t("Yes");
			else if($data == 2) $data = (string) t("No");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="0023") { //White Balance Bias
			$data=$data." EV";
		} 
		if($tag=="0024") { //Flash Bias
			$data = $data;
		}
		if($tag=="0028") { //Colour Effect
			if($data == 1) $data = (string) t("Off");
			else if($data == 2) $data = (string) t("Warm");
			else if($data == 3) $data = (string) t("Cool");
			else if($data == 4) $data = (string) t("Black and White");
			else if($data == 5) $data = (string) t("Sepia");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="002a") { //Burst Mode
			if($data == 0) $data = (string) t("Off");
			else if($data == 1) $data = (string) t("Low/High Quality");
			else if($data == 2) $data = (string) t("Infinite");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="002c") { //Contrast
			if($data == 0) $data = (string) t("Standard");
			else if($data == 1) $data = (string) t("Low");
			else if($data == 2) $data = (string) t("High");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="002d") { //Noise Reduction
			if($data == 0) $data = (string) t("Standard");
			else if($data == 1) $data = (string) t("Low");
			else if($data == 2) $data = (string) t("High");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="002e") { //Self Timer
			if($data == 1) $data = (string) t("Off");
			else if($data == 2) $data = (string) t("10s");
			else if($data == 3) $data = (string) t("2s");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="0030") { //Rotation
			if($data == 1) $data = (string) t("Horizontal (normal)");
			else if($data == 6) $data = (string) t("Rotate 90 CW");
			else if($data == 8) $data = (string) t("Rotate 270 CW");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="0032") { //Color Mode
			if($data == 0) $data = (string) t("Normal");
			else if($data == 1) $data = (string) t("Natural");
			else $data = (string) t("Unknown")." (".$data.")";
		}
		if($tag=="0036") { //Travel Day
			$data=$data;
		} 
	} else if($type=="UNDEFINED") {

	} else {
		$data = bin2hex($data);
		if($intel==1) $data = intel2Moto($data);
	}
	
	return $data;
}



//=================
// Panasonic Special data section
//====================================================================
function parsePanasonic($block,&$result) {	
		
	//if($result['Endien']=="Intel") $intel=1;
	//else $intel=0;
	$intel=1;
	
	$model = $result['IFD0']['Model'];

	$place=8; //current place
	$offset=8;
	
	
	$num = bin2hex(substr($block,$place,4));$place+=4;
	if($intel==1) $num = intel2Moto($num);
	$result['SubIFD']['MakerNote']['Offset'] = hexdec($num);
	
	//Get number of tags (2 bytes)
	$num = bin2hex(substr($block,$place,2));$place+=2;
	if($intel==1) $num = intel2Moto($num);
	$result['SubIFD']['MakerNote']['MakerNoteNumTags'] = hexdec($num);
	
	//loop thru all tags  Each field is 12 bytes
	for($i=0;$i<hexdec($num);$i++) {
		
		//2 byte tag
		$tag = bin2hex(substr($block,$place,2));$place+=2;
		if($intel==1) $tag = intel2Moto($tag);
		$tag_name = lookup_Panasonic_tag($tag);
		
		//2 byte type
		$type = bin2hex(substr($block,$place,2));$place+=2;
		if($intel==1) $type = intel2Moto($type);
		lookup_type($type,$size);
		
		//4 byte count of number of data units
		$count = bin2hex(substr($block,$place,4));$place+=4;
		if($intel==1) $count = intel2Moto($count);
		$bytesofdata = $size*hexdec($count);
		
		//4 byte value of data or pointer to data
		$value = substr($block,$place,4);$place+=4;

		
		if($bytesofdata<=4) {
			$data = $value;
		} else {
			$value = bin2hex($value);
			if($intel==1) $value = intel2Moto($value);
			$data = substr($block,hexdec($value)-$offset,$bytesofdata*2);
		}
		$formated_data = formatPanasonicData($type,$tag,$intel,$data);
		
		if($result['VerboseOutput']==1) {
			$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
			if($type=="URATIONAL" || $type=="SRATIONAL" || $type=="USHORT" || $type=="SSHORT" || $type=="ULONG" || $type=="SLONG" || $type=="FLOAT" || $type=="DOUBLE") {
				$data = bin2hex($data);
				if($intel==1) $data = intel2Moto($data);
			}
			$result['SubIFD']['MakerNote'][$tag_name."_Verbose"]['RawData'] = $data;
			$result['SubIFD']['MakerNote'][$tag_name."_Verbose"]['Type'] = $type;
			$result['SubIFD']['MakerNote'][$tag_name."_Verbose"]['Bytes'] = $bytesofdata;
		} else {
			$result['SubIFD']['MakerNote'][$tag_name] = $formated_data;
		}
	}
}

?>