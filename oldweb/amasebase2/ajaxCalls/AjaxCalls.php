<?php

/**
 * @abstract Goal of this file is to contain on the future all the ajax-calls that are needed from the application.
 * Currently contains the following calls:
 *      - Refresh/ Show the student-status on the Student-Table
 *
 * @version 1.0
 * @author Christos Monogios 
 */

define('_VALID_INCLUDE', TRUE);
require_once "../modul.inc.php";
require_once "../config.inc.php";

// Return a JSON object with PHP4:
require_once('../JSON.php');

define('DATABASE', $datenbank);

session_name('amasebase');
session_start();
check_login();

$identify = new IdentifyAjaxCall();

class IdentifyAjaxCall
{
    function IdentifyAjaxCall()
    {
        if (isset($_POST['studentId']) && isset($_POST['studentStatus']))
        {
            $id = $_POST['studentId'];
            $studentStatus = $_POST['studentStatus'];
            $studenStatus = new StudentStatus($id, $studentStatus);
        }
    }
}

class StudentStatus
{
    function StudentStatus($id, $studentStatus)
    {
        $success = $this->update($id, $studentStatus);
        $this->answer($success);
    }
    
    function update($id, $studentStatus)
    {
        $query = "UPDATE amase_students SET complete='$studentStatus' WHERE id=$id;";
        mysql_select_db(DATABASE) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
        $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
        if (result)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    function answer($success)
    {
		$json = new Services_JSON();
        $json_return = array();
        $json_return['success'] = $success;
		echo $json->encode($json_return);
    }
}
 
?>
