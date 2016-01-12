<?php 
define('_VALID_INCLUDE', TRUE);
	require "edit.common.php";
	require_once "modul.inc.php";
	require_once "config.inc.php";
	require_once "./register/validation.inc.php";
        
	session_name('amasebase');
	session_start();
	check_login();
        $cat2 = $_POST['cat2'];
        $cat1 = $_POST['cat1'];

        $query = "SELECT id, modules_tracks, ects FROM amase_courses WHERE local_description = '$cat2' LIMIT 1";
        mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
        $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
        $invCourses = array();
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo "<input type=\"text\" name=\"modul_info\" id=\"modul_info\" value=\"Module: $row[modules_tracks]\" size=\"80\" disabled >";
            echo "<br/>";
            echo "<input type=\"text\" name=\"ects_grade_info\" id=\"ects_grade_info\" value=\"Credits: $row[ects]\" size=\"80\" disabled >";
        }		 
?>    	
