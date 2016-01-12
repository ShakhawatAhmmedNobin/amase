<?php
/*
 * Created on 04.02.2008
 *
 * @author 	$Author: Daniel $ 
 * @version $Revision: 37 $
 * @date 	$Date: 2008-03-16 19:45:45 +0100 (So, 16 Mrz 2008) $
 * @id 		$Id: printcert_uds.php 37 2008-03-16 18:45:45Z Daniel $
 */
 
 	define('_VALID_INCLUDE', TRUE);
 	require('config.inc.php');
	require('modul.inc.php');
	require('./pdf/fpdf.php');	
	
	session_name('amasebase');
	session_start();
	check_login();

 	if ( !isset($_GET['id']) || !is_clean_digit($_GET['id']) ) {
		box(false, "no valid student-ID given", 1, 0);
		echo "</table>\n";
		echo "</body></html>";
		die();
	}
	$id = $_GET['id'];
	
###### QUERY
	if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }
    
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
	$query_stud	 = "SELECT nachname, vorname, gender, birth_date, matrikel1, matrikel2, university1, university2 FROM amase_students WHERE id='$id';";
	// var_dump($query_stud);
    $result_stud = mysql_query($query_stud) OR die("Error: could not load student dataset: " . mysql_error());
	$student_from_db = mysql_fetch_assoc($result_stud);
###### QUERY



//Page header
class PDF extends FPDF
{
	function Header()
	{
		global $student_from_db;
		
		//Logo
	    $this->Image('./icons/uds_head.png', 20, 5, 70);
		$this->Image('./icons/amase_head.png', 110, 8, 80);
		
		// Überschrift
	    $this->SetXY(10, 40);
	    $this->SetFont('Arial','B',18);
		$this->Write(5, 'Studiennachweis');
		
		$this->SetFont('Arial','I',12);
		$datum = 'Saarbrücken, den ' . date("d.m.Y");
		$this->Cell(0,10,$datum,0,1,R);
		
		$this->SetFont('Arial','B',12);
		$geschlecht =  (($student_from_db['gender']=='male') ? "Herr " : "Frau ") . $student_from_db['vorname']. " " . $student_from_db['nachname'] ;
	    $this->Cell(0,5,$geschlecht,0,1);
		
		$this->SetFont('Arial','',12);
		$geburtsDatum = "geb. am: "  .  germandate_from_sqldate($student_from_db['birth_date']);
		$this->Cell(0,5,$geburtsDatum,0,1);
		
		$this->Ln(10);
		
		$matrikel1 = "Matrikelnummer 1: " .$student_from_db['matrikel1']. " (" . $student_from_db['university1']. ")";
		$this->Cell(0,5,$matrikel1,0,1);
		
		$matrikel2=  "Matrikelnummer 2: " .$student_from_db['matrikel2']. " (". $student_from_db['university2']   .")";
		$this->Cell(0,5,$matrikel2,0,1);
		
		//Line break
	    $this->Ln(20);
	}

//Page footer
	function Footer()	{
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function PrintLine() {
		$this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY());
	}
}
//Instanciation of inherited class
$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
//$pdf->PrintLine();

###### QUERY
	$startindex		 		= -1;
	$ects_sum 				= 0.0;
	$ects_sum_ohne_sprache 	= 0.0;
	$summe_note_ects 		= 0.0;
	
	// PDF-Farben festlagen
	$pdf->SetFillColor(224,235,255);
	$pdf->SetTextColor(0);

	$ueberschriftCoursenameFont = 10;
	$ueberschriftEctsCreditsFont = 10;
	$coursenameFont = 9;
	
	// Tabellenkopf setzen
	$pdf->SetFont('Times','B',$ueberschriftCoursenameFont);
	$pdf->Cell(140,7,'Course',1,0,'C');
	$pdf->SetFont('Times','B',$ueberschriftEctsCreditsFont);
	$pdf->Cell(25,7,"ECTS Credits",1,0,'C');
	$pdf->Cell(25,7,"Local Grade",1,0,'C');
	$pdf->Ln();

	// Einzelne Module  mit eigenem Query isolieren
	foreach ($PrintCertCoursesUds as $Mcourse) {
		$fill=1; // Hintergrund fuellen

		$query_grades = "SELECT modul,coursename,credits,local_grade FROM amase_grades WHERE university='UdS' AND student_id='$id' AND modul='$Mcourse' AND status IN ('passed', 'grade')";

		$result_grades = mysql_query($query_grades) OR die("Error: could not retrieve grades from database: " . mysql_error());
		
		if  (mysql_num_rows($result_grades) != 0) { 
		
			// Tabellenkopf setzen
			$pdf->SetFont('Times','B',$ueberschriftCoursenameFont);
			$pdf->Cell(190,7,$Mcourse,1,0,'L');
			$pdf->Ln();

			// Kurse aus Modul extrahieren
			while($row = mysql_fetch_assoc($result_grades)) {
				
				$row['credits']= ereg_replace(",", ".", $row['credits']);
				$row['local_grade']= ereg_replace(",", ".", $row['local_grade']);
				
				$ects_sum = $ects_sum + $row['credits'];

				// Noten ohne Sprachenschein
				if ( ($row['modul'] != "Language") && ($row['local_grade'] != "bestanden") ) { 
					
					$ects_sum_ohne_sprache	+= $row['credits'];
					$summe_note_ects 		+= $row['local_grade'] * $row['credits'];
				} 

				$pdf->SetFont('Times','',$coursenameFont);
				$pdf->Cell(140,5, wordwrap($row['coursename'], 60),1,0,'L',$fill);
				$pdf->Cell(25,5,$row['credits'] . ' ECTS',1,0,'R',$fill);
				$pdf->Cell(25,5,$row['local_grade'],1,0,'R',$fill);
				$pdf->Ln();
				
				$fill = !$fill;
				$lastmod=$row['modul'];
			}
		}
	}

	// Fuellfarbe für den Tabellenhintergrund setzen
	$pdf->Ln(10);
	$pdf->SetFont('Times','',$ueberschriftCoursenameFont);
	$pdf->Cell(140,5, "Total credit points (UdS): ",0,0,'R');
	$pdf->SetFont('Times','BU',$ueberschriftCoursenameFont);
	$pdf->Cell(25,5,$ects_sum . ' ECTS','',0,'R');
	$pdf->Ln();
/*	
	$pdf->Cell(140,5, "credit points without language (UdS): ",0,0,'R');
	$pdf->Cell(25,5,$ects_sum_ohne_sprache . ' ECTS','',0,'R');
	$pdf->Ln();
	
	$pdf->Cell(140,5, "summe(note*ects): ",0,0,'R');
	$pdf->Cell(25,5,$summe_note_ects ,'',0,'R');
	$pdf->Ln();
*/

	$pdf->SetFont('Times','',$ueberschriftCoursenameFont);
	$pdf->Cell(140,5, "Avg. Grade (UdS): ",0,0,'R');
	$pdf->SetFont('Times','BU',$ueberschriftCoursenameFont);
	if ($ects_sum_ohne_sprache == 0) {
		$pdf->Cell(25,5, 'none',2,'',0,'R');
	} else {
		$pdf->Cell(25,5, round(($summe_note_ects / $ects_sum_ohne_sprache),2),'',0,'R');
	}
	$pdf->Ln();	
	
$pdf->Output();


?>