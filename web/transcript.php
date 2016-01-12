<?php
// FOR THE TRANSCRIPT OF RECORDS DOCUMENT
 
    define('_VALID_INCLUDE', TRUE);
    require('config.inc.php');
    require('modul.inc.php');
    require('./pdf/fpdf.php');  
    require('grades.inc.php');

    session_name('amasebase');
    session_start();
    check_login();

    if ( !isset($_GET['id']) || !is_clean_digit($_GET['id']) ) {
            box(false, "Please select a valid student ID", 1, 0);
            echo "</table>\n";
            echo "</body></html>";
            die();
    } else $id = $_GET['id'];

    if ($_SESSION["user_status"] != "supervisor") {
        echo "You do not have the right to view this page.";
        die();        
    }    
    
###### QUERY FOR THE GENERAL INFORMATION ABOUT THE USER
    if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
    $query_stud  = "SELECT nachname, vorname, gender, birth_date, birth_place, country_birth, matrikel1, matrikel2, university1, university2, start_semester FROM amase_students WHERE id='$id';";
    $result_stud = mysql_query($query_stud) OR die("Error: could not load student dataset: " . mysql_error());
    $student_from_db = mysql_fetch_assoc($result_stud);
###### QUERY

    
    if ($student_from_db["university1"] != "UdS" && $student_from_db["university2"] != "UdS") {
        echo "The Transcript of Records is available only for students that are studying in the UdS.";
        die();
    }
    

class PDF extends FPDF {
    
    function Header() { //Page header
        global $student_from_db;
        $this->Image('./icons/uds_head.jpg', 45, 5, 40);    //Logo 1    Image($file,$x,$y,$w=0,$h=0,$type='',$link='')
        $this->Image('./icons/amase_head.png', 115, 4, 50); //Logo 2

        // first title    
        $this->SetXY(10, 22);
        $this->SetFont('Arial','B',14);
        $this->Cell(0,7,'ECTS - EUROPEAN CREDIT TRANSFER SYSTEM',0,1, C); // Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
        $this->Cell(0,10,'Transcript of Records',0,1, C);
        $this->Ln(5);
        
        $this->SetFont('Arial','B',11);
        $geschlecht =  (($student_from_db['gender']=='male') ? "Herr " : "Frau ") . utf8_decode($student_from_db['vorname']). " " . utf8_decode($student_from_db['nachname']);
        $this->Cell(0,3,$geschlecht,0,1);
        $this->SetFont('Arial','',11);
        $geburtsDatum = "born on "  .  germandate_from_sqldate($student_from_db['birth_date']) . " in " . utf8_decode($student_from_db['birth_place']) . ", " . utf8_decode($student_from_db['country_birth']);
        $this->Cell(0,5,$geburtsDatum,0,1);

        $this->Ln(4); //Line break

        $matrikel1 = "1st Student-ID: " .$student_from_db['matrikel1']. " (" . $student_from_db['university1']. ")";
        $this->Cell(0,5,$matrikel1,0,1);
        $matrikel2=  "2nd Student-ID: " .$student_from_db['matrikel2']. " (". $student_from_db['university2']   .")";
        $this->Cell(0,5,$matrikel2,0,1);
        
        $this->Ln(5); //Line break
    }

    function Footer() {   //Page footer
        $person = "Examination Board";
        $inst = "Saarland University";
        $fussnote = "German Grades: 1.0, 1.3, 1.7, 2.0, 2.3, 2.7, 3.0, 3.3, 3.7, 4.0, 5.0. Less is better, 5.0 means 'failed'";

        $this->SetY(-35);   //Position at 1.5 cm from bottom

        $this->SetFont('Arial','',10);
        $datum = 'Saarbrücken, ' . date("F j, Y");
        $this->Cell(150,7,$datum,0,0);
        $this->Cell(150,7,$person,0,0);
        $this->Ln();
        $this->Cell(150,7,"",0,0);
        $this->Cell(150,7,$inst,0,0);

        $this->Ln(18);
        $this->SetFont('Arial','I',8);  //Arial italic 8

        $this->Cell(170,9,$fussnote,1,0,'L');   //Footnote

        $this->Cell(20,9,'Page '.$this->PageNo().'/{nb}',1,0,'R'); //Page number
    }
    
