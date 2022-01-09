<?php
/******************************************************************************************
This file is a part of WSPRd SIMPLE WEB STATICSTICS GENERATOR FROM WSPRd LOG FILE
This script may have a lot of bugs, problems and it's written in very non-efficient way without a lot of good programming rules. But it works for me.
Author: Alfredo IZ7BOJ iz7boj[--at--]gmail.com
You can modify this program, but please give a credit to original author. Program is free for non-commercial use only.
(C) Alfredo IZ7BOJ 2021
*******************************************************************************************/

include 'config.php';
include 'functions.php';

logexists(); //verify presence of log file

session_start(); //start session
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Description" content="rtl-sdr wsprd statistics" />
	<meta name="Keywords" content="" />
	<meta name="Author" content="IZ7BOJ" />

	<!-- next style is to show arrows in sortable table's column headers to indicate that the table is sortable -->
	<style type="text/css">
		table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after {
			content: " \25B4\25BE"
		}
	</style>

	<title>WSPR statistics - summary</title>
</head>

<body>
	<!-- LOGO PART -->
	<?php if(file_exists($logourl)){ ?>
	<center><img src="<?php echo $logourl ?>" width="200" height="100" align="middle"></center><br>
	<?php } ?>

	<center><font size="20"><b>WSPR statistics</b></font></center>
	<br>

	<?php
	if(!isset($_GET['time']) or ($_GET['time'] == "")) { //if time range not specified
		$time = time() - 3600; //so take frames from last 1 hour
	} elseif($_GET['time'] == "e") { //if whole log
		$time = 0;
	} else { //else if the time range is choosen
		$time = time() - ($_GET['time'] * 3600); //convert hours to seconds
	}
	
	// READ LOG FILE, BUILD RECEIVED STATIONS ARRAY AND COUNT RECEIVED STATIONS
	$receivedstations = array();
	$lines = 0;
	$rx = 0;
	$logfile = file($logpath); //read log file
	$linesinlog = count($logfile);
	
	while ($lines < $linesinlog) { //read line by line
		$line = $logfile[$lines];
		if(strpos($line, "Spot :  ") !== false){ //if there is a spot
			stationspivot($line);
			$rx++;
			} 
		$lines++;
	}
	
	//include custom info
	if(file_exists('custom.php')) include 'custom.php';

	//mean spots/min
	echo "<br><b>Number of lines in log: </b>".$rx;
	rxload();
	echo "<br><b>RX Load (last 20 spots): </b>".number_format($rxframespermin, 2, '.', ',')." spots/min";
	
	// Init sys parameters
	$sysver       = NULL;
	$kernelver    = NULL;
	$uptime       = NULL;
	$cputemp      = NULL;
	$cpufreq      = NULL;
	
	// Sys parameters acquisition
	$sysver    = shell_exec ("cat /etc/os-release | grep PRETTY_NAME |cut -d '=' -f 2");
	$kernelver = shell_exec ("uname -r");
	
	$uptime = shell_exec('uptime -p');
	
	if (file_exists ("/sys/class/thermal/thermal_zone0/temp")) {
		exec("cat /sys/class/thermal/thermal_zone0/temp", $cputemp);
		$cputemp = $cputemp[0] / 1000;
	} else {
		$cputemp = "NA";
	}
	
	if (file_exists ("/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq")) {
		exec("cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq", $cpufreq);
		$cpufreq = $cpufreq[0] / 1000;
	}
	
	// Init config parameters
	$configvers   = NULL;
	$configcall   = NULL;
	$configloc    = NULL;
	$configfreq   = NULL;
	$configppm    = NULL;
	$configgain   = NULL;
	$configuptime = NULL;
	$configdev    = NULL;

	// Config parameters acquisition
	configparse($logpath);
	
	?>

	<br><br><hr><br>

	<!-- SYSTEM PARAMETERS TABLE -->
	<table style="text-align: left; height: 116px; width: 600px;" border="1" cellpadding="2" cellspacing="2">
		<tbody>
			<tr align="center">
			  <td bgcolor="#ffd700" style="width: 600px;" colspan="2" rowspan="1"><span style="color: red; font-weight: bold;">SYSTEM STATUS</span></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>System Version: </b></td>
			  <td style="width: 400px;"><?php echo $sysver ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>Kernel Version: </b></td>
			  <td style="width: 400px;"><?php echo $kernelver ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>WSPRd Version: </b></td>
			  <td style="width: 400px;"><?php echo $configvers ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>System uptime: </b></td>
			  <td style="width: 400px;"><?php echo $uptime ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>WSPRd uptime: </b></td>
			  <td style="width: 400px;"><?php echo $configuptime ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>CPU temperature:</b></td>
			  <td style="width: 400px;"><?php echo $cputemp ?> °C </td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>CPU frequency: </b></td>
			  <td style="width: 400px;"><?php echo $cpufreq ?> MHz </td>
			</tr>
		</tbody>
	</table>
	<br>
	<br>

	<!-- WSPRD PARAMETERS TABLE -->

	<table style="text-align: left; height: 116px; width: 600px;" border="1" cellpadding="2" cellspacing="2">
		<tbody>
			<tr align="center">
			  <td bgcolor="#ffd700" style="width: 600px;" colspan="2" rowspan="1"><span style="color: red; font-weight: bold;">WSPRd CONFIG PARAMETERS</span></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>CALL : </b></td>
			  <td style="width: 400px;"><?php echo $configcall ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>DEVICE TYPE : </b></td>
			  <td style="width: 400px;"><?php echo $configdev ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>Locator: </b></td>
			  <td style="width: 400px;"><?php echo $configloc ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>Dial Frequency: </b></td>
			  <td style="width: 400px;"><?php echo $configfreq ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>PPM correction:</b></td>
			  <td style="width: 400px;"><?php echo $configppm ?></td>
			</tr>
			<tr>
			  <td bgcolor="silver" style="width: 200px;"><b>Gain:</b></td>
			  <td style="width: 400px;"><?php echo $configgain ?></td>
			</tr>
		</tbody>
	</table>

	<br><br><hr>
	<!-- HISTORY LENGTH SELECTION -->
		<br><br>
		<form action="index.php" method="GET">
			Show stations since last:
			<select name="time">
				<option value="1"  <?php if(isset($_GET['time'])&&($_GET['time'] == 1))   echo 'selected="selected"'?>>1 hour</option>
				<option value="2"  <?php if(isset($_GET['time'])&&($_GET['time'] == 2))   echo 'selected="selected"'?>>2 hours</option>
				<option value="4"  <?php if(isset($_GET['time'])&&($_GET['time'] == 4))   echo 'selected="selected"'?>>4 hours</option>
				<option value="6"  <?php if(isset($_GET['time'])&&($_GET['time'] == 6))   echo 'selected="selected"'?>>6 hours</option>
				<option value="12" <?php if(isset($_GET['time'])&&($_GET['time'] == 12))  echo 'selected="selected"'?>>12 hours</option>
				<option value="24" <?php if(isset($_GET['time'])&&($_GET['time'] == 24))  echo 'selected="selected"'?>>1 day</option>
				<option value="48" <?php if(isset($_GET['time'])&&($_GET['time'] == 48))  echo 'selected="selected"'?>>2 days</option>
				<option value="168"<?php if(isset($_GET['time'])&&($_GET['time'] == 168)) echo 'selected="selected"'?>>week</option>
				<option value="720"<?php if(isset($_GET['time'])&&($_GET['time'] == 720)) echo 'selected="selected"'?>>30 days</option>
				<option value="e"  <?php if(isset($_GET['time'])&&($_GET['time'] == 'e')) echo 'selected="selected"'?>>all</option>
			</select>
		</form>
	<input type="submit" value="Refresh">
	<?php
	uasort($receivedstations, 'cmp'); //sort array by heard time
	$linesinlog = count(file($logpath))-19; // subtract info lines
	echo "<br><br><b>".count($receivedstations)." Stations received (sorted by Last Time Heard)</b><br><br>";
	?>
		<script src="sorttable.js"></script>

	<!-- HEARD STATIONS TABLE -->
		<table style="text-align: left; height: 116px; width: 1000px;" border="1" class="sortable" id="table">
			<tbody>
				<tr align="center">
					<th bgcolor="#ffd700"><b><font color="blue">Call</font></b></th>
					<th bgcolor="#ffd700"><b><font color="blue">Band</font></b></th>
					<th bgcolor="#ffd700"><b><font color="blue">Date</font></b></th>
					<th bgcolor="#ffd700"><b><font color="blue">Time(z)</font></b></th>
					<th bgcolor="#ffd700"><b><font color="blue">N. of spots</font></b></th>
					<td bgcolor="#ffd700"><b><font color="blue">Details</font></b></td>
					<th bgcolor="#ffd700"><b><font color="blue">Distance [Km]</font></b></th>
					<th bgcolor="#ffd700"><b><font color="blue">Bearing [deg]</font></b></th>
				</tr>
				<?php while(list($call,$details) = each($receivedstations)) {
					$remoteloc = (strlen($details[3])==4 ? $details[3]."LL" : $details[3]); //consider locator subsqaure center because WSPRd manages only 4digit locator
					$bd = bearing_dist($configloc, $remoteloc); 
				?>
				<tr>
					<td align="center"><b><?php echo $call ?></b></td>
					<td align="center"><?php echo $details[4] ?></td>
					<td align="center"><?php echo $details[1] ?></td>
					<td align="center"><?php echo $details[2] ?></td>
					<td align="center"><?php echo $details[0] ?></td>
					<td><?php echo '<a target="_blank" href="frames.php?getcall='.$call.'">Show Spot details</a>' ?></td>
					<td align="center"><?php echo $bd['km'] ?></td>
					<td align="center"><?php echo $bd['deg'] ?></td>
					
				</tr>
				<?php } ?>
			</tbody>
		</table>

	<br><hr><br>
	<center><a href="https://github.com/IZ7BOJ/WSPRd_web_interface" target="_blank">WSPR Simple Webstat version <?php echo $version; ?></a> by Alfredo IZ7BOJ</center>
	<br>
</body>
</html>
