<?php

/**
	 * Part of : amasebase
 	 * Filename: fixnames.php
	 * Created on 15.03.2008
	 * 
 	 * @author 	$Author: Daniel $ 
	 * @version $Revision:  $
	 * @version	$Id:  $
	 **/
 
 	define('_VALID_INCLUDE', TRUE);
 	require('config.inc.php');
	require('modul.inc.php');
	
	
	session_name('amasebase');
	session_start();
	// check_login();
 
 	html_head();
 	
 	if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
    
    echo "Lade Noten..";

    $query = "SELECT * FROM `grades`" ;
	$result = mysql_query($query) OR die("Error: Konnte Query nicht absetzen: " . mysql_error());    

	echo "<b> erfolgreich abgeschlossen (" .mysql_num_rows($result) . " IDs)</b><br><br>\n";
	echo "Update Notenliste...<br>\n";
	echo '<table width="96%" border="1" align="center" cellpadding="2" cellspacing="0">';
	
	while ( $daten = mysql_fetch_assoc($result) ) {
		if ( ($daten['university'] == 'UdS') && ($daten['local_grade'] == 'bestanden') ) {
			$status = 'passed';
		} elseif ( ($daten['ects_grade'] == 'pendung ') ){
			$status = 'open';
		} elseif ( ($daten['ects_grade'] == 'F') || ($daten['ects_grade'] == 'FX') ){
			$status = 'failed';
		} elseif ( ($daten['university'] == 'UdS') && ($daten['local_grade'] == '5.0' || $daten['local_grade'] == '5' || $daten['local_grade'] == '5,0') ) {
			$status = 'failed';			
		} else {
			$status = 'grade';	
		}
		$zeile = "\n	<tr>\n";
		
		foreach ($daten as $key=>$value)  {
			$zeile .= '		<td>' . $value .  "</td>\n";
		}
				
		$zeile .= '		<td><b>' . $status . "</b></td>\n";
		$zeile .= "	</tr>\n";		

		// $query = "UPDATE `grades` SET status='". $status . "' WHERE id='" . $id['id'] . "';";
		// $result = mysql_query($query) OR die("Error: could update dataset " .  $id[0] . " from student-table. " . mysql_error());
		
		echo $zeile;
		
/*		
		
		$query_fullname = "SELECT CONCAT(nachname,', ',vorname) AS fullname from students WHERE id=". $id[0];
		$result_fullname = mysql_query($query_fullname) OR die("Error: could not get fullname by id " .  $id[0] . " from student-table. " . mysql_error());
		$fullname = mysql_fetch_row($result_fullname);
		
		$query = "UPDATE grades SET name='". $fullname[0]. "' WHERE student_id='" . $id[0] . "';";
		$result = mysql_query($query) OR die("Error: could update dataset " .  $id[0] . " from student-table. " . mysql_error());
*/
		if (!$result) {
    		die('mySQL Server konnte Datensatz nicht finden: ' . mysql_error());
		}
	}
	echo "</table>\n";
	echo " <br><b>...erfolgreich abgeschlossen</b><br>\n";
 	
 	
 	include "footer.html";
?>