<?php

    define('_VALID_INCLUDE', TRUE);

    //ugly fix to get fonts to work again as directory changed to usr share
    $fontprefixfix = "/www/htdocs/w006e175/amase/amasebase2/pdf/font/"; 
    define('FPDF_FONTPATH', $fontprefixfix);
    
    require('config.inc.php');
    require('modul.inc.php');
    require "./pdf/fpdi.php";
    require('grades.inc.php');

    session_name('amasebase');
    session_start();
    check_login();

    if ( !isset($_GET['id']) || !is_clean_digit($_GET['id']) ) {
            box(false, "no valid student-ID given", 1, 0);
            echo "</table>\n";
            echo "</body></html>";
            die();
    } else $id = $_GET['id'];

###### QUERY STUDENTS
    if (!($db_link)) die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error());
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
    $query = "SELECT nachname, vorname, gender, birth_date, geburtsort, geburtsland, university1, university2, matrikel1, matrikel2, start_semester FROM amase_students WHERE id='$id';";
    $result = mysql_query($query) OR die("Error: could not load student dataset: " . mysql_error());
    $student = mysql_fetch_assoc($result);
###### QUERY STUDENTS

###### REPLACEMENTS
    // Gender
    $get_gender = array('male' => 'Herr', 'female' => 'Frau');  
    if ( array_key_exists($student['gender'], $get_gender) ) { 
        $student['gender'] = $get_gender[$student['gender']];
        $student['gender'] == 'Herr' ? $ihm_oder_ihr = 'ihm' : $ihm_oder_ihr = 'ihr';
    }
    else die("unknown gender");
    // Birthday
    list($year, $month, $day) = split('-', $student['birth_date']); 
    $student['birth_date'] = date("d.m.Y", mktime(0, 0, 0, $month, $day, $year));
    // Matrikelnummer BUT ONLY FOR UDS
    if ($student['university1'] == "UdS") $student['matrikel'] = $student['matrikel1']; 
    elseif  ($student['university2'] == "UdS") $student['matrikel'] = $student['matrikel2'];
    else die ("Student ist kein Mitglied der UdS/ Student is not a member of the UdS");
###### REPLACEMENTS

###### QUERY GRADES
    $query2 = "SELECT * FROM amase_grades WHERE student_id='$id' AND university IN ('UdS') AND status='grade' AND ects_grade IN ('A', 'B', 'C', 'D', 'E');";
    $result2 = mysql_query($query2) OR die("Error: could not load students grades: " . mysql_error());
###### QUERY GRADES

###### QUERY MASTER THESIS
    $query3 = "SELECT * FROM amase_master WHERE student_id='$id' AND university='UdS';";
    $result3 = mysql_query($query3) OR die("Error: could not load students grades: " . mysql_error());
    $master = mysql_fetch_assoc($result3);
###### QUERY MASTER THESIS

###### QUERY FOR THE LATEST DATE
    $query4 = "SELECT max(ex_date) as datum FROM amase_grades WHERE student_id='$id' AND status IN ('grade', 'passed')";
    $result4 = mysql_query($query4) OR die("Error: could not load students grades: " . mysql_error());
    $latest_date1 = mysql_fetch_assoc($result4);
    $query5 = "SELECT ex_date as datum FROM amase_master WHERE student_id='$id' AND status='grade'";
    $result5 = mysql_query($query5) OR die("Error: could not load students grades: " . mysql_error());
    $latest_date2 = mysql_fetch_assoc($result5);
###### QUERY FOR THE LATEST DATE

###### LATEST DATE
    if  (mysql_num_rows($result5) != 0) { // date of the master thesis
        $masterEndnote = substr((((float)$master['examiner_grade1'] + (float)$master['examiner_grade2']) / 2 ),0,3);
        if ($latest_date1['datum'] > $latest_date2['datum']) {
            list($year1, $month1, $day1) = split('-', $latest_date1['datum']);
            $latest_date = date("d.m.Y", mktime(0, 0, 0, $month1, $day1, $year1));
        } else {
            list($year2, $month2, $day2) = split('-', $latest_date2['datum']);
            $latest_date = date("d.m.Y", mktime(0, 0, 0, $month2, $day2, $year2));
        }
    } else {
        list($year1, $month1, $day1) = split('-', $latest_date1['datum']);
        $latest_date = date("d.m.Y", mktime(0, 0, 0, $month1, $day1, $year1));
    }
###### LATEST DATE
    
    class fpdf_amase extends fpdi {
            function rand() {
                    $this->SetXY(26, $this->GetY() );   // SETS THE X AND Y OF THE THE SECOND PAGE
            }
    }

    $pdf= new fpdf_amase(); //Instanciation of inherited class, FIXES ALSO THE PROBLEM WITH THE BOTTOM MARGIN


        
    $start_year = substr($student['start_semester'], 0, 4); // for new exam regulations 2011 (added by manpreet)
	if ($start_year >= 2011){
    $pagecount = $pdf->setSourceFile("./pdf/Urkunde-Muster-neu v3.pdf");
	}else{	
    $pagecount = $pdf->setSourceFile("./pdf/Urkunde-Muster-old v2.pdf");
	}
   

    $tplidx = $pdf->ImportPage(1);
    $pdf->addPage();
    $pdf->useTemplate($tplidx);
    $pdf->AddFont('Univers55','','univers55.php');
    $pdf->AddFont('Univers55','B','univers55b.php');
    $pdf->AddFont('Univers57cn','','univers57cn.php');
    $pdf->AddFont('Univers57cn','B','univers57cnb.php');

    $studFullName = " " . utf8_decode($student['vorname']) . " " . utf8_decode($student['nachname']); // Name
    $pdf->SetFont('Univers55','',18);
    $genderLen  = $pdf->GetStringWidth($student['gender']);
    $pdf->SetFont('Univers55','B',18);
    $nameLen	=  $pdf->GetStringWidth($studFullName);
    $textmiddle = 104.0 -($nameLen/2.0) -($genderLen/2.0);  // WIDTH

    $pdf->SetXY($textmiddle, 120); 
    $pdf->SetFont('Univers55','',18);
    $pdf->SetTextColor(0, 51, 136);
    $pdf->Write(10, $student['gender']);
    $pdf->SetFont('Univers55','B',18);
    $pdf->Write(10, $studFullName);
    $pdf->SetTextColor(0);
    
    $pdf->SetXY(108, 198);
    $pdf->SetFont('Univers55','',12);
    $pdf->Write(10,$ihm_oder_ihr);

    $pdf->SetXY(0, 130); // Geburtsdaten   
    $pdf->SetFont('Univers57cn','',12);
    $pdf->MultiCell(210,5,"geboren am " . $student['birth_date'] . "\n" .  "in " .  utf8_decode($student['geburtsort']) . ", " .  utf8_decode($student['geburtsland']) . "\n",0,'C'); 

    $pdf->SetXY(0, 228); // Letztes Pr�fungsdatum
    $pdf->SetFont('Univers57cn','',13);

    $printDate = date("d.m.Y");
    //$printDate = "01.12.2016";
    $pdf->MultiCell(207,4,utf8_decode("Datum der letzten Prüfungsleistung: ") . $latest_date . utf8_decode("\n\nSaarbrücken, den ") . $printDate,0,'C');

    $pdf->Output();
?>