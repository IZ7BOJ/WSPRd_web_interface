<?php
/******************************************************************************************
This file is a part of WSPRd SIMPLE WEB STATICSTICS GENERATOR FROM WSPRd LOG FILE
This script may have a lot of bugs, problems and it's written in very non-efficient way without a lot of good programming rules. But it works for me.
Author: Alfredo IZ7BOJ iz7boj[--at--]gmail.com
You can modify this program, but please give a credit to original author. Program is free for non-commercial use only.
(C) Alfredo IZ7BOJ 2021
*******************************************************************************************/

function logexists()
{
	global $logpath;
	global $version;
	if(!file_exists($logpath)) {
		echo '<font color="red" size="6"><b>Error. Cannot open WSPRd log file at '.$logpath.'.</b></font>';
		echo '<br><br>Please check, if log file path in config.php is set correctly.<br>Plase check, if file '.$logpath.' exists.';
		echo '<br><br><b>Pointless to continue.</b>';
		echo '<br><br><br><br><br><br><center>Wspr Simple Webstat version '.$version.' by Alfredo IZ7BOJ (C) 2021</center>';
		die();
	}
}

function cmp($a, $b)
{
    if ($a[2] == $b[2]) {
        return 0;
    }
    return ($a[2] > $b[2]) ? -1 : 1;
}

function configparse($logpath)
{
global $configvers;
global $configcall;
global $configloc;
global $configfreq;
global $configppm;
global $configgain;
global $configuptime;
global $configdev;

$lines=0;
$logfile = file($logpath); //read log file
$linesinlog = count($logfile); //get number of lines
while ($lines < $linesinlog) { //read line by line
	$line = $logfile[$lines];
	
	if (strpos($line, "Callsign") !== false) {
		$configcall=substr($line, 17,-1);
	}
	elseif (strpos($line, "Locator") !== false) {
		$configloc=substr($line, 17,-1);
	}
	elseif (strpos($line, "Dial freq.") !== false) {
		$configfreq=substr($line, 17,-1);
	}
	elseif (strpos($line, "PPM factor") !== false) {
		$configppm=substr($line, 17,-1);
	}
	elseif (strpos($line, "Gain") !== false) {
		$configgain=substr($line, 17,-1);
	}
	elseif (strpos($line, "Version") !== false) {
		$configvers=substr($line, -5,-1);
		$configuptime=substr($line,23,18);
	}
	elseif (strpos($line, "Using device") !== false) {
		$configdev=substr($line, strpos($line,":")+1,-1);
	}
	$lines++;
	}
}

function freq2band($freq)
{

$band=NULL;

switch ($freq) {
	case (intval($freq)==144):
		$band="2m";
		break;
	case (intval($freq)==70):
		$band="4m";
		break;
	case (intval($freq)==50):
		$band="6m";
		break;
	case (intval($freq)==28):
		$band="10m";
		break;
	case (intval($freq)==24):
		$band="12m";
		break;
	case (intval($freq)==21):
		$band="15m";
		break;
	case (intval($freq)==18):
		$band="17m";
		break;
	case (intval($freq)==14):
		$band="20m";
		break;
	case (intval($freq)==10):
		$band="30m";
		break;
	case (intval($freq)==7):
		$band="40m";
		break;
	case (intval($freq)==3):
		$band="80m";
		break;
	case (intval($freq)==1):
		$band="160m";
		break;
	case (round($freq,2)==0.47):
		$band="600m";
		break;
	case (round($freq,2)==0.13):
		$band="2200m";
		break;
	} //close switch
return $band;
} //close function

