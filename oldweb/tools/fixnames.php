<?php

/*
 * Part of : amasebase
 * Filename: fixnames.php
 * Author: 	 Daniel Henry
 * 
 * Created on 02.08.2007
 * 
 */
 
 	define('_VALID_INCLUDE', TRUE);
 	// require('config.inc.php');
	require('modul.inc.php');
	
	
	session_name('amasebase');
	session_start();
	// check_login();
 
 	html_head();
 	
 	if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
    
    echo "Lade Studentenids...";
    $result_id = mysql_query("SELECT id FROM students;") OR die("Error: could select ids from student-table. " . mysql_error());
	echo "<b> erfolgreich abgeschlossen (" .mysql_num_rows($result_id) . " IDs)</b><br><br>\n";
	
	
	echo "Update Notenliste...<br>\n";
	 
	while ( $id = mysql_fetch_row($result_id) ) {
		echo "Datensatz: " . $id[0] . "<br>\n";
		
		$query_fullname = "SELECT CONCAT(nachname,', ',vorname) AS fullname from students WHERE id=". $id[0];
		$result_fullname = mysql_query($query_fullname) OR die("Error: could not get fullname by id " .  $id[0] . " from student-table. " . mysql_error());
		$fullname = mysql_fetch_row($result_fullname);
		
		$query = "UPDATE grades SET name='". $fullname[0]. "' WHERE student_id='" . $id[0] . "';";
		$result = mysql_query($query) OR die("Error: could update dataset " .  $id[0] . " from student-table. " . mysql_error());

		if (!$result) {
    		die('mySQL Server responded "false": ' . mysql_error());
		}
	}
	echo " <br><b>...erfolgreich abgeschlossen</b><br>\n";
 	
 	
 	include "footer.html";
?>