    function PrintLine() {
    $this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY());
    }
}

    $pdf=new PDF(); //Instanciation of inherited class
    $pdf->SetLeftMargin(5);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);

    $startindex = -1;
    $ects_sum = 0.0;
    $ects_sum_for_module_grade = 0.0;
    $ects_sum_for_module_passed = 0.0;
    $ects_sum_nonote = 0.0;
    $ects_sum_benotete = 0.0;
    $ects_sum_ohne_sprache = 0.0;
    $summe_note_ects = 0.0;
    $ects_sum_benotete_partner_uni = 0.0;
    $ects_sum_for_module_passed_partner_uni = 0.0;
    $ects_sum_sprache_partner_uni = 0.0;
    $ects_sum_zus_leist = 0.0;
    $ects_sum_master_thesis = 0.0;
    if ($student_from_db["university1"] == "UdS") $ects_sum_array = array("benotete" => "/40.0", "unnoted" => "/20.0", "total" => "/60.0");
    else if ($student_from_db["university2"] == "UdS") $ects_sum_array = array("benotete" => "/20.0", "unnoted" => "/10.0", "total" => "/30.0");
    
    $pdf->SetFillColor(224,235,255);    // PDF-Farben festlagen
    $pdf->SetTextColor(0);

    $ueberschriftCoursenameFont = 11;
    $ueberschriftEctsCreditsFont = 10;
    $coursenameFont = 9;

    // Tabellenkopf setzen
    $pdf->SetFont('Arial','B',$ueberschriftCoursenameFont);
    $pdf->Cell(126,7,'Course',1,0,'C');
    $pdf->Cell(24,7,'ECTS grade',1,0,'C');
    $pdf->Cell(25,7,"ECTS credits",1,0,'C');
    $pdf->Cell(25,7,"Local grade",1,0,'C');
    $pdf->Ln();
    $start_year = substr($student_from_db['start_semester'], 0, 4); // for new exam regulations 2011 (added by manpreet)
    
    if($student_from_db["start_semester"] >= 2011){ //added by manpreet
    foreach ($parse_modul_new as $Mcourse) { // Einzelne Module mit eigenem Query isolieren
        $fill=1; // Hintergrund fuellen

        $query_grades = "SELECT status, modul, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                                AND university='UdS' AND modul='$Mcourse' AND status IN ('grade', 'passed') GROUP BY coursename"; // workaround query for selecting only the best local_grade
        $result_grades = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());
        if  (mysql_num_rows($result_grades) != 0 && $Mcourse != "Zusaetzliche Leistung") {  
            $pdf->SetFont('Arial','B',$ueberschriftCoursenameFont); // Names of the modules/tracks
            $pdf->Cell(200,7,$Mcourse,1,0,'L');
            $pdf->Ln();
                
            while($row = mysql_fetch_assoc($result_grades)) {   // Kurse aus Modul extrahieren
                $row['credits']= ereg_replace(",", ".", $row['credits']);
                $row['local_grade']= ereg_replace(",", ".", $row['local_grade']);

                if ( ($row['modul'] != "Language") && ($row['status'] == "grade") ) { // Noten ohne Sprachenschein for the Average Grade
                    $ects_sum_ohne_sprache += $row['credits'];
                    if ( isValidGrade("UdS", (float)$row['local_grade'])) $summe_note_ects += ( (float)$row['credits']*(float)$row['local_grade'] );
                    else echo "invalid grade: " . $row['local_grade'] . " - university: ". $row['university'] . "<br>";
                }
                
             ###### English - German description
                $query_grades = "SELECT english_description FROM amase_courses WHERE local_description = '$row[coursename]' GROUP BY english_description";
                $result_coursenames = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());
                while($row2 = mysql_fetch_assoc($result_coursenames)) {
                    $row['coursename']  = $row['coursename']. " - " . $row2['english_description'];
                }
             ###### English - German description   

                $pdf->SetFont('Arial','',$coursenameFont);
                
                if(strlen($row['coursename'])>60){
                $yBeforeCell = $pdf->GetY();
                $pdf->MultiCell(126,5,utf8_decode($row['coursename']),1,'L',$fill);
                $yCurrent = $pdf->GetY();
                $rowHeight = $yCurrent - $yBeforeCell;
                $pdf->SetXY($x + 131, $yBeforeCell);
                $pdf->Cell(24,$rowHeight, $row['ects_grade'], 1,0,'R',$fill);
                $pdf->Cell(25,$rowHeight,$row['credits'] ,1,0,'R',$fill);
                if (($row['status'] != "passed") && ($row['modul'] != "Language")) {
                    $ects_sum_for_module_grade += $row['credits'];
                    $pdf->Cell(25,$rowHeight,$row['local_grade'],1,0,'R',$fill);
                }
                else if ($row['modul'] == "Language") {
                    $ects_sum_languages += $row['credits'];   
                    $pdf->Cell(25,$rowHeight,$row['local_grade'],1,0,'R',$fill);
                    if($student_from_db["university1"]== "UdS"){
                    if ($ects_sum_languages > 9) $ects_sum_languages = 9;
                    }
                    elseif($student_from_db["university2"]== "UdS"){
                    if ($ects_sum_languages > 5) $ects_sum_languages = 5;
                    }
                }
                else if ($row['status'] == "passed") {
                    $ects_sum_for_module_passed += $row['credits'];
                    $pdf->Cell(25,$rowHeight,"passed",1,0,'R',$fill);
                } 
                }
                else{
                $pdf->Cell(126,5, wordwrap(utf8_decode($row['coursename']), 60),1,0,'L',$fill);
                $pdf->Cell(24,5, $row['ects_grade'], 1,0,'R',$fill);
                $pdf->Cell(25,5,$row['credits'] ,1,0,'R',$fill);
                
                if (($row['status'] != "passed") && ($row['modul'] != "Language")) {
                    $ects_sum_for_module_grade += $row['credits'];
                    $pdf->Cell(25,5,$row['local_grade'],1,0,'R',$fill);
                }
                else if ($row['modul'] == "Language") {
                    $ects_sum_languages += $row['credits'];   
                    $pdf->Cell(25,5,$row['local_grade'],1,0,'R',$fill);
                    if($student_from_db["university1"]== "UdS"){
                    if ($ects_sum_languages > 9) $ects_sum_languages = 9;
                    }
                    elseif($student_from_db["university2"]== "UdS"){
                    if ($ects_sum_languages > 5) $ects_sum_languages = 5;
                    }
                }
                else if ($row['status'] == "passed") {
                    $ects_sum_for_module_passed += $row['credits'];
                    $pdf->Cell(25,5,"passed",1,0,'R',$fill);
                } 
                }
                            

                $pdf->Ln();

                $fill = !$fill;
                $lastmod=$row['modul'];
                $laststatus = $row['status'];
            }
            $pdf->SetFont('Arial','B',$coursenameFont);
            if($lastmod == "Language"){
            $text = "ECTS points with marks from this module / track:
In 1st and 2nd semester: min-7 & max-9; In 3rd semester: min-3 & max-5";                    
            $yBeforeCell = $pdf->GetY();
            $pdf->MultiCell(150,5,$text,1,'L');
            $pdf->SetXY($x + 155, $yBeforeCell);
            }
            elseif($lastmod == "Module I: Structure & Properties"){
            $pdf->Cell(150,5,"ECTS points with marks from this module / track:  (min-12 & max-15)",1,0,'L');
            }
            elseif($lastmod == "Module II: Materials Characterization"){
            $pdf->Cell(150,5,"ECTS points with marks from this module / track:  (min-5 & max-8)",1,0,'L');
            }
            elseif($lastmod == "Module III: Materials Engineering & Processing Technologies"){
            $pdf->Cell(150,5,"ECTS points with marks from this module / track:  (min-5 & max-8)",1,0,'L');
            }
            else{
            $pdf->Cell(150,5,"ECTS points with marks from this module / track:",1,0,'L');
            }
            $sum_ects_and_passed=$ects_sum_for_module_grade+$ects_sum_for_module_passed;
            if($lastmod == "Module I: Structure & Properties"){
            if($sum_ects_and_passed > 15){
            $sum_ects_and_passed = 15;
            }
            }
            elseif($lastmod == "Module II: Materials Characterization"){
            if($sum_ects_and_passed > 8){
            $sum_ects_and_passed = 8;
            }
            }
            elseif($lastmod == "Module III: Materials Engineering & Processing Technologies"){
            if($sum_ects_and_passed > 8){
            $sum_ects_and_passed = 8;
            }
            }
            
            if (($lastmod != "Language")) {$pdf->Cell(25,5,number_format($sum_ects_and_passed,1),1,0,'R');}
            else{ 
            
            $pdf->Cell(25,10,number_format($ects_sum_languages,1),1,0,'R');
            }
            if($lastmod == "Language"){
            $pdf->Cell(25,10,"",1,0,'L');
            }
            else{
            $pdf->Cell(25,5,"",1,0,'L');
            }
            $pdf->Ln();
            $pdf->Ln();
            $ects_sum_benotete += $ects_sum_for_module_grade;
            $ects_sum_nonote += $ects_sum_for_module_passed + $ects_sum_languages;
            $ects_sum_for_module_grade = $ects_sum_for_module_passed = $ects_sum_languages = 0.0;
        
        }
        if ($pdf->GetY() > 230) $pdf->AddPage();
    }
    }
    else{
    foreach ($parse_modul as $Mcourse) { // Einzelne Module mit eigenem Query isolieren
        $fill=1; // Hintergrund fuellen

        $query_grades = "SELECT status, modul, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                                AND university='UdS' AND modul='$Mcourse' AND status IN ('grade', 'passed') GROUP BY coursename"; // workaround query for selecting only the best local_grade
        $result_grades = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());
        if  (mysql_num_rows($result_grades) != 0) { 
            $pdf->SetFont('Arial','B',$ueberschriftCoursenameFont); // Names of the modules/tracks
            $pdf->Cell(200,7,$Mcourse,1,0,'L');
            $pdf->Ln();
                
            while($row = mysql_fetch_assoc($result_grades)) {   // Kurse aus Modul extrahieren
                $row['credits']= ereg_replace(",", ".", $row['credits']);
                $row['local_grade']= ereg_replace(",", ".", $row['local_grade']);

                if ( ($row['modul'] != "Language") && ($row['status'] == "grade") ) { // Noten ohne Sprachenschein for the Average Grade
                    $ects_sum_ohne_sprache += $row['credits'];
                    if ( isValidGrade("UdS", (float)$row['local_grade'])) $summe_note_ects += ( (float)$row['credits']*(float)$row['local_grade'] );
                    else echo "invalid grade: " . $row['local_grade'] . " - university: ". $row['university'] . "<br>";
                }
                
             ###### English - German description
                $query_grades = "SELECT english_description FROM amase_courses WHERE local_description = '$row[coursename]' GROUP BY english_description";
                $result_coursenames = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());
                while($row2 = mysql_fetch_assoc($result_coursenames)) {
                    $row['coursename'] = $row['coursename']. " - " . $row2['english_description'];
                }
             ###### English - German description   

                $pdf->SetFont('Arial','',$coursenameFont);
                $pdf->Cell(126,5, wordwrap(utf8_decode($row['coursename']), 60),1,0,'L',$fill);
                $pdf->Cell(24,5, $row['ects_grade'], 1,0,'R',$fill);
                $pdf->Cell(25,5,$row['credits'] ,1,0,'R',$fill);
                if (($row['status'] != "passed") && ($row['modul'] != "Language")) {
                    $ects_sum_for_module_grade += $row['credits'];
                    $pdf->Cell(25,5,$row['local_grade'],1,0,'R',$fill);
                }
                else if ($row['modul'] == "Language") {
                    $ects_sum_languages += $row['credits'];   
                    $pdf->Cell(25,5,$row['local_grade'],1,0,'R',$fill);
                    if ($ects_sum_languages > 8) $ects_sum_languages = 8;
                }
                else if ($row['status'] == "passed") {
                    $ects_sum_for_module_passed += $row['credits'];
                    $pdf->Cell(25,5,"passed",1,0,'R',$fill);
                }                                  

                $pdf->Ln();

                $fill = !$fill;
                $lastmod=$row['modul'];
                $laststatus = $row['status'];
            }
            $pdf->SetFont('Arial','B',$coursenameFont);
            $pdf->Cell(150,5,"ECTS points with marks from this module / track:",1,0,'L');
            if (($lastmod != "Language") && ($laststatus != "passed")) $pdf->Cell(25,5,number_format($ects_sum_for_module_grade,1),1,0,'R');
            else if (($lastmod != "Language") && ($laststatus == "passed")) $pdf->Cell(25,5,number_format($ects_sum_for_module_passed,1),1,0,'R');
            else $pdf->Cell(25,5,number_format($ects_sum_languages,1),1,0,'R');
            $pdf->Cell(25,5,"",1,0,'L');
            $pdf->Ln();
            $pdf->Ln();
            $ects_sum_benotete += $ects_sum_for_module_grade;
            $ects_sum_nonote += $ects_sum_for_module_passed + $ects_sum_languages;
            $ects_sum_for_module_grade = $ects_sum_for_module_passed = $ects_sum_languages = 0.0;
        } 
        
        if ($pdf->GetY() > 230) $pdf->AddPage();
    }
    }

            if($student_from_db["start_semester"] >= 2011){ //added by manpreet
        $fill=1; // Hintergrund fuellen

        $query_zus_leist = "SELECT status, modul, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                                AND university='UdS' AND modul='Zusaetzliche Leistung' AND status IN ('grade', 'passed') GROUP BY coursename"; // workaround query for selecting only the best local_grade
        $result_zus_leist = mysql_query($query_zus_leist) OR die("Error: could not retrieve grades from database: " . mysql_error());
        if  (mysql_num_rows($result_zus_leist) != 0) { 
                    $pdf->SetFont('Arial','B',$ueberschriftCoursenameFont); // Names of the modules/tracks
                    $pdf->Cell(200,7,"Zusaetzliche Leistung ",1,0,'L');
                    $pdf->Ln();
                    $pdf->Cell(126,7,'Course',1,0,'C');
                    $pdf->Cell(24,7,'ECTS grade',1,0,'C');
                    $pdf->Cell(25,7,"ECTS credits",1,0,'C');
                    $pdf->Cell(25,7,"Local grade",1,0,'C');
                    $pdf->Ln();
            while($row = mysql_fetch_assoc($result_zus_leist)) {   // Kurse aus Modul extrahieren
                $row['credits']= ereg_replace(",", ".", $row['credits']);
                $row['local_grade']= ereg_replace(",", ".", $row['local_grade']);
             ###### English - German description
                $query_grades = "SELECT english_description FROM amase_courses WHERE local_description = '$row[coursename]' GROUP BY english_description";
                $result_coursenames = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());
                while($row2 = mysql_fetch_assoc($result_coursenames)) {
                    $row['coursename']  = $row['coursename']. " - " . $row2['english_description'];
                }
                $pdf->SetFont('Arial','',$coursenameFont);
                $rowHeight = 5;
                if(strlen($row['coursename'])>60){
                $yBeforeCell = $pdf->GetY();
                $pdf->MultiCell(126,5,utf8_decode($row['coursename']),1,'L',$fill);
                $yCurrent = $pdf->GetY();
                $rowHeight = $yCurrent - $yBeforeCell;
                $pdf->SetXY($x + 131, $yBeforeCell);
                }
                else{
                $pdf->Cell(126,5, wordwrap(utf8_decode($row['coursename']), 60),1,0,'L',$fill);
                }
                $pdf->Cell(24,$rowHeight, $row['ects_grade'], 1,0,'R',$fill);
                
                $pdf->Cell(25,$rowHeight,$row['credits'] ,1,0,'R',$fill);
                $ects_sum_zus_leist = $ects_sum_zus_leist + $row['credits'];
                if ($row['status'] != "passed"){
                    $pdf->Cell(25,$rowHeight,$row['local_grade'],1,0,'R',$fill);
                }
                else if ($row['status'] == "passed") {
                    $pdf->Cell(25,$rowHeight,"passed",1,0,'R',$fill);
                }                                  

                $pdf->Ln();

                $fill = !$fill;
                }
                $pdf->SetFont('Arial','B',$coursenameFont);
                $text = "Note: The marks of the additional courses are not considered in the average grade.";
                $pdf->Cell(200,5,$text,1,0,'L', 0);
                $pdf->Ln();
                $pdf->Ln();
                }
                }
