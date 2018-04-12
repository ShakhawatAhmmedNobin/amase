<?php

defined('_VALID_INCLUDE') or die('Direct access not allowed (modul).');

//ugly fix to get fonts to work again as directory changed to usr share
$fontprefixfix = "/www/htdocs/w006e175/amase/amasebase2/pdf/font/";
define('FPDF_FONTPATH', $fontprefixfix);

/**
 * @return mixed
 */
function guard_login()
{
    session_name('amasebase');
    session_start();
    check_login();

    if (!isset($_GET['id']) || !is_clean_digit($_GET['id'])) {
        box(false, "no valid student-ID given", 1, 0);
        echo "</table>\n";
        echo "</body></html>";
        die();
    } else $id = $_GET['id'];
    return $id;
}

/**
 * @param $db_link
 * @param $datenbank
 * @param $studentID
 * @return array
 */
function getStudentData($db_link, $datenbank, $studentID)
{
    if (!($db_link)) {
        die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: " . mysql_error());
    }
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
    $query = "SELECT nachname, vorname, gender, birth_date, geburtsort, geburtsland, university1, university2, matrikel1, matrikel2, start_semester FROM amase_students WHERE id='$studentID';";
    $result = mysql_query($query) OR die("Error: could not load student dataset: " . mysql_error());
    return mysql_fetch_assoc($result);
}


/**
 * @param $student
 * @return mixed
 */
function updateStudentData($student)
{
    $student['gender'] = translateGenderToText($student['gender']);
    $student['birth_date'] = translateDateToText($student['birth_date']);
    $student['matrikel'] = getUDSMatrikelNumber($student);
    return $student;
}

/**
 * @param $gender
 * @return mixed
 */
function translateGenderToText($gender)
{
    $genderMapping = array('male' => 'Herr', 'female' => 'Frau');
    if (array_key_exists($gender, $genderMapping)) {
        return $genderMapping[$gender];
    } else die("unknown gender: " . $gender);
}

/**
 * @param $date
 * @return bool|string
 */
function translateDateToText($date)
{
    list($year, $month, $day) = split('-', $date); //TODO: change split to explode before PHP7!
    return date("d.m.Y", mktime(0, 0, 0, $month, $day, $year));
}

/**
 * @param $student
 * @return mixed
 */
function getUDSMatrikelNumber($student)
{
    if ($student['university1'] == "UdS") {
        return $student['matrikel1'];
    } elseif ($student['university2'] == "UdS") {
        return $student['matrikel2'];
    } else {
        die ("Student ist kein Mitglied der UdS");
    }
}

/**
 * @param $studentID
 * @return bool
 */
function queryGrades($studentID){
    $query = "SELECT * FROM amase_grades WHERE student_id='$studentID' AND university IN ('UdS') AND status='grade' AND ects_grade IN ('A', 'B', 'C', 'D', 'E');";
    return mysql_query($query) OR die("Error: could not load students grades: " . mysql_error());
}

/**
 * @param $studentID
 * @return resource
 */
function queryMaster($studentID){
    $query3 = "SELECT * FROM amase_master WHERE student_id='$studentID' AND university='UdS' AND status='grade';";
    $result3 = mysql_query($query3) OR die("Error: could not load students grades: " . mysql_error());
    return $result3;
}

?>