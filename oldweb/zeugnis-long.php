<?php

    define('_VALID_INCLUDE', TRUE);

    //ugly fix to get fonts to work again as directory changed to usr share
    $fontprefixfix = "/www/htdocs/w006e175/amase/amasebase2/pdf/font/"; 
    define('FPDF_FONTPATH', $fontprefixfix);

    require('config.inc.php');
    require('modul.inc.php');
    require('./pdf/fpdi.php');
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
    if ( array_key_exists($student['gender'], $get_gender) ) $student['gender'] = $get_gender[$student['gender']];
    else die("unknown gender");
    // Birthday
    list($year, $month, $day) = split('-', $student['birth_date']); 
    $student['birth_date'] = date("d.m.Y", mktime(0, 0, 0, $month, $day, $year));
    // Matrikelnummer BUT ONLY FOR UDS
    if ($student['university1'] == "UdS") $student['matrikel'] = $student['matrikel1']; 
    elseif  ($student['university2'] == "UdS") $student['matrikel'] = $student['matrikel2'];
    else die ("Student ist kein Mitglied der UdS");
###### REPLACEMENTS

###### QUERY GRADES
    $query2 = "SELECT * FROM amase_grades WHERE student_id='$id' AND university IN ('UdS') AND status='grade' AND ects_grade IN ('A', 'B', 'C', 'D', 'E');";
    $result2 = mysql_query($query2) OR die("Error: could not load students grades: " . mysql_error());
###### QUERY GRADES

###### QUERY MASTER THESIS
    $query3 = "SELECT * FROM amase_master WHERE student_id='$id' AND university='UdS' AND status='grade';";
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
                    $this->SetXY(10, $this->GetY() );   // SETS THE X AND Y OF THE THE SECOND PAGE
            }
    }

    $pdf= new fpdf_amase(); //Instanciation of inherited class, FIXES ALSO THE PROBLEM WITH THE BOTTOM MARGIN
    
	$start_year = substr($student['start_semester'], 0, 4); // for new exam regulations 2011 (added by manpreet)
	if ($start_year >= 2011)//added by manpreet
	{
	$pdf->setSourceFile("./pdf/zeugnis_blanco_new.pdf");
	}
	
	else
    {
    $pdf->setSourceFile("./pdf/zeugnis_blanco.pdf");
	}
    $tplidx = $pdf->ImportPage(1);
    $pdf->addPage('P','mm','Letter');
    $pdf->useTemplate($tplidx);
    $pdf->SetAutoPageBreak(true, 10);
    
    
    $pdf->AddFont('Univers55','','univers55.php');
    $pdf->AddFont('Univers55','B','univers55b.php');
    $pdf->AddFont('Univers57cn','','univers57cn.php');
    $pdf->AddFont('Univers57cn','B','univers57cnb.php');

    $studFullName = " " . utf8_decode($student['vorname']) . " " . utf8_decode($student['nachname']); // Name
    $pdf->SetFont('Univers55','',18);
    $genderLen  = $pdf->GetStringWidth($student['gender']);
    $pdf->SetFont('Univers55','B',18);
    $nameLen	=  $pdf->GetStringWidth($studFullName);
    $textmiddle = 103 - ($nameLen/2.0) -($genderLen/2.0);  // WIDTH

    $pdf->SetXY($textmiddle, 130); 
    $pdf->SetFont('Univers55','',18);
    $pdf->SetTextColor(0, 51, 136);
    $pdf->Write(10, $student['gender']);
    $pdf->SetFont('Univers55','B',18);
    $pdf->Write(10, $studFullName);
    $pdf->SetTextColor(0);

    $pdf->SetXY(0, 140); // Geburtsdaten   
    $pdf->SetFont('Univers57cn','',12);
    $pdf->MultiCell(206,5,"geboren am " . $student['birth_date'] . "\n" .  "in " .  utf8_decode($student['geburtsort']) . ", " .  utf8_decode($student['geburtsland']) . "\n",0,'C'); 

    $pdf->SetXY(0, 218); // Letztes Prüfungsdatum
    $pdf->SetFont('Univers57cn','',12);
    $pdf->MultiCell(206.5,5,"Datum der letzten Prüfungsleistung: " . $latest_date . "\n\nSaarbrücken, den " . date("d.m.Y"),0,'C');
    
