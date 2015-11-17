<?php

/*
 * Part of : amasebase
 * Filename: printgrades.php
 * Author: 	 Daniel Henry
 * 
 * Created on 22.07.2007
 * 
 */
 	define('_VALID_INCLUDE', TRUE);
 	require('config.inc.php');
	require('modul.inc.php');
	
	
	session_name('amasebase');
	session_start();
	check_login();
 
 	html_head();
	 	
 	$id = $_GET['id'];
 
	if ( !isset($id) || !is_clean_digit($id) ) {
		box(false, "no student-ID given", 1, 0);
		echo "</table>\n";
		echo "</body></html>";
		die();
	}
###### QUERY
	if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());

	$query_stud	 = "SELECT nachname, vorname, gender, birth_date, matrikel1, matrikel2, university1, university2 FROM amase_students WHERE id='$id';";
    $result_stud = mysql_query($query_stud) OR die("Error: could not load student dataset: " . mysql_error());
	$student_from_db = mysql_fetch_assoc($result_stud);
###### QUERY

	// var_dump($student_from_db);
	// var_dump($result_stud);
 	
 	?>
	<form>
		<input type=button value="    PRINT    " onClick="javascript:window.print()">
	</form><br><br>
	
	<table width="90%" border="0" align="center" cellspacing="0" cellpadding="0">
		<tr>
			<td><div align="center"><img src="./icons/uds_head.jpg" alt="UdS Logo"></div></td>
			<td><div align="center"><img src="./icons/amase_head.gif" alt="Amase Logo"></div></td>
		</tr>
	
 	<?php
 	echo "		<tr>";
 	echo "			<td colspan=\"2\">";
	echo "<h1><b>Studiennachweis</b></h1><br>\n";
	echo "<div align=\"right\">Saarbr&uuml;cken, den ".  date("d.m.Y") . "</div><br><br>\n";
	echo (($student_from_db['gender']=='male') ? "Herr " : "Frau ") . $student_from_db['vorname']. " " . $student_from_db['nachname'] ."<br>\n";
	echo "geb. am: "  .  germandate_from_sqldate($student_from_db['birth_date']) . "<br><br>\n";
	echo "Matrikelnummer 1: <b>" .$student_from_db['matrikel1']. "</b> (" . $student_from_db['university1']. ")<br>\n";
	echo "Matrikelnummer 2: <b>" .$student_from_db['matrikel2']. "</b> (". $student_from_db['university2']   .")<br><br>\n";
	echo "			</td>";
	echo "		</tr>";
	echo "</table>\n";
	
###### QUERY
    $query_grades = "SELECT modul,coursename,credits,local_grade,ects_grade FROM amase_grades WHERE university='UdS' AND student_id='$id' AND ects_grade NOT IN ('pending', 'F', 'FX') AND local_grade NOT IN ('0.0', '0,0', '5.0', '5,0') ORDER BY modul";
	$result_grades = mysql_query($query_grades) OR die("Error: could not collect grades from database: " . mysql_error());
###### QUERY
	echo "<table width=\"90%\" border=\"0\" align=\"center\" cellspacing=\"0\" cellpadding=\"0\">";
	
	$lastmod= 		"";
	$startindex=	-1;
	$ects_sum = 	0.0;
	
	while($row = mysql_fetch_assoc($result_grades)) {
		
// 		if ( ($row['local_grade'] != "0.0") && ($row['local_grade'] != "5.0") && ($row['ects_grade'] != "pending")) {
			$ects_sum = $ects_sum + $row['credits'];
//		}
/*		
		if ($row['local_grade'] == "5.0" ) || ($row['local_grade'] == "5,0" ){
			$row['local_grade'] = "5.0 (failed)";
			$row['credits'] = "0.0";
		} 
		
		if (($row['local_grade'] == "0.0") ||  $row['local_grade'] == "0,0") ){
			$row['local_grade'] = "pending";
			$row['credits'] = "0.0";
		}
*/
		// Wenn Modulname = der der aktuellen �berschrift ist, dann f�ge den Datensazt darunter
		// andernfalls setze eine neue �berschrift und setze den 1. Datensatz darunter

		if ($row['modul'] != $lastmod) {
			$startindex++;
			// <img src=\"./icons/1pix_black.png\" alt=\"Fillpixel\" width=\"900\" height=\"2\" />
			echo "<tr>\n";
			echo "  <td style=\"border-bottom:2px solid #000000; border-spacing:0px; font-size:18px;\" width=\"60%\"><b><br><br>" . $row['modul'] . "</b></td>\n";
			echo "  <td style=\"border-bottom:2px solid #000000; border-spacing:0px;\" width=\"20%\"><b><br><br><br>ECTS Credits</b>\n";
			echo "  <td style=\"border-bottom:2px solid #000000; border-spacing:0px;\" width=\"20%\"><b><br><br><br>Local Grade</b>\n";
			echo "</tr>\n";
			
			echo "<tr><td width=\"60%\">". $row['coursename'] . "</td><td width=\"20%\">" . $row['credits'] . " ECTS </td><td width=\"20%\">" . $row['local_grade'] . "</td></tr>\n" ;
		} else {
			echo "<tr><td width=\"60%\">". $row['coursename'] . "</td><td width=\"20%\">" . $row['credits'] . " ECTS </td><td width=\"20%\">" . $row['local_grade'] . "</td></tr>\n" ;
		}
		
		
		$lastmod=$row['modul'];
	}
	echo "<tr><td><br><br><br><b>Total credit points: </b></td><td><br><br><br><b>$ects_sum ECTS</b></td><td>&nbsp;</td>";
	echo "</table><br><br><br>\n";
	
?>	
	<form>
		<input type=button value="    PRINT    " onClick="javascript:window.print()">
	</form>
<!--
	<script type="text/javascript">
		window.print()
	</script>
-->

<?php 	echo "</BODY></HTML>"; ?>
