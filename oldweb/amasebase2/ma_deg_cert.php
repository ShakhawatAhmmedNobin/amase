<?php

define('_VALID_INCLUDE', TRUE);

//ugly fix to get fonts to work again as directory changed to usr share
    $fontprefixfix = "/www/htdocs/w006e175/amase/amasebase2/pdf/font/"; 
    define('FPDF_FONTPATH', $fontprefixfix);

require('config.inc.php');
require('modul.inc.php');
require "./pdf/fpdi.php";
require('grades.inc.php');

function total_factor2ects ($factor) {
	
	if ( ($factor >= 1) && ($factor < 1.5) ) {
		return 'E';
	} elseif ( ($factor >= 1.5) && ($factor < 2.5) ) {
		return 'D';
	} elseif ( ($factor >= 2.5) && ($factor < 3.5) ) {
		return 'C';
	} elseif ( ($factor >= 3.5) && ($factor < 4.5) ) {
		return 'B';
	} elseif ( ($factor >= 4.5) && ($factor <= 5.0) ) {		
		return 'A';
	} else {
		die("Grade-Factor not valid: " . $factor);
	}
}

session_name('amasebase');
session_start();
check_login();

// $id = 28; // debug //
// $_GET['id'] = 28;  // debug //

if ( !isset($_GET['id']) || !is_clean_digit($_GET['id']) ) {
	box(false, "no valid student-ID given", 1, 0);
	echo "</table>\n";
	echo "</body></html>";
	die();
} else {
	$id = $_GET['id'];
}

###### QUERY
if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }
mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
$query	 = "SELECT nachname, vorname, gender, birth_date, birth_place, country_birth, university1, university2 FROM amase_students WHERE id='$id';";
$result = mysql_query($query) OR die("Error: could not load student dataset: " . mysql_error());
$student = mysql_fetch_assoc($result);
###### QUERY


######################################################################################### REPLACEMENTS
// University
$get_full_uni = array('UdS' => 'Saarland University, Germany', 'UL' => 'University of Lorraine', 'LTU' => 'Luleå University of Technology, Sweden', 'UPC'=>'Technical University of Catalonia, Spain');

if ( array_key_exists($student['university1'], $get_full_uni) && array_key_exists($student['university2'], $get_full_uni) ) {
	$student['university1'] = $get_full_uni[$student['university1']];
	$student['university2'] = $get_full_uni[$student['university2']];
} else {
	die("unknown university selected");
}

// Gender
$get_gender = array('male' => 'Mr.', 'female' => 'Ms.');
if ( array_key_exists($student['gender'], $get_gender) ) {
	$student['gender'] = $get_gender[$student['gender']];
} else {
	die("unknown gender");
}

// Birthday
list($year, $month, $day) = split('-', $student['birth_date']);
$student['birth_date'] = date("F j, Y", mktime(0, 0, 0, $month, $day, $year));
######################################################################################### REPLACEMENTS

// var_dump($student);

###### QUERY
if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }

mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
$query2	 = "SELECT * FROM amase_grades WHERE student_id='$id' AND modul NOT IN ('Language') AND status='grade' AND ects_grade IN ('A', 'B', 'C', 'D', 'E');";
$result2 = mysql_query($query2) OR die("Error: could not load students grades: " . mysql_error());
###### QUERY


###################################################################################################### COMPUTING
$ects_to_factor = array('A' => 5, 'B'=>4, 'C'=>3, 'D'=>2, 'E'=>1);
$credits = 0.0;
$total_factor = 0;

while( $row = mysql_fetch_assoc($result2) ) { 
	$credits += $row['credits'];
	$total_factor += $ects_to_factor[$row['ects_grade']]*$row['credits'] ;
	// var_dump($row);
}

// echo "total credits: $credits<br>";
// echo "total factor: $total_factor<br>";
// echo "total factor / total credits: " . $total_factor/$credits . "<br>";
if ($credits != 0) {
	$total_ects_grade = total_factor2ects($total_factor/$credits);
} else {
	die("Division by zero. 0 credit points.");
}
###################################################################################################### COMPUTING

###### QUERY
if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }

mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
$query3	 = "SELECT projectname FROM amase_master WHERE student_id='$id' AND status='grade';";
$result3 = mysql_query($query3) OR die("Error: could not load students grades: " . mysql_error());
$master = mysql_fetch_assoc($result3);
###### QUERY

###### QUERY
if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }

mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
$query4	 = "SELECT max(ex_date) as datum FROM amase_grades WHERE student_id='$id' AND status IN('grade','passed');";
$result4 = mysql_query($query4) OR die("Error: could not load students grades: " . mysql_error());
$latest_date1 = mysql_fetch_assoc($result4);