if($student_from_db["start_semester"] >= 2011){
         $query_check = "SELECT status, modul, university, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                                AND (university='LTU' OR university='UPC' OR university='UL') AND status IN ('grade', 'passed') GROUP BY coursename"; // workaround query for selecting only the best local_grade
         $result_check = mysql_query($query_check) OR die("Error: could not retrieve grades from database: " . mysql_error());
         $partner_uni = mysql_fetch_assoc($result_check);
        if  (mysql_num_rows($result_check) != 0) {  
    $pdf->SetFont('Arial','B',$ueberschriftCoursenameFont); // Names of the modules/tracks
    $pdf->Cell(200,7,"Partner Universities- ".$partner_uni['university'],1,0,'L');
    $pdf->Ln();
    $pdf->Cell(100,7,'Course',1,0,'C');
    $pdf->Cell(26,7,'Module/Track',1,0,'C');
    $pdf->Cell(24,7,'ECTS grade',1,0,'C');
    $pdf->Cell(25,7,"ECTS credits",1,0,'C');
    $pdf->Cell(25,7,"Local grade",1,0,'C');
    $pdf->Ln();
    }
    $fill=1;
foreach ($parse_modul_new as $Mcourse){

        $query_partner_uni = "SELECT status, university, modul, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                                AND (university='LTU' OR university='UPC' OR university='UL') AND modul='$Mcourse' AND status IN ('grade', 'passed') GROUP BY coursename"; // workaround query for selecting only the best local_grade
        $result_partner_uni = mysql_query($query_partner_uni) OR die("Error: could not retrieve grades from database: " . mysql_error());
        
          while($row = mysql_fetch_assoc($result_partner_uni)) {   // Kurse aus Modul extrahieren
                $row['credits']= ereg_replace(",", ".", $row['credits']);
                $row['local_grade']= ereg_replace(",", ".", $row['local_grade']);
                
                $row['local_grade'] = convert_grade($row['local_grade'], $row['university']);
                
                if ( ($row['modul'] != "Language") && ($row['status'] == "grade") ) { // Noten ohne Sprachenschein for the Average Grade
                    $ects_sum_ohne_sprache += $row['credits'];
                    $ects_sum_benotete_partner_uni += $row['credits'];
                    if ( isValidGrade("UdS", (float)$row['local_grade'])) $summe_note_ects += ( (float)$row['credits']*(float)$row['local_grade'] );
                    else echo "invalid grade: " . $row['local_grade'] . " - university: ". $row['university'] . "<br>";
                }
                $query_grades = "SELECT english_description FROM amase_courses WHERE local_description = '$row[coursename]' GROUP BY english_description";
                $result_coursenames = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());
                while($row2 = mysql_fetch_assoc($result_coursenames)) {
                    $row['coursename'] = $row['coursename']. " - " . $row2['english_description'];
                }
                if($row['modul'] == "Language")
                 {
           $pdf->SetFont('Arial','B', $coursenameFont); // Names of the modules/tracks
           $pdf->Cell(200,5,$row['modul'],1,0,'L');
           $pdf->Ln();
                  }
                if ($row['modul'] != "Language"){
                $pdf->SetFont('Arial','',$coursenameFont);
                $rowHeight = 5;
                if(strlen($row['coursename'])>60){
                $yBeforeCell = $pdf->GetY();
                $pdf->MultiCell(100,5,utf8_decode($row['coursename']),1,'L',$fill);
                $yCurrent = $pdf->GetY();
                $rowHeight = $yCurrent - $yBeforeCell;
                $pdf->SetXY($x + 105, $yBeforeCell);
                }
                else{
                $pdf->Cell(100,5, wordwrap(utf8_decode($row['coursename']), 60),1,0,'L',$fill);
                }
                
                  if ( ($row['modul'] != "Language") && ($row['status'] == "passed") ) {
                  $ects_sum_for_module_passed_partner_uni += $row['credits'];
                  }
                $trackname = substr($row['modul'], 0, 10); // for Module I, Module II, Module III...
                
                if ($trackname == 'Module III')
                {
                   $trackname = 'Module III';
                }
                elseif ($trackname == 'Module II:')
                {
                   $trackname = 'Module II';
                }
                elseif ($trackname == 'Module I: ')
                {
                   $trackname = 'Module I';
                }
                elseif(substr($row['modul'], 0, 6) == 'Module'){ // for Module 1, Module 2...
                   $trackname = substr($row['modul'], 0, 8);
                }
                else // for tracks i.e. Track 1 , Track 2 and so on
                {
                 $trackname = substr($row['modul'], 0, 7);
                }
                $pdf->Cell(26,$rowHeight, $trackname, 1,0,'R',$fill);
                $pdf->Cell(24,$rowHeight, $row['ects_grade'], 1,0,'R',$fill);
                $pdf->Cell(25,$rowHeight,$row['credits'] ,1,0,'R',$fill);
                $pdf->Cell(25,$rowHeight,$row['local_grade'] ,1,0,'R',$fill);
                 }
                 else{
                 $pdf->SetFont('Arial','',$coursenameFont);
                $pdf->Cell(126,5, wordwrap(utf8_decode($row['coursename']), 60),1,0,'L',$fill);
                $pdf->Cell(24,5, $row['ects_grade'], 1,0,'R',$fill);
                $pdf->Cell(25,5,$row['credits'] ,1,0,'R',$fill);
                $pdf->Cell(25,5,$row['local_grade'] ,1,0,'R',$fill);
                $ects_sum_sprache_partner_uni += $row['credits'] ;
                 }
            
        $pdf->Ln();
        $fill = !$fill;
        if ($pdf->GetY() > 230) $pdf->AddPage();
}

}