################################## 2 SEITE ##################################
    
    $ects_sum = 0.0;
    $ects_sum_ohne_sprache = 0.0;
    $summe_note_ects = 0.0;
    
    $ects_sum_languages = 0.0;

    $module_ects = 0.0;
	$partner_uni_ects = 0.0;
	$partner_uni_noted = 0.0;
	$zus_leist_ects = 0.0;
    $module_summe_gradedEcts = 0.0;
    $module_summe_grades = 0.0;
    
    $gradeSize = 8;
    $courseSize = 10;
	$Partner_Uni = '';

    $tplidx = $pdf->ImportPage(2);
    $pdf->addPage();
    $pdf->useTemplate($tplidx);

###### NAME AND SIMPLE LINE    
    $pdf->SetXY(0, 22); // name    
    $pdf->SetFont('Univers55','',18);
    $pdf->Cell(210,5,$studFullName, 0,0, 'C');
    $ly = $pdf->GetY() + 6;
    $padding = 15;
    $pdf->Line(0+$padding, $ly, 210-$padding, $ly );
###### NAME AND SIMPLE LINE

######  HEAD TITLES
    $pdf->SetXY(0, 30);
    $pdf->SetFont('Univers57cn','',$gradeSize);    
    $pdf->rand();
    $pdf->Cell(130,5,"Prüfungsleistungen",1,0,'L');
    $x=$pdf->GetX();
    $y=$pdf->GetY();
    $pdf->Cell(20,5,"ECTS-Punkte",1,0, 'C');
    $x = $x + 20;
    $pdf->SetXY($x,$y); 
    $pdf->Cell(20,5,"Note",1,0,'C');
    $pdf->Cell(20,5,"ECTS-Note",1,0, 'C');
    $pdf->Ln();
######  HEAD TITLES
    
######  EACH MODULE/ TRACK AND ITS COURSES  
if ($start_year >= 2011){ //According to new regulations March 2011
   foreach ($parse_modul_new as $Mcourse) {
        $query_grades = "SELECT status, modul, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                        AND university='UdS' AND modul='$Mcourse' AND status IN ('grade', 'passed') GROUP BY coursename";
        $result_grades = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());

        if  (mysql_num_rows($result_grades) != 0) { 
            $pdf->rand();
            $pdf->SetFont('Univers57cn','b',$courseSize);
            $pdf->Cell(130,7, $Mcourse,1,0,'L');
			$modulX=$pdf->GetX();
            $modulY=$pdf->GetY();
            $pdf->Ln();	
			$pdf->SetFont('Univers57cn','b',$gradeSize);
            if($Mcourse == "Language"){
			$text = "ECTS-Punkte in diesem Modul / Track:
Im 1. und 2. Semester: min-7 & max-9; Im 3. Semester: min-3 & max-5";
			$pdf->MultiCell(190,5,$text,1,'L');		
			}
			elseif($Mcourse == "Module I: Structure & Properties"){
			$pdf->Cell(190,5,"ECTS-Punkte in diesem Modul / Track:  (min-12 & max-15)",1,0,'L');
			$pdf->Ln();
			}
			elseif($Mcourse == "Module II: Materials Characterization"){
			$pdf->Cell(190,5,"ECTS-Punkte in diesem Modul / Track:  (min-5 & max-8)",1,0,'L');	 
			$pdf->Ln();
			}
			elseif($Mcourse == "Module III: Materials Engineering & Processing Technologies"){
			$pdf->Cell(190,5,"ECTS-Punkte in diesem Modul / Track:  (min-5 & max-8)",1,0,'L');
			$pdf->Ln();
			}
           				

            while($row = mysql_fetch_assoc($result_grades)) {  // Kurse aus Modul extrahieren
                $row['credits']= ereg_replace(",", ".", $row['credits']);
                $row['local_grade']= ereg_replace(",", ".", $row['local_grade']);

                if (($row['modul'] != "Language") && ($row['status'] == "grade")) {
                    if($row['modul'] != "Zusaetzliche Leistung"){				
                    $ects_sum_ohne_sprache += $row['credits'];
					}
					else{
					$zus_leist_ects += $row['credits'];
					}
                    $module_summe_gradedEcts += $row['credits'];

                    if (isValidGrade("UdS",(float)$row['local_grade'])) {
					      if($row['modul'] != "Zusaetzliche Leistung"){
                        $summe_note_ects += ( (float)$row['credits']*(float)$row['local_grade'] );
						}
                        $module_summe_gradesXEctsGrades += ( (float)$row['credits']*(float)$row['local_grade'] );
                    } else echo "invalid grade: " . $row['local_grade'] . " - university: ". $row['university'] . "<br>";
                } else if ($row['modul'] == "Language") {
                    $ects_sum_languages += $row['credits'];   
                    if($student["university1"]== "UdS"){
                    if ($ects_sum_languages > 9) $ects_sum_languages = 9;
					}
					elseif($student["university2"]== "UdS"){
					if ($ects_sum_languages > 5) $ects_sum_languages = 5;
					}
                    }
                 else if (($row['modul'] != "Language") && ($row['status'] == "passed")) {
                    $ects_sum_passed += $row['credits']; 
                }

            ###### One line
                $module_ects += (float) $row['credits']; 	// Modulcredits
                //		// Komplettsumme Credits
                $pdf->rand();
                $pdf->SetFont('Univers57cn','',$gradeSize);
                $pdf->Cell(130,5, utf8_decode($row['coursename']),0,0,'L');
                $pdf->Cell(20,5,$row['credits'] ,0,0,'C');

                // bestanden for passed. The actual grade for grade
                if ( $row['status'] == "passed" ) $pdf->Cell(20,5,"bestanden",0,0,'C');
                else $pdf->Cell(20,5,$row['local_grade'],0,0,'C');

                // ECTS-Grade
                if ( $row['ects_grade'] == "not set" ) $pdf->Cell(20,5, "--", 0,0,'C');
                else $pdf->Cell(20,5, $row['ects_grade'], 0,0,'C');

                $pdf->Ln();
            ###### One line				

                $lastmod=$row['modul'];
            }
            $ects_sum = $ects_sum_passed + $ects_sum_ohne_sprache + $ects_sum_languages;
            
        ###### MODULE AVERAGE            
            $actX=$pdf->GetX();
            $actY=$pdf->GetY();
            $pdf->SetXY($modulX, $modulY);
			if($lastmod == "Module I: Structure & Properties"){
    			if($module_ects > 15){
    			$module_ects = 15;
    			}
			}elseif($lastmod == "Module II: Materials Characterization"){
    			if($module_ects> 8){
    			$module_ects = 8;
    			}
			}elseif($lastmod == "Module III: Materials Engineering & Processing Technologies"){
    			if($module_ects > 8){
    			$module_ects = 8;
    			}
			}
            $pdf->SetFont('Univers57cn','b',$courseSize);
            if ($lastmod != "Language") {
                $pdf->Cell(20,7, number_format( $module_ects, 1), 1, 0, 'C');
            }else{
                $pdf->Cell(20,7, number_format( $ects_sum_languages, 1), 1, 0, 'C');
            }
            if ($module_summe_gradedEcts != 0.0) {
                $average_master = number_format(convert_grade(($module_summe_gradesXEctsGrades/$module_summe_gradedEcts), 'UDS'),1);
                $pdf->Cell(20,7, substr( $average_master, 0, 3),1,0,'C');
            }else{
                $pdf->Cell(20,7, "--",1,0,'C');
            }
            $pdf->Cell(20,7, "", 1, 0, 'C');
        ###### MODULE AVERAGE 
            
            $pdf->SetXY($actX, $actY);
            $module_ects = $module_summe_gradedEcts = $module_summe_gradesXEctsGrades = 0.0;
        }
    }
 $query_check = "SELECT status, modul, university, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                 AND (university='LTU' OR university='UPC' OR university='UL') AND status IN ('grade', 'passed') GROUP BY coursename"; // workaround query for selecting only the best local_grade
         $result_check = mysql_query($query_check) OR die("Error: could not retrieve grades from database: " . mysql_error());
		 $partner_uni = mysql_fetch_assoc($result_check);
		if  (mysql_num_rows($result_check) != 0) {	
	         $pdf->rand();
            $pdf->SetFont('Univers57cn','b',$courseSize);
           $pdf->Cell(130,7,"Partner Universities - ".$partner_uni['university'],1,0,'L');
           $modulX=$pdf->GetX();
            $modulY=$pdf->GetY();
			$Partner_Uni = $partner_uni['university'];
            $pdf->Ln();	
			$pdf->SetFont('Univers57cn','b',$gradeSize);
	}
	foreach ($parse_modul_new as $Mcourse){

        $query_partner_uni = "SELECT status, university, modul, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                                AND (university='LTU' OR university='UPC' OR university='UL') AND modul='$Mcourse' AND status IN ('grade', 'passed') GROUP BY coursename"; // workaround query for selecting only the best local_grade
        $result_partner_uni = mysql_query($query_partner_uni) OR die("Error: could not retrieve grades from database: " . mysql_error());
		
		  while($row = mysql_fetch_assoc($result_partner_uni)) { 
    		   $row['credits']= ereg_replace(",", ".", $row['credits']);
               $row['local_grade']= ereg_replace(",", ".", $row['local_grade']);
               $row['local_grade']= convert_grade( $row['local_grade'], $row['university']);
	
				if (($row['modul'] != "Language") && ($row['status'] == "grade")) { 
                    $ects_sum_ohne_sprache += $row['credits'];
					$partner_uni_noted += $row['credits'];
                    $module_summe_gradedEcts += $row['credits'];

                    if (isValidGrade("UdS",(float)$row['local_grade'])) {
                        $summe_note_ects += ( (float)$row['credits']*(float)$row['local_grade'] );
                        $module_summe_gradesXEctsGrades += ( (float)$row['credits']*(float)$row['local_grade'] );
                    } else echo "invalid grade: " . $row['local_grade'] . " - university: ". $row['university'] . "<br>";
				}
				 $partner_uni_ects += (float) $row['credits']; 
				if($row['modul'] != "Language"){
				$pdf->rand();
                $pdf->SetFont('Univers57cn','',$gradeSize);
				$query_coursename = "SELECT english_description FROM amase_courses WHERE local_description = '$row[coursename]' GROUP BY english_description";
                $result_coursenames = mysql_query($query_coursename) OR die("Error: could not retrieve grades from database: " . mysql_error());
                while($row2 = mysql_fetch_assoc($result_coursenames)) {
                    $row['coursename'] = preg_replace("/\r|\n/s", "",$row['coursename']). " - " . preg_replace("/\r|\n/s", "",$row2['english_description']);
                }
                if(strlen($row['coursename'])> 120){
                    $pdf->Cell(130,5, substr(utf8_decode($row['coursename']),0,80),0,0,'L');
                    $pdf->Ln();
                    $pdf->Cell(130,5, substr(utf8_decode($row['coursename']),80),0,0,'L');
                    $pdf->Cell(20,5,$row['credits'] ,0,0,'C');

                    // bestanden for passed. The actual grade for grade
                    if ( $row['status'] == "passed" ) $pdf->Cell(20,5,"bestanden",0,0,'C');
                    else $pdf->Cell(20,5,$row['local_grade'],0,0,'C');

                    // ECTS-Grade
                    if ( $row['ects_grade'] == "not set" ) $pdf->Cell(20,5, "--", 0,0,'C');
                    else $pdf->Cell(20,5, $row['ects_grade'], 0,0,'C');

                    $pdf->Ln();
                }else{
                    $pdf->Cell(130,5, utf8_decode($row['coursename']),0,0,'L');
                    $pdf->Cell(20,5,$row['credits'] ,0,0,'C');

                    // bestanden for passed. The actual grade for grade
                    if ( $row['status'] == "passed" ) $pdf->Cell(20,5,"bestanden",0,0,'C');
                    else $pdf->Cell(20,5,$row['local_grade'],0,0,'C');

                    // ECTS-Grade
                    if ( $row['ects_grade'] == "not set" ) $pdf->Cell(20,5, "--", 0,0,'C');
                    else $pdf->Cell(20,5, $row['ects_grade'], 0,0,'C');

                    $pdf->Ln();
                }
		      }
			  elseif($row['modul'] == "Language"){
			  $pdf->rand();
                $pdf->SetFont('Univers57cn','b',$gradeSize);
				$pdf->Cell(130,5,"Language:",0,0,'L');
				$pdf->Ln();
				$query_coursename = "SELECT english_description FROM amase_courses WHERE local_description = '$row[coursename]' GROUP BY english_description";
                $result_coursenames = mysql_query($query_coursename) OR die("Error: could not retrieve grades from database: " . mysql_error());
                while($row2 = mysql_fetch_assoc($result_coursenames)) {
                    $row['coursename'] = $row['coursename']. " - " . $row2['english_description'];
                }
				$pdf->SetFont('Univers57cn','',$gradeSize);
                $pdf->Cell(130,5, utf8_decode($row['coursename']),0,0,'L');
                $pdf->Cell(20,5,$row['credits'] ,0,0,'C');

                // bestanden for passed. The actual grade for grade
                if ( $row['status'] == "passed" ) $pdf->Cell(20,5,"bestanden",0,0,'C');
                else $pdf->Cell(20,5,$row['local_grade'],0,0,'C');

                // ECTS-Grade
                if ( $row['ects_grade'] == "not set" ) $pdf->Cell(20,5, "--", 0,0,'C');
                else $pdf->Cell(20,5, $row['ects_grade'], 0,0,'C');

                $pdf->Ln();
			  
			  }
	
		  
		  
		  }

}
           $query_master_thesis_partner_uni = "SELECT projectname,university,examiner1, examiner2, examiner_grade1, examiner_grade2, credits_master, local_grade_master FROM amase_master WHERE student_id='$id' AND status IN ('grade', 'passed') AND (university='LTU' OR university='UPC' OR university='UL')";
    $result_master_thesis_partner_uni = mysql_query($query_master_thesis_partner_uni) OR die("Error: could not retrieve grades from database: " . mysql_error());


          
    if  ( mysql_num_rows($result_master_thesis_partner_uni) != 0 ) { 
        $row = mysql_fetch_assoc($result_master_thesis_partner_uni);
		$partner_uni_ects += 30.0;
		$pdf->SetFont('Univers57cn','b',$gradeSize);
		$pdf->rand();
		$pdf->Cell(130,5,"Masterarbeit:",0,0,'L');
		$pdf->Ln();
        $pdf->rand();
        $pdf->SetFont('Univers57cn','',$gradeSize);
        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $master['projectname'] = preg_replace("/\r|\n/s", "", $row['projectname']);  // Title
        $pdf->MultiCell(130,5,"Thema: " . utf8_decode($row['projectname']),0,'L');
        $x = $x + 130;
        $y_master=$pdf->GetY(); // Untere Kante des Titels merken
        $pdf->SetXY($x,$y); 
		if ( $row['examiner_grade1'] =="passed" && $row['examiner_grade1'] == "passed" ) { $masterEndnote = "bestanden"; }
                else {
         $masterEndnote = substr((((float)$master['examiner_grade1'] + (float)$master['examiner_grade2']) / 2 ),0,3);
		 }
        $pdf->Cell(20,5,"30",0,0,'C');  // ECTS points for Master Thesis
        $pdf->Cell(20,5,$masterEndnote ,0,0,'C');   // Grade from the 2 examiners for the Master Thesis
        $pdf->SetXY($pdf->GetX(),$y_master); // Zur unteren Kante des Titels springen

		}
	   $actX=$pdf->GetX();
            $actY=$pdf->GetY();
            $pdf->SetXY($modulX, $modulY);	
			$pdf->SetFont('Univers57cn','b',$courseSize);
            $pdf->Cell(20,7, number_format( $partner_uni_ects, 1), 1, 0, 'C');
            $averagestuffshit = number_format(convert_grade( ($module_summe_gradesXEctsGrades/$module_summe_gradedEcts), 'UDS'),1);
			if ($module_summe_gradedEcts != 0.0)  $pdf->Cell(20,7, substr($averagestuffshit , 0, 3),1,0,'C');
            else $pdf->Cell(20,7, "--",1,0,'C');
            $pdf->Cell(20,7, "", 1, 0, 'C');
			 $pdf->SetXY($actX, $actY);
		
}
 else{
    foreach ($parse_modul as $Mcourse) {
        $query_grades = "SELECT status, modul, coursename, credits, MIN(local_grade) as local_grade, MIN(ects_grade) as ects_grade FROM amase_grades WHERE student_id='$id' 
                        AND university='UdS' AND modul='$Mcourse' AND status IN ('grade', 'passed') GROUP BY coursename";
        $result_grades = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());

        if  (mysql_num_rows($result_grades) != 0) { 
            $pdf->rand();
            $pdf->SetFont('Univers57cn','b',$courseSize);
            $pdf->Cell(130,8, $Mcourse,1,0,'L');
            $modulX=$pdf->GetX();
            $modulY=$pdf->GetY();
            $pdf->Ln();			

            while($row = mysql_fetch_assoc($result_grades)) {  // Kurse aus Modul extrahieren
                $row['credits']= ereg_replace(",", ".", $row['credits']);
                $row['local_grade']= ereg_replace(",", ".", $row['local_grade']);

                if (($row['modul'] != "Language") && ($row['status'] == "grade")) { 
                    $ects_sum_ohne_sprache += $row['credits'];
                    $module_summe_gradedEcts += $row['credits'];

                    if (isValidGrade("UdS",(float)$row['local_grade'])) {
                        $summe_note_ects += ( (float)$row['credits']*(float)$row['local_grade'] );
                        $module_summe_gradesXEctsGrades += ( (float)$row['credits']*(float)$row['local_grade'] );
                    } else echo "invalid grade: " . $row['local_grade'] . " - university: ". $row['university'] . "<br>";
                } else if ($row['modul'] == "Language") {
                    $ects_sum_languages += $row['credits'];   
                    if ($ects_sum_languages > 8) {
                        $ects_sum_languages = 8;
                    }
                } else if (($row['modul'] != "Language") && ($row['status'] == "passed")) {
                    $ects_sum_passed += $row['credits']; 
                }

            ###### One line
                $module_ects += (float) $row['credits']; 	// Modulcredits
                //		// Komplettsumme Credits
                $pdf->rand();
                $pdf->SetFont('Univers57cn','',$gradeSize);
                $pdf->Cell(130,5, utf8_decode($row['coursename']),0,0,'L');
                $pdf->Cell(20,5,$row['credits'] ,0,0,'C');

                // bestanden for passed. The actual grade for grade
                if ( $row['status'] == "passed" ) $pdf->Cell(20,5,"bestanden",0,0,'C');
                else $pdf->Cell(20,5,$row['local_grade'],0,0,'C');

                // ECTS-Grade
                if ( $row['ects_grade'] == "not set" ) $pdf->Cell(20,5, "--", 0,0,'C');
                else $pdf->Cell(20,5, $row['ects_grade'], 0,0,'C');

                $pdf->Ln();
            ###### One line				

                $lastmod=$row['modul'];
            }
            $ects_sum = $ects_sum_passed + $ects_sum_ohne_sprache + $ects_sum_languages;
            
        ###### MODULE AVERAGE            
            $actX=$pdf->GetX();
            $actY=$pdf->GetY();
            $pdf->SetXY($modulX, $modulY);
            $pdf->SetFont('Univers57cn','b',$courseSize);
            if ($lastmod != "Language") $pdf->Cell(20,8, number_format( $module_ects, 1), 1, 0, 'C');
            else $pdf->Cell(20,8, number_format( $ects_sum_languages, 1), 1, 0, 'C');
            if ($module_summe_gradedEcts != 0.0) { 
                $average_master = number_format(convert_grade(($module_summe_gradesXEctsGrades/$module_summe_gradedEcts), 'UDS'),1);
                $pdf->Cell(20,8, substr( $average_master, 0, 3),1,0,'C');
            }
            else $pdf->Cell(20,8, "--",1,0,'C');
            $pdf->Cell(20,8, "", 1, 0, 'C');
        ###### MODULE AVERAGE 
            
            $pdf->SetXY($actX, $actY);
            $module_ects = $module_summe_gradedEcts = $module_summe_gradesXEctsGrades = 0.0;
        }
    }
	}
    $ectsSchnitt = substr( ($summe_note_ects / $ects_sum_ohne_sprache), 0, 3);
if(($start_year >= 2011)){
###### MASTER THESIS   
    if (mysql_num_rows($result3) != 0) {
        $pdf->rand();
        $pdf->SetFont('Univers57cn','b',$courseSize);
        $pdf->Cell(130,7, "Masterarbeit - UdS",1,0,'L');
        $pdf->SetFont('Univers57cn','',$gradeSize);
        $pdf->Ln();
        $pdf->rand();

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $master['projectname'] = preg_replace("/\r|\n/s", "", $master['projectname']);  // Title
        $pdf->MultiCell(130,5,"Thema: " . utf8_decode($master['projectname']),0,'L');
        $x = $x + 130;
        $y_master=$pdf->GetY(); // Untere Kante des Titels merken
        $pdf->SetXY($x,$y); 

        $pdf->Cell(20,5,"30",0,0,'C');  // ECTS points for Master Thesis
        $masterEndnote = number_format(convert_grade($masterEndnote, 'UDS'),1);
        $pdf->Cell(20,5,$masterEndnote ,0,0,'C');   // Grade from the 2 examiners for the Master Thesis
        $pdf->SetXY($pdf->GetX(),$y_master); // Zur unteren Kante des Titels springen
       $ects_sum_and_master = $ects_sum + 30;
		}
        // Master Credits dazuzählen
        //$ects_sum_and_master = $ects_sum + 30;
        //$gesamtSchnitt = (float)((($master['credits']*$masterEndnote)+($summe_note_ects)) / ($master['credits']+$ects_sum_ohne_sprache));
###### MASTER THESIS 
      ###### WITHOUT MASTER THESIS 
	  else{
            $ects_sum_and_master = $ects_sum;
			}
            $gesamtSchnitt = $ectsSchnitt;
    
	//if ($pdf->GetY() > 230){  $tplidx = $pdf->ImportPage(2);
    //$pdf->addPage();
    //$pdf->useTemplate($tplidx);
    //$pdf->Ln();
	//$pdf->Ln();
	//$pdf->Ln();
	//}
###### ALL THE ECTS POINTS	
    $pdf->rand();
    $pdf->SetFont('Univers57cn','b',$courseSize);
    $pdf->Cell(130,7, "Summe an der UdS erworbenen ECTS-Punkte:",0,0,'L');
    $pdf->Cell(20,7, number_format($ects_sum_and_master + $zus_leist_ects,1), 0, 0,'C');  // SUMME ECTS Credits + Masterarbeit
    $pdf->Ln();
###### ALL THE ECTS POINTS

###### ALL THE ECTS POINTS	
    $pdf->rand();
    $pdf->SetFont('Univers57cn','b',$courseSize);
    $pdf->Cell(130,7, "Summe an der UdS und ".$Partner_Uni." erworbenen ECTS-Punkte:",0,0,'L');
    $pdf->Cell(20,7, number_format($ects_sum_and_master + $zus_leist_ects + $partner_uni_ects,1), 0, 0,'C');  // SUMME ECTS Credits + Masterarbeit
    $pdf->Ln();
###### ALL THE ECTS POINTS
    
###### AVERAGE CRADE POINT	
    $pdf->rand();
    $x=$pdf->GetX();
    $y=$pdf->GetY();
    $pdf->SetFont('Univers57cn','b',$courseSize);
    $pdf->MultiCell(130,5, "Notendurchschnitt der an der UdS und ".$Partner_Uni." erbrachten Leistungen:", 0, 'L');
    $x = $x + 130;
    $pdf->SetXY($x,$y); 

    $pdf->Cell(20,5, "" ,0,0,'L');
    $pdf->Cell(20,5, substr( $gesamtSchnitt , 0, 3),0,0,'C');
    $pdf->Ln(8);
###### AVERAGE CRADE POINT

	
###### SIMPLE LINEPartn
    $pdf->Line(0+$padding, $pdf->GetY(), 210-$padding, $pdf->GetY() );
    $pdf->SetXY( 0, $pdf->GetY() +1 ); 
###### SIMPLE LINE

###### 1 SENTENCES
    $pdf->Cell(210,5, "Die erforderlichen 120 ECTS-Punkte wurden im Rahmen des Masterstudiums erworben.",0 ,0,'C');
###### 1 SENTENCES
	



}
else{
###### MASTER THESIS   
    if (mysql_num_rows($result3) != 0) {
        $pdf->rand();
        $pdf->SetFont('Univers57cn','b',$courseSize);
        $pdf->Cell(130,8, "Masterarbeit",1,0,'L');
        $pdf->SetFont('Univers57cn','',$gradeSize);
        $pdf->Ln();
        $pdf->rand();

        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $master['projectname'] = preg_replace("/\r|\n/s", "", $master['projectname']);  // Title
        $pdf->MultiCell(130,5,"Thema: " . utf8_decode($master['projectname']),0,'L');
        $x = $x + 130;
        $y_master=$pdf->GetY(); // Untere Kante des Titels merken
        $pdf->SetXY($x,$y); 

        $pdf->Cell(20,5,"30",0,0,'C');  // ECTS points for Master Thesis
        $pdf->Cell(20,5,$masterEndnote ,0,0,'C');   // Grade from the 2 examiners for the Master Thesis
        $pdf->SetXY($pdf->GetX(),$y_master); // Zur unteren Kante des Titels springen

        // Master Credits dazuzählen
        $ects_sum_and_master = $ects_sum + 30;
        $gesamtSchnitt = (float)((($master['credits']*$masterEndnote)+($summe_note_ects)) / ($master['credits']+$ects_sum_ohne_sprache));
###### MASTER THESIS 
    } else { ###### WITHOUT MASTER THESIS 
            $ects_sum_and_master = $ects_sum;
            $gesamtSchnitt = $ectsSchnitt;
    }
	
###### ALL THE ECTS POINTS	
    $pdf->rand();
    $pdf->SetFont('Univers57cn','b',$courseSize);
    $pdf->Cell(130,8, "Summe an der UdS erworbenen ECTS-Punkte:",0,0,'L');
    $pdf->Cell(20,8, number_format($ects_sum_and_master,1), 0, 0,'C');  // SUMME ECTS Credits + Masterarbeit
    $pdf->Ln();
###### ALL THE ECTS POINTS
    
###### AVERAGE CRADE POINT	
    $pdf->rand();
    $x=$pdf->GetX();
    $y=$pdf->GetY();
    $pdf->SetFont('Univers57cn','b',$courseSize);
    $pdf->MultiCell(130,5, "Notendurchschnitt der an der UdS erbrachten Leistungen:", 0, 'L');
    $x = $x + 130;
    $pdf->SetXY($x,$y); 

    $pdf->Cell(20,5, "" ,0,0,'L');
    $pdf->Cell(20,5, substr( $gesamtSchnitt , 0, 3),0,0,'C');
    $pdf->Ln(13);
###### AVERAGE CRADE POINT
	
###### SIMPLE LINE
    $pdf->Line(0+$padding, $pdf->GetY(), 210-$padding, $pdf->GetY() );
    $pdf->SetXY( 0, $pdf->GetY() +1 ); 
###### SIMPLE LINE

###### 2 SENTENCES
    $pdf->Cell(210,5, "Die erforderlichen 120 ECTS-Punkte wurden im Rahmen des Masterstudiums erworben.",0 ,0,'C');
    $pdf->SetXY( 0, $pdf->GetY() + 5 );
    $pdf->Cell(210,5, "Die weiteren Prüfungsergebnisse sind im Zeugnis der Partner-Universität aufgelistet.",0 ,0,'C');
###### 2 SENTENCES
}
    $pdf->Output();
?>