$query5	 = "SELECT `ex_date` as datum FROM amase_master WHERE student_id='$id' AND status IN('grade','passed');";
$result5 = mysql_query($query5) OR die("Error: could not load students grades: " . mysql_error());

// aktuelleres Datum ermitteln
if  (mysql_num_rows($result5) != 0) {
	$latest_date2 = mysql_fetch_assoc($result5);
	// var_dump("datum der masterarbeit vorhanden: " . $latest_date2['datum']);
	if ($latest_date1['datum'] > $latest_date2['datum']) {
		// var_dump("datum exams bigger: " . $latest_date1['datum']);
		list($year1, $month1, $day1) = split('-', $latest_date1['datum']);
		$latest_date = date("F j, Y", mktime(0, 0, 0, $month1, $day1, $year1));
	} else {
		list($year2, $month2, $day2) = split('-', $latest_date2['datum']);
		$latest_date = date("F j, Y", mktime(0, 0, 0, $month2, $day2, $year2));
		// var_dump("datum master-project bigger: " . $latest_date2['datum']);
	}

} else {
	// var_dump("datum masterarbeit nicht vorhanden");
	list($year1, $month1, $day1) = split('-', $latest_date1['datum']);
	$latest_date = date("F j, Y", mktime(0, 0, 0, $month1, $day1, $year1));
}

###### QUERY

//Instanciation of inherited class
$pdf= new fpdi('P','mm','Letter');

$pagecount = $pdf->setSourceFile("./pdf/ma_deg_cert_blanco.pdf");
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
$pdf->useTemplate($tplidx);
$pdf->AddFont('Arialnarrow','','arialnarrow.php');
$pdf->AddFont('Arialblack','','arialblack.php');
$pdf->AddFont('Arialnarrow','B','arialnarrowb.php');

// Name
$pdf->SetXY(0, 83); 
$pdf->SetFont('Arialblack','',18);
$pdf->SetTextColor(0, 51, 136);
$pdf->Cell(210,12,$student['gender'] ." ".  utf8_decode($student['vorname']) ." ". utf8_decode($student['nachname']) ,0,0,'C');
$pdf->SetTextColor(0);

// Geburtsdaten
$pdf->SetXY(0, 95); 
$pdf->SetFont('Arialnarrow','',12);
$pdf->MultiCell(210,5,"born on " . $student['birth_date'] . "\n" .  "in " .  utf8_decode($student['birth_place']) . ", " .  $student['country_birth'],0,'C');

// The 2 universities
$pdf->SetXY(0, 135); 
$pdf->SetFont('Arialnarrow','',12);
$pdf->Cell(210,5,"at the " . $student['university1'] . " and",0,1,'C');
$pdf->SetXY(0, $pdf->GetY());
$pdf->Cell(210,5,"and the " . $student['university2'],0,0,'C');

// Grade Intro
$pdf->SetXY(0, 149); 
$pdf->Cell(126,5,"with the overall ECTS-Grade:",0,0, 'R');  

// Grade
$pdf->SetFont('Arialnarrow','B',16);
$pdf->Cell(30,5,$total_ects_grade,0,1,'L'); 
$pdf->SetXY(0, $pdf->GetY());

// Letztes Prüfungsdatum
$pdf->SetFont('Arialnarrow','',12);
$pdf->Cell(210,5,"Date of the last examination: " . $latest_date,0,0, 'C');

// Master Thesis
$pdf->SetXY(30, 180); 
$pdf->SetFont('Arialnarrow','',12);

$pdf->SetTextColor(0, 51, 136);
$pdf->MultiCell(150,5,"Topic: ". utf8_decode($master['projectname']),0,'C');
$pdf->SetTextColor(0);

// Datum & Unterschrift
$pdf->SetXY(0, 227); 
$pdf->SetFont('Arialnarrow','',10);
$pdf->Cell(69,4,"Saarbrücken, " . date("F j, Y"),0,0, 'R');  
$pdf->Cell(72,4,"",0,0, 'C');
$pdf->Cell(60,4,"Prof. Dr.-Ing. F. Mücklich",0,0, 'L');
$pdf->Ln();
$pdf->SetXY(0, $pdf->GetY());
$pdf->SetFont('Arialnarrow','',8);
$pdf->Cell(141,4,"",0,0, 'R');
$pdf->Cell(41,4,"Coordinator of the Master Programme",0,0, 'L');



$pdf->Output();

/*
Header('Content-Type: application/pdf');
$pdf->Output("newpdf.pdf","I");
$pdf->closeParsers();
*/



?>