$pdf->SetFont('Arial','',$coursenameFont);
    $query_master_thesis_partner_uni = "SELECT projectname,university,examiner1, examiner2, examiner_grade1, examiner_grade2, credits_master, local_grade_master FROM amase_master WHERE student_id='$id' AND status IN ('grade', 'passed') AND (university='LTU' OR university='UPC' OR university='UL')";
    $result_master_thesis_partner_uni = mysql_query($query_master_thesis_partner_uni) OR die("Error: could not retrieve grades from database: " . mysql_error());


          
    if  ( mysql_num_rows($result_master_thesis_partner_uni) != 0 ) { 
        $row = mysql_fetch_assoc($result_master_thesis_partner_uni);
        $pdf->SetFont('Arial','B',$coursenameFont);
        if ($pdf->GetY() > 230) $pdf->AddPage();
        $pdf->Cell(200,5, "Master Thesis" ,1,0,'L');
        
        $pdf->Ln();
        $pdf->SetFont('Arial','', $coursenameFont);
         
        $x=$pdf->GetX();
        $y=$pdf->GetY();
        if (strlen($row['projectname']) < 65)
        {
        $pdf->MultiCell(92,10, ereg_replace("\n", " ", utf8_decode($row['projectname'])),1,'L',0); // when length of title is only one line make the height of the cell 10
        }
        else{
        $pdf->MultiCell(92,5, ereg_replace("\n", " ", utf8_decode($row['projectname'])),1,'L',0);
        }
        $x2 = $x + 92;
        $pdf->SetXY($x2,$y);

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(58,5, utf8_decode($row['examiner1']) . "\n" .  utf8_decode($row['examiner2']),1,'L',0);
        $x2 = $x + 58;
        $pdf->SetXY($x2,$y);        

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(25,5, "30.0\n ",1,0,'R');
        $x2 = $x + 25;
        $pdf->SetXY($x2,$y); 
        $ects_sum_master_thesis = 30.0; // ECTS points for thesis
        
        $xx=$pdf->GetX();
        $yy=$pdf->GetY();
        $pdf->MultiCell(25,5, $row['examiner_grade1'] . "\n" .  $row['examiner_grade2'],1,0,'R'); 
        $pdf->SetFont('Arial','B',$coursenameFont);
        
        $pdf->Cell(150,5,"Avg. grade: ",1,0,'L');
        $pdf->Cell(25,5,"--",1,0,'C');
        $i = 0;
        if (isset($row['examiner_grade1'])) $i++;
        if (isset($row['examiner_grade2'])) $i++;
        $pdf->Cell(25,5,substr( (($row['examiner_grade1'] + $row['examiner_grade2']) / $i), 0, 3 ),1,0,'R');
        $pdf->SetXY($x2,$y+10); // 2 Zeilen Platz lassen um die Punktzahl einzutragen
        // $pdf->Cell(25,5, "Total: " .  $row['credits'],1,0,'R');
        $pdf->Ln();
        $pdf->Ln();

        //$pdf->Cell(150,5, "Total credit points from courses and master project: " ,1,0,'L');

        //$pdf->Cell(25,5,number_format($ects_sum_benotete + $ects_sum_nonote + 30,1), 1, 0, 'R');
        //$pdf->Cell(25,5,"--", 1, 0, 'C');
    }
