<?php
    define('_VALID_INCLUDE', TRUE);
    require "config.inc.php";
    require "modul.inc.php";
    require "register/validation.inc.php";

    session_name('amasebase');
    session_start();
    check_login();
    
    global $parse_modul;    // from config.inc.php. Contains all the modules, tracks

    if ( isset($_GET['id']) && (is_clean_digit($_GET['id'])) && isset($_SESSION['html_template']) )  {
        $id = $_GET['id'];
        $html_template = $_SESSION['html_template'];
    } else {
        box(false, "Datenmaske fehlerhaft / datatype missmatch", false);
        exit;
    }
    
    html_head(false);   // HTML HEADER SCHREIBEN
    function pretty_printer($datensatz) {   // Personal information about the student
        echo "Student's name: " . $datensatz["vorname"] ." " . $datensatz["nachname"] . "<br>\n";
        echo "1st university: " . $datensatz["university1"] . " - student id: " . $datensatz["matrikel1"] . "<br>\n";
        echo "2nd university: " . $datensatz["university2"] . " - student id: " . $datensatz["matrikel2"] . "<br>\n";
        echo "Date/ place of birth: " . $datensatz["birth_date"]  ."/ " . $datensatz["birth_place"] . "<br>\n";
        echo "Country of birth: " . $datensatz["country_birth"] . "<br>\n";
        echo "Email: " . "<a href=\"mailto:".  $datensatz["email"] . "\">" . $datensatz["email"] . "</a>";
    }
    
    function fill_default ($cell, $index) { // Fuellt Zellen mit Verlinktem Eintrag oder leere Zellen mit &nbsp; auf
        if ($cell == "") return "<td>&nbsp;</td>\n";
        else return "<td>$cell</td>\n";
    }    

######## Datenbankverbindung aufbauen
    if (!($db_link)) die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); // zum mySQL-Server verbinden
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error()); // Datenbank auswaehlen

    // Tabelle auf existenz pruefen
    $result = mysql_query("SELECT * FROM $tabellen[$html_template] WHERE 1=0") OR die("Tabelle nicht vorhanden - Error #24 - SQL-Message: " . mysql_error());
    $query = "SELECT * FROM amase_students WHERE id='" . $id . "'";
    $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
    $datensatz = mysql_fetch_assoc($result);
    echo "<body class=\"bgForOutput\">";
    echo "<div id=\"bgForOutput\">";
    echo "<b><u>STUDENT INFORMATION</u></b><br/><br/>";

    pretty_printer($datensatz);    
    
    echo "<br/><br/><br/>";
    echo "<b><u>CREDIT POINTS</u></b><br/><br/>";
    
####### Total credit points for the  courses
    $query = "SELECT status, modul, coursename, credits, MIN(local_grade) as local_grade, ects_grade FROM amase_grades WHERE student_id='$id' 
                AND status IN ('grade', 'passed') AND modul NOT IN ('Language') GROUP BY coursename";
    $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
    $creditsSumme = 0;
    while ($row = mysql_fetch_assoc($result)) { // Workaround for selecting only one time each course
        $creditsSumme += $row["credits"];
    }
    $datensatz = mysql_fetch_assoc($result);
    $overallECTSSumme = $creditsSumme;
    if ($overallECTSSumme == "") $overallECTSSumme = "0";
    echo "ECTS credits from technical courses: <b>" . $overallECTSSumme. " / 90</b/><br>\n";
    
####### Credit points per track/module   
    foreach ($parse_modul as $value) {
        if ($value != "Language") {
            $query = "SELECT credits, MIN(local_grade) as local_grade FROM amase_grades WHERE student_id='$id' AND status IN ('grade', 'passed') AND modul = '$value' GROUP BY coursename";
            $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
            while ($row = mysql_fetch_assoc($result)) { // Workaround for selecting only one time each course
                $creditPoints[$value] += $row["credits"];
            }
        }
    }
    if ($creditPoints != null) {
        foreach ($creditPoints as $key => $value) {
            if ($value) echo "ECTS credits from $key: <b>" . intval($value) . "</b><br/>";
            else ;
        }    
    }
    
    echo "<br/>";
    $query2 = "SELECT status FROM amase_master WHERE student_id =$id";
    $result2 = mysql_query($query2) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
    $datensatz2 = mysql_fetch_assoc($result2);
    if ($datensatz2["status"] == "passed" || $datensatz2["status"] == "grade") {
        echo "ECTS credits from Master's Thesis: <b>30 / 30</b><br>\n";
        $master_grade = 30;
    } else {
        echo "ECTS credits from Master's Thesis: <b>0 / 30</b><br>\n";
        $master_grade = 0;
    }
    
###### LANGUAGE POINTS 
    $query = "SELECT credits, MIN(local_grade) as local_grade FROM amase_grades WHERE student_id='$id' AND status IN ('grade', 'passed') AND modul = 'Language' GROUP BY coursename";
    $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $creditPointsLanguage += $row["credits"];
    }
    if ((isset($creditPointsLanguage)) && ($creditPointsLanguage <= 8)) echo "<br/>ECTS credits from Language: <b>" . intval($creditPointsLanguage) . "</b><br/>";
    else echo "<br/>ECTS credits from Language: <b>8</b><br/>";
###### LANGUAGE POINTS  
    
    $totalEcts = (int)($overallECTSSumme + $master_grade + $creditPointsLanguage);
    echo "Total ECTS credits: <b>" . $totalEcts . ".0/ 120</b><br>\n";
    
    echo "<br/><br/>";
    echo "<b><u>COURSES</u> (Only the passed/ graded courses)</b><br/><br/>";

    $query = "SELECT university, status, coursename, ex_date, modul, ects_grade, credits, MIN(local_grade) as local_grade, try FROM amase_grades WHERE student_id='$id' AND status IN ('grade', 'passed') GROUP BY coursename";
    $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
?>
    <table border="1" align="center" cellpadding="5" cellspacing="0" class="tabellen_layout">
    <tr class='tableHeader'><th>University</th><th>Course Name</th><th>Exam Date</th><th>Module</th><th>Local Grade</th><th>ECTS Grade</th><th>Credits</th></th><th>Attempt</th></tr>
<?php
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    echo"<tr>\n";
                    echo fill_default($row["university"], "university");
                    echo fill_default($row["coursename"], "coursename");
                    echo fill_default($row["ex_date"], "ex_date");
                    echo fill_default($row["modul"], "modul");
                    echo fill_default($row["local_grade"], "local_grade");
                    echo fill_default($row["ects_grade"], "ects_grade");
                    echo fill_default($row["credits"], "credits");
                    echo fill_default($row["try"], "try");
                    echo "</tr>\n";
                } 
echo "</table><br/><br/>";
echo "<b><u>MASTER THESIS</b></u><br/><br/>";
     $query = "SELECT projectname, ex_date, university, examiner1, examiner2, examiner_grade1, examiner_grade2, local_grade_master, ects_grade, try FROM amase_master WHERE student_id='$id' AND status IN ('grade', 'passed')";
     $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
     ?><table border="1" align="center" cellpadding="5" cellspacing="0" class="tabellen_layout">
        <tr class='tableHeader'><th>Title of thesis</th><th>Exam Date</th><th>University</th><th>Examiner 1</th><th>Examiner 2</th><th>Examiner grade 1</th><th>Examiner grade 2</th></th><th>Local grade</th><th>ECTS grade</th><th>Attempt</th></tr>
    <?php
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    echo"<tr>\n";
                    echo fill_default($row["projectname"], "projectname");
                    echo fill_default($row["ex_date"], "ex_date");
                    echo fill_default($row["university"], "university");
                    echo fill_default($row["examiner1"], "examiner1");
                    echo fill_default($row["examiner2"], "examiner2");
                    echo fill_default($row["examiner_grade1"], "examiner_grade1");
                    echo fill_default($row["examiner_grade2"], "examiner_grade2");
                    echo fill_default($row["local_grade_master"], "local_grade_master");
                    echo fill_default($row["ects_grade"], "ects_grade");
                    echo fill_default($row["try"], "try");
                    echo "</tr>\n";
                } 
echo "</table>";

echo "</div></div>";