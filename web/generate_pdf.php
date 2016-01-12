<?php

    define('_VALID_INCLUDE', TRUE);

     //ugly fix to get fonts to work again as directory changed to usr share
    $fontprefixfix = "/www/htdocs/w006e175/amase/amasebase2/pdf/font/"; 
    define('FPDF_FONTPATH', $fontprefixfix);

    require "./pdf/fpdi.php";



    require "modul.inc.php";
    require "config.inc.php";
    require "./register/validation.inc.php";

    session_name('amasebase');
    session_start();
    check_login();

    if (!(isset($_GET['studid']) && is_clean_digit($_GET['studid']))) {
        box(false, "Student-ID not set. Please contact administration", false);
        exit;
    }

    if (!(isset($_GET['datid']) && is_clean_digit($_GET['datid']))) {
        box(false, "Data-ID not set. Please contact administration", false, 0);
        exit;
    }
    
####### QUERY
    if (!($db_link)) die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error());  // zum mySQL-Server verbinden

    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error()); // Datenbank auswählen

    $result = mysql_query("SELECT * FROM amase_grades WHERE 1=0") OR die("Tabelle nicht vorhanden - Error #24 - SQL-Message: " . mysql_error()); // Tabelle auf existenz prüfen
            
    $result = mysql_query("SELECT * FROM amase_students WHERE 1=0") OR die("Tabelle nicht vorhanden - Error #24 - SQL-Message: " . mysql_error());
            
    $query = "SELECT * FROM amase_students WHERE id='" . $_GET['studid'] . "'";   //Datensatz auslesen
    $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
    $student = mysql_fetch_assoc($result);

    $query = "SELECT * FROM amase_grades WHERE id='" . $_GET['datid'] . "'";
    $result = mysql_query($query) OR die("SQL Server offline? - Error #25 - SQL-Message: " . mysql_error());
    $grades = mysql_fetch_assoc($result);
####### QUERY
    
    $pdf= new fpdi();
    $fachsemester = diff_semester($student["start_semester"], get_current_semester());
    $versuch_array = array("1st attempt"=>"erste Anmeldung", "2nd attempt"=>"zweite Anmeldung", "3rd attempt"=>"dritte Anmeldung");
    $versuch = $versuch_array[$grades["try"]];

    $pagecount = $pdf->setSourceFile("./pdf/pdfdoc.pdf");
    $tplidx = $pdf->ImportPage(1);

    $pdf->addPage();
    $pdf->useTemplate($tplidx);
    $pdf->AddFont('ArialNarrow','','arialnarrow.php');
    $pdf->AddFont('ArialNarrow','B','arialnarrowb.php');
    //$pdf->AddFont('Helvetica','','helvetica.php');
    //$pdf->AddFont('Helvetica','B','helveticab.php');
    $pdf->SetFont('ArialNarrow','B',12);
    
    static $start_x=30; // vom rand weg , von oben weg
    $start_y=31;

    $pdf->SetXY($start_x,$start_y);
    $pdf->write(12, (utf8_decode($student['vorname']) . " " .  utf8_decode($student['nachname'])));

    $pdf->SetXY($start_x+1,$start_y+7);
    $pdf->write(12,germandate_from_sqldate($student[birth_date]));

    $pdf->SetXY($start_x+44,$start_y+7,5);
    $pdf->write(13,$student['birth_place']);


    if ($student["university1"]== "UdS") $matrikel_nr= $student["matrikel1"];
    elseif ($student["university2"]== "UdS") $matrikel_nr= $student["matrikel2"];	
    else $matrikel_nr= "?";

    $pdf->SetXY($start_x+108,$start_y+7,5);
    $pdf->write(12,$matrikel_nr);

    $pdf->SetXY($start_x+2,$start_y+13);
    $pdf->write(12,"AMASE");

    $pdf->SetXY($start_x-22,$start_y+30);
    $pdf->write(12,utf8_decode($grades['coursename']));

    $pdf->SetFont('ArialNarrow','',10);

    $pdf->SetXY($start_x-10,$start_y+39);
    $pdf->write(12,$versuch);

    $pdf->SetXY($start_x+2,$start_y+44);
    $pdf->write(12,date("d.m.Y"));

    $pdf->SetXY($start_x+99,$start_y+39);
    $pdf->write(12,$fachsemester);

    
    ################### Start of second page:
    $start_y=179;
    $pdf->SetFont('ArialNarrow','B',12);

    $pdf->SetXY($start_x,$start_y);
    $pdf->write(12, (utf8_decode($student['vorname']) . " " . utf8_decode($student['nachname'])));

    $pdf->SetXY($start_x+2,$start_y+7);
    $pdf->write(12,germandate_from_sqldate($student['birth_date']));

    $pdf->SetXY($start_x+44,$start_y+7);
    $pdf->write(14,$student['birth_place']);

    $pdf->SetXY($start_x+108,$start_y+7);
    $pdf->write(12,$matrikel_nr);

    $pdf->SetXY($start_x+3,$start_y+13);
    $pdf->write(12,"AMASE");

    $pdf->SetXY($start_x-22,$start_y+30);
    $pdf->write(12,utf8_decode($grades['coursename']));

    $pdf->SetFont('ArialNarrow','',10);

    $pdf->SetXY($start_x-10,$start_y+39);
    $pdf->write(12,$versuch);

    $pdf->SetXY($start_x+2,$start_y+44);
    $pdf->write(12,date("d.m.Y"));

    $pdf->SetXY($start_x+99,$start_y+39);
    $pdf->write(12,$fachsemester);

    Header('Content-Type: application/pdf');
    $pdf->Output("newpdf.pdf","I");
    $pdf->closeParsers();
?> 