//$pdf->Ln();
//$pdf->Ln();

###### MASTER THESIS
    $pdf->SetFont('Arial','',$coursenameFont);
    $query_master = "SELECT projectname,university,examiner1, examiner2, examiner_grade1, examiner_grade2, credits_master, local_grade_master FROM amase_master WHERE student_id='$id' AND status IN ('grade', 'passed') AND university='UdS'";
    $result_master = mysql_query($query_master) OR die("Error: could not retrieve grades from database: " . mysql_error());

    //$pdf->Ln();
    //$pdf->Ln();
    

    if  ( mysql_num_rows($result_master) != 0 ) { 
        $row = mysql_fetch_assoc($result_master);
        $pdf->Ln();
        if ($pdf->GetY() > 210) $pdf->AddPage();
        $pdf->SetFont('Arial','B',$ueberschriftCoursenameFont);
        $pdf->Cell(92,5, "Master Thesis- UdS" ,1,0,'L');
        $pdf->Cell(58,5, "Examiner" ,1,0,'L');
        $pdf->Cell(25,5, "ECTS credits" ,1,0,'C');
        $pdf->Cell(25,5, "Local grade" ,1,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','', $coursenameFont);
        
        $x=$pdf->GetX();
        $y=$pdf->GetY();
        if (strlen($row['projectname']) < 65){
            $pdf->MultiCell(92,15, ereg_replace("\n", " ", utf8_decode($row['projectname'])),1,'L',0); // when length of title is only one line make the height of the cell 10
        }else if (strlen($row['projectname']) < 130){
            $pdf->MultiCell(92,5, ereg_replace("\n", " ", utf8_decode($row['projectname']))."\n\n",1,'L',0);
        }else{
            $pdf->MultiCell(92,5, ereg_replace("\n", " ", utf8_decode($row['projectname'])),1,'L',0);
        }
        $x2 = $x + 92;
        $pdf->SetXY($x2,$y);

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(58,5, utf8_decode($row['examiner1']) . "\n" .  utf8_decode($row['examiner2']) . "\n\n",1,'L',0);
        $x2 = $x + 58;
        $pdf->SetXY($x2,$y);       

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(25,5, "30.0\n\n ",1,0,'R');
        $x2 = $x + 25;
        $pdf->SetXY($x2,$y); 
        $ects_sum_master_thesis = 30.0;
        
        $xx=$pdf->GetX();
        $yy=$pdf->GetY();
        $pdf->MultiCell(25,5, $row['examiner_grade1'] . "\n" .  $row['examiner_grade2'] . "\n\n",1,0,'R'); 
        $pdf->SetFont('Arial','B',$coursenameFont);


        $pdf->Cell(150,5,"Avg. grade: ",1,0,'L');
        $pdf->Cell(25,5,"--",1,0,'C');
        $i = 0;
        if (isset($row['examiner_grade1'])) $i++;
        if (isset($row['examiner_grade2'])) $i++;
        $average_master = (($row['examiner_grade1'] + $row['examiner_grade2']) / $i);
        $average_master = number_format(convert_grade($average_master, 'UDS'),1);
        $pdf->Cell(25,5,substr($average_master , 0, 3 ),1,0,'R');
        $pdf->SetXY($x2,$y+10); // 2 Zeilen Platz lassen um die Punktzahl einzutragen
        // $pdf->Cell(25,5, "Total: " .  $row['credits'],1,0,'R');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();

       // $pdf->Cell(150,5, "Total credit points from courses and master project: " ,1,0,'L');

        //$pdf->Cell(25,5,number_format($ects_sum_benotete + $ects_sum_nonote + 30,1), 1, 0, 'R');
        //$pdf->Cell(25,5,"--", 1, 0, 'C');
    }
    $pdf->Ln();
###### ECTS CREDIT POINTS SUMS  
    if ($pdf->GetY() > 230) $pdf->AddPage();
    $pdf->SetFont('Arial','B',$coursenameFont);
    $pdf->Cell(150,5,"Total ECTS credit points from noted courses UdS" ,1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_benotete,1).$ects_sum_array["benotete"],1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln();
    $pdf->Cell(150,5,"Total ECTS credit points from unnoted courses UdS" ,1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_nonote,1),1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln();
    $pdf->Cell(150,5,"Total ECTS credit points from Zusaetzliche Leistung UdS" ,1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_zus_leist,1),1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln();
    $pdf->Cell(150,5,"Total ECTS credit points from all courses UdS" ,1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_benotete + $ects_sum_nonote + $ects_sum_zus_leist,1).$ects_sum_array["total"],1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln(); 
    $pdf->Cell(150,5,"Total ECTS credit points from all courses and master project ".$student_from_db['university1']." & ".$student_from_db['university2'] ,1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_benotete + $ects_sum_nonote + $ects_sum_benotete_partner_uni + $ects_sum_sprache_partner_uni + $ects_sum_for_module_passed_partner_uni + $ects_sum_zus_leist + $ects_sum_master_thesis,1)."/120.0",1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln(); 
    
###### ECTS CREDIT POINTS SUMS  
    
    $pdf->SetFont('Arial','B',$$coursenameFont);    /// Durchschnittsnote
    $pdf->Cell(150,5, "Avg. grade",1,0,'L');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->SetFont('Arial','B',$coursenameFont);
    if ($ects_sum_ohne_sprache == 0) {
        $pdf->Cell(25,5, 'none',1,0,'R');
    }else{
        $average = ($summe_note_ects / $ects_sum_ohne_sprache);
        $pdf->Cell(25,5,substr($average,0,3),1,0,'R');
    }


}
else{
    
###### ECTS CREDIT POINTS SUMS  
    $pdf->SetFont('Arial','B',$coursenameFont);
    $pdf->Cell(150,5,"Total ECTS credit points from noted courses",1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_benotete,1).$ects_sum_array["benotete"],1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln();
    $pdf->Cell(150,5,"Total ECTS credit points from unnoted courses",1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_nonote,1),1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln();
    $pdf->Cell(150,5,"Total ECTS credit points from all courses",1,0,'L'); // Zellraum auffüllen
    $pdf->Cell(25,5,number_format($ects_sum_benotete + $ects_sum_nonote,1).$ects_sum_array["total"],1,0,'R');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->Ln();
###### ECTS CREDIT POINTS SUMS  
    
    $pdf->SetFont('Arial','B',$$coursenameFont);    /// Durchschnittsnote
    $pdf->Cell(150,5, "Avg. grade",1,0,'L');
    $pdf->Cell(25,5,"---",1,0,'C');
    $pdf->SetFont('Arial','B',$coursenameFont);
    if ($ects_sum_ohne_sprache == 0) $pdf->Cell(25,5, 'none',1,0,'R');
    else $pdf->Cell(25,5,substr(($summe_note_ects / $ects_sum_ohne_sprache),0,3),1,0,'R');

###### MASTER THESIS
    $pdf->SetFont('Arial','',$coursenameFont);
    $query_master = "SELECT projectname,university,examiner1, examiner2, examiner_grade1, examiner_grade2, credits_master, local_grade_master FROM amase_master WHERE student_id='$id' AND status IN ('grade', 'passed') AND university='UdS'";
    $result_master = mysql_query($query_master) OR die("Error: could not retrieve grades from database: " . mysql_error());

    $pdf->Ln();
    $pdf->Ln();
    if ($pdf->GetY() > 210) $pdf->AddPage();

    if  ( mysql_num_rows($result_master) != 0 ) { 
        $row = mysql_fetch_assoc($result_master);
        $pdf->SetFont('Arial','B',$ueberschriftCoursenameFont);
        $pdf->Cell(85,5, "Master Thesis" ,1,0,'L');
        $pdf->Cell(65,5, "Examiner" ,1,0,'L');
        $pdf->Cell(25,5, "ECTS credits" ,1,0,'C');
        $pdf->Cell(25,5, "Local grade" ,1,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','', $coursenameFont);

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(85,5, ereg_replace("\n", " ", utf8_decode($row['projectname'])),1,'L',0);
        $x2 = $x + 85;
        $pdf->SetXY($x2,$y);

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(65,5, utf8_decode($row['examiner1']) . "\n" .  utf8_decode($row['examiner2']),1,'L',0);
        $x2 = $x + 65;
        $pdf->SetXY($x2,$y);        

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(25,5, "30.0\n ",1,0,'R');
        $x2 = $x + 25;
        $pdf->SetXY($x2,$y); 
        
        $xx=$pdf->GetX();
        $yy=$pdf->GetY();
        $pdf->MultiCell(25,5, $row['examiner_grade1'] . "\n" .  $row['examiner_grade2'],1,0,'R'); 
        $pdf->SetFont('Arial','B',$coursenameFont);
        
        $pdf->Cell(150,5,"Avg. grade: ",1,0,'L');
        $pdf->Cell(25,5,"--",1,0,'C');
        $i = 0;
        if (isset($row['examiner_grade1'])) $i++;
        if (isset($row['examiner_grade2'])) $i++;
        $pdf->Cell(25,5,substr( (($row['examiner_grade1'] + $row['examiner_grade2']) / $i), 0, 3 ),1,0,'R');
        $pdf->SetXY($x2,$y+10); // 2 Zeilen Platz lassen um die Punktzahl einzutragen
        // $pdf->Cell(25,5, "Total: " .  $row['credits'],1,0,'R');
        $pdf->Ln();
        $pdf->Ln();

        $pdf->Cell(150,5, "Total credit points from courses and master project: " ,1,0,'L');

        $pdf->Cell(25,5,number_format($ects_sum_benotete + $ects_sum_nonote + 30,1), 1, 0, 'R');
        $pdf->Cell(25,5,"--", 1, 0, 'C');
    }
    }
    

    

$pdf->Output();

?>