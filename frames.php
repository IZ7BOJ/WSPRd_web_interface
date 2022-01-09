
<?php
/******************************************************************************************
This file is a part of WSPRd SIMPLE WEB STATICSTICS GENERATOR FROM WSPRd LOG FILE
This script may have a lot of bugs, problems and it's written in very non-efficient way without a lot of good programming rules. But it works for me.
Author: Alfredo IZ7BOJ iz7boj[--at--]gmail.com
You can modify this program, but please give a credit to original author. Program is free for non-commercial use only.
(C) Alfredo IZ7BOJ 2021
*******************************************************************************************/
?>
<?php
include 'config.php';
include 'functions.php';

logexists();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="WSPRd statistics" />
<meta name="Keywords" content="wspr" />
<meta name="Author" content="IZ7BOJ" />
<title>WSPRd statistics - spots search</title>
</head>
<body>
	<?php
	if(file_exists($logourl)){
	?>
	<center><img src="<?php echo $logourl ?>" width="200" height="100" align="middle"></center><br>
	<?php
	}
	?>

	<br>
	<form action="frames.php" method="get">
		Show all spots from the callsign: <input type="text" name="getcall" <?php if(isset($_GET['getcall'])) echo "value=\"".$_GET['getcall']."\""; ?>>
		<input type="submit" value="Show">
	</form>
	<br>
	<!-- SPOTS TABLE -->
	<table style="text-align: left; height: 116px; width: 800px;" border="1" class="sortable" id="table">
	<tbody>
		<tr align="center">
		<th bgcolor="#ffd700"><b><font color="blue">Call</font></b></th>
		<th bgcolor="#ffd700"><b><font color="blue">Date</font></b></th>
		<th bgcolor="#ffd700"><b><font color="blue">Time(z)</font></b></th>
		<th bgcolor="#ffd700"><b><font color="blue">SNR</font></b></th>
		<td bgcolor="#ffd700"><b><font color="blue">DT</font></b></td>
		<td bgcolor="#ffd700"><b><font color="blue">Frequency</font></b></td>
		<td bgcolor="#ffd700"><b><font color="blue">Drift</font></b></td>
		<td bgcolor="#ffd700"><b><font color="blue">Locator</font></b></td>
		<td bgcolor="#ffd700"><b><font color="blue">Power</font></b></td>
		</tr>
	<?php
	if(isset($_GET['getcall']) and ($_GET['getcall'] !== ""))
		{
		$scall = strtoupper($_GET['getcall']);
		$linesinlog = 0;
		$logfile = file($logpath); //read log file
		$linesinlog = count($logfile);
		$counter= 0;
		//parse line by line
		while($counter < $linesinlog) {
			$line = $logfile[$counter];
			$frame1 = substr($line, 8, -1); //cut first unuseful part
                        $parts = preg_split('/\s+/', $frame1); //split all fields
                        if (count($parts)==9) { // if all fields are present
                                if ($parts[6]==$_GET['getcall']) { //if spot is received from call

	?>
	<tr>
		<td align="center"><?php echo $parts[6] ?></td>
		<td align="center"><?php echo $parts[0] ?></td>
		<td align="center"><?php echo $parts[1] ?></td>
		<td align="center"><?php echo $parts[2] ?></td>
		<td align="center"><?php echo $parts[3] ?></td>
		<td align="center"><?php echo $parts[4] ?></td>
		<td align="center"><?php echo $parts[5] ?></td>
		<td align="center"><?php echo $parts[7] ?></td>
		<td align="center"><?php echo $parts[8] ?></td>
	</tr>
	<?php
				 } // close if received from call
                       } // close if count($parts)==9
		$counter++;
		}//close while	
	?>
	</tbody>
	</table>
	<?php
	} //close if issset GET['call'] 
	?>
	<br><br><br><br><br><br><br><br><br><br><br><br><br><br>
	<center><a href="https://github.com/IZ7BOJ/WSPRd_web_interface" target="_blank">WSPRd Simple Webstat version <?php echo $version; ?></a> by Alfredo IZ7BOJ</center>
	</body>
</html>