function stationspivot($frame) //function for parsing station information
{
global $receivedstations;
global $time;

	$frame = substr($frame, 8, -1); //cut first part
	$parts = preg_split('/\s+/', $frame); //split all fields
	$stationcall = $parts[6];
	if (((strtotime($parts[0]." ".$parts[1]))+ date('Z'))>$time) { // if in timerange
		if (($stationcall !== "<...>")AND(count($parts)==9)) { //if call is not <...> and all the fields are present
			if (array_key_exists($stationcall, $receivedstations)) { //if this callsign is already on stations list
				$receivedstations[$stationcall][0]++; //increment the number of frames from this station
			} else {
				$receivedstations[$stationcall][0] = 1; //add new callsign to the list
			}
			$receivedstations[$stationcall][1] = $parts[0]; //add last date
			$receivedstations[$stationcall][2] = $parts[1]; //add last time
			$receivedstations[$stationcall][3] = $parts[7]; //add locator
			$receivedstations[$stationcall][4] = freq2band($parts[4]); //add frequency
		} //close if call is not <...>
	} //close if in timerange
} //close function


//function for load calc
function rxload()
{
global $logfile;
global $callraw;
global $lines;
global $rxframespermin;

$count=0;
$index1=17; //skip first part of general information
//find the time of last rx packet in log
while (($index1<$lines)AND(strpos($logfile[$lines - $index1],"Spot :"))) {
        $index1++;
        }
$time1 = strtotime(substr($logfile[$lines - $index1], 8, 19));
$index2=$index1+1;

//go back to last-20  received packets and take time
while (($index2<$lines)AND($count<19)) {
        if(strpos($logfile[$lines - $index2],"Spot :")!==false) {
                $time2 = strtotime(substr($logfile[$lines - $index2], 8, 19));
                $count++;
                }
	$index2++;
}

$rxframespermin = $count / (($time1 - $time2) / 60);
return $rxframespermin;
}

/* 
 * PHP code snippet to calculate the distance and bearing between two
 * maidenhead QTH locators. 
 *
 * Written by Fabian Kurz, DJ1YFK; losely based on wwl+db by VA3DB.
 *
 */


function valid_locator ($loc) {
	if (preg_match("/^[A-R]{2}[0-9]{2}[A-X]{2}$/", $loc)) {
		return 1;
	}
	else {
		return 0;
	}
}

function loc_to_latlon ($loc) {
	/* lat */
	$l[0] = 
	(ord(substr($loc, 1, 1))-65) * 10 - 90 +
	(ord(substr($loc, 3, 1))-48) +
	(ord(substr($loc, 5, 1))-65) / 24 + 1/48;
	$l[0] = deg_to_rad($l[0]);
	/* lon */
	$l[1] = 
	(ord(substr($loc, 0, 1))-65) * 20 - 180 +
	(ord(substr($loc, 2, 1))-48) * 2 +
	(ord(substr($loc, 4, 1))-65) / 12 + 1/24;
	$l[1] = deg_to_rad($l[1]);

	return $l;
}

function deg_to_rad ($deg) {
	return (M_PI * $deg/180);
}

function rad_to_deg ($rad) {
	return (($rad/M_PI) * 180);
}

function bearing_dist($loc1, $loc2) {
	$loc1=strtoupper($loc1);
	$loc2=strtoupper($loc2);
	if (!valid_locator($loc1) || !valid_locator($loc2)) {
		return 0;
	}
		
	$l1 = loc_to_latlon($loc1);
	$l2 = loc_to_latlon($loc2);

	$co = cos($l1[1] - $l2[1]) * cos($l1[0]) * cos($l2[0]) +
			sin($l1[0]) * sin($l2[0]);
	$ca = atan2(sqrt(1 - $co*$co), $co);
	$az = atan2(sin($l2[1] - $l1[1]) * cos($l1[0]) * cos($l2[0]),
				sin($l2[0]) - sin($l1[0]) * cos($ca));

	if ($az < 0) {
		$az += 2 * M_PI;
	}

	$ret['km'] = round(6371*$ca);
	$ret['deg'] = round(rad_to_deg($az));

	return $ret;
}

/* Example usage: Distance and heading from JO60LK to JO61UA: */
//$bd = bearing_dist("JN81FG", "JO61UA");
//echo "$bd[km]km, $bd[deg]deg";

?>
