<?php 
    define('_VALID_INCLUDE', TRUE);
	require "edit.common.php";
	require_once "modul.inc.php";
	require_once "config.inc.php";
		
	session_name('amasebase');
	session_start();
	check_login();
    $cat1 = $_POST['cat1']; 

    function fillDropDownLine($row) {
        $invCourses = array();
        echo "<option value=\"$row[local_description]\">" . $invCourses[$row[id]] = $row[local_description] . " -- ";
                                                        
        if (strpos($row[modules_tracks], ':') !== false)  {
            echo substr($row[modules_tracks], 0, strpos($row[modules_tracks],":"));
        } else {
            echo $row[modules_tracks];
        }
        echo "</option>";
    }

?>
<select id="cat2" name="cat2" onchange="checkNew('cat2', '<?php echo $cat1 ?>');" size="1">
<option value="0"></option>
<?php 
    if( $cat1 == '1' ) { 
        $query = "SELECT id, local_description, modules_tracks FROM amase_courses WHERE university = 'LTU'";
        mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
        $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            fillDropDownLine($row);
        }
    } elseif( $cat1 == '2' ) { 
        $query = "SELECT id, local_description, modules_tracks FROM amase_courses WHERE university = 'UdS' ORDER BY local_description";
        mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
        $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            fillDropDownLine($row);
        }    
    } elseif( $cat1 == '3' ) { 
        $query = "SELECT id, local_description, modules_tracks FROM amase_courses WHERE university = 'UPC'";
        mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
        $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            fillDropDownLine($row);
        }    
    } elseif( $cat1 == '4' ) { 
        $query = "SELECT id, local_description, modules_tracks FROM amase_courses WHERE university = 'UL'";
        mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
        $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            fillDropDownLine($row);
        }
    } 
?>
</select>
<?php echo "<br/><input type='text' name='language_course' id='language_course' value='' size='100' placeholder='Write here only if you want to edit the name of the course'>"; ?>
