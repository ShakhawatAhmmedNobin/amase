<?php

    define('_VALID_INCLUDE', TRUE);

    $fontprefixfix = "/www/htdocs/w006e175/amase/amasebase2/pdf/font/"; 
    define('FPDF_FONTPATH', $fontprefixfix);

    require('config.inc.php');
    require('modul.inc.php');
    require "./pdf/fpdi.php";
    require('grades.inc.php');

    session_name('amasebase');
    session_start();
    check_login();


######## DEFINES
    $_unistatus1 = "Universität des ";
    $_unistatus12 = "Saarlandes (UdS) ";
	$_unistatus13 = "University / ";
	$_unistatus14 = "State Institution";
    $_unistatus2 = "Luleå tekniska ";
    $_unistatus22 = "universitet (LTU) ";
	$_unistatus23 = "University / ";
	$_unistatus24 = "State Institution";
    $_unistatus3 = "Universitat Politècnica ";
    $_unistatus32 = "de Catalunya (UPC) ";
	$_unistatus33 = "University / ";
	$_unistatus34 = "State Institution";
    $_unistatus4 = "Université de ";
    $_unistatus42 = "Lorraine (UL) ";
	$_unistatus43 = "Grande Ecole / ";
	$_unistatus44 = "State Institution";

    $_uniname1 = "Universität des ";
    $_uniname12 = "Saarlandes";
    $_uniname2 = "Luleå tekniska ";
    $_uniname22 = "universitet";
    $_uniname3 = "Universitat Politèc";
    $_uniname32 = "nica de Catalunya";
    $_uniname4 = "Université de ";
    $_uniname42 = "Lorraine";

    $_unicountry1= "Germany";
    $_unicountry2= "Sweden";
    $_unicountry3= "Spain";
    $_unicountry4= "France";

    $_unilang1= "German";
    $_unilang2= "English";
    $_unilang3= "Spanish";
    $_unilang4= "French";
######## DEFINES

    if ( !isset($_GET['id']) || !is_clean_digit($_GET['id']) ) {
            box(false, "no valid student-ID given", 1, 0);
            echo "</table>\n";
            echo "</body></html>";
            die();
    } else $id = $_GET['id'];

    if ( !isset($_POST['fourthuni'])) {
            echo '<html>
<head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
</head>
<body align="center">
    <form class="form-horizontal" action="supplement.php?id='.$id.'" method="post">
        <fieldset>

            <!-- Form Name -->
            <legend>Diploma Supplement Generator</legend>

            <!-- Select Basic -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="fourthuni">Viertes Uni Jahr</label>
              <div class="col-md-4">
                <select id="fourthuni" name="fourthuni" class="form-control">
                  <option value="1">UDS</option>
                  <option value="2">LTU</option>
                  <option value="3">UPC</option>
                  <option value="4">UL</option>
                </select>
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="issuedate">Ausstelldatum</label>  
              <div class="col-md-4">
              <input id="issuedate" name="issuedate" type="text" placeholder="today" class="form-control input-md" required="">
              <span class="help-block">help</span>  
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line1"></label>  
              <div class="col-md-4">
              <input id="line1" name="line1" type="text" placeholder="" class="form-control input-md">
              <span class="help-block">Zeile 1</span>  
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line2">Master Urkunde / Diploma UdS - </label>  
              <div class="col-md-4">
              <input id="line2" name="line2" type="text" placeholder="(03.09.2015)" class="form-control input-md">
              <span class="help-block">Zeile 2</span>  
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line3">Zeugnis / Official Transcript UdS - </label>  
              <div class="col-md-4">
              <input id="line3" name="line3" type="text" placeholder=" (03.09.2015)" class="form-control input-md">
              <span class="help-block">Zeile 3</span>  
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line4">Transcript of Records UdS - </label>  
              <div class="col-md-4">
              <input id="line4" name="line4" type="text" placeholder="(February 21, 2015)" class="form-control input-md">
              <span class="help-block">Zeile 4</span>  
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line5">Master’s Degree Certificate UdS - </label>  
              <div class="col-md-4">
              <input id="line5" name="line5" type="text" placeholder="(February 21, 2015)" class="form-control input-md">
              <span class="help-block">Zeile 5</span>  
              </div>
            </div>

            <!-- Select Basic -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line6choice">Hier wählen</label>
              <div class="col-md-4">
                <select id="line6choice" name="line6choice" class="form-control">
                  <option value="Examensbevis - LTU">Examensbevis - LTU</option>
                  <option value="Expedición de titulos académicos - UPC">Expedición de titulos académicos - UPC</option>
                  <option value="Attestation de réussite au diplome - UL">Attestation de réussite au diplome - UL</option>
                </select>
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line6">Wahl oben</label>  
              <div class="col-md-4">
              <input id="line6" name="line6" type="text" placeholder="(12th d’octubre de 2014)" class="form-control input-md">
              <span class="help-block">Zeile 6</span>  
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="line7"></label>  
              <div class="col-md-4">
              <input id="line7" name="line7" type="text" placeholder="" class="form-control input-md">
              <span class="help-block">Zeile 7</span>  
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="signdate">Zertifizierungsdatum</label>  
              <div class="col-md-4">
              <input id="signdate" name="signdate" type="text" placeholder="now" class="form-control input-md">
              <span class="help-block">help</span>  
              </div>
            </div>

            <!-- Button -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="submit">Abschicken</label>
              <div class="col-md-4">
                <button id="submit" name="submit" type="submit" class="btn btn-success">Abschicken</button>
            </div>

        </fieldset>
    </form>

</body>
</html>
            ';
            die();
    } else {
        $fourthuni = $_POST['fourthuni'];
        $issuedate = $_POST['issuedate'];
        $line1 = $_POST['line1'];
        $line2 = $_POST['line2'];
        $line3 = $_POST['line3'];
        $line4 = $_POST['line4'];
        $line5 = $_POST['line5'];
        $line6choice = $_POST['line6choice'];
        $line6 = $_POST['line6'];
        $line7 = $_POST['line7'];
        $signdate = $_POST['signdate'];
    }

###### QUERY
    if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ". mysql_error()); }
    mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
    $query = "SELECT nachname, vorname, gender, birth_date, birth_place, nationality, university1, university2, matrikel1, matrikel2 FROM amase_students WHERE id='$id';";
    $result = mysql_query($query) OR die("Error: could not load student dataset: " . mysql_error());
    $student = mysql_fetch_assoc($result);    
###### QUERY

    list($year, $month, $day) = split('-', $student['birth_date']); // Geburtsdatum
    $student['birth_date'] = date("d.m.Y", mktime(0, 0, 0, $month, $day, $year));
    
    // Universität Volle Bezeichnung
    if ( $student['university1'] == 'UdS') {
            $uni1status	= $_unistatus1;
            $uni1status2 = $_unistatus12;
			$uni1status3 = $_unistatus13;
			$uni1status4 = $_unistatus14;
            $uni1name = $_uniname1;
            $uni1name2 = $_uniname12;
            $uni1lang = $_unilang1;
            $uni1country = $_unicountry1;
    } elseif ( $student['university1'] == 'LTU') {
            $uni1status	= $_unistatus2;
            $uni1status2 = $_unistatus22;
			$uni1status3 = $_unistatus23;
			$uni1status4 = $_unistatus24;
            $uni1name = $_uniname2;
            $uni1name2 = $_uniname22;
            $uni1lang = $_unilang2;
            $uni1country = $_unicountry2;
    } elseif ($student['university1'] == 'UPC') {
            $uni1status	= $_unistatus3;
            $uni1status2 = $_unistatus32;
			$uni1status3 = $_unistatus33;
			$uni1status4 = $_unistatus34;
            $uni1name = $_uniname3;
            $uni1name2 = $_uniname32;
            $uni1lang = $_unilang3;
            $uni1country = $_unicountry3;
    } elseif ($student['university1'] == 'UL') {
            $uni1status	= $_unistatus4;
            $uni1status2 = $_unistatus42;
			$uni1status3 = $_unistatus43;
			$uni1status4 = $_unistatus44;
            $uni1name = $_uniname4;
            $uni1name2 = $_uniname42;
            $uni1lang = $_unilang4;
            $uni1country = $_unicountry4;
    } else die ('No known University. Contact the administrator. Errorcode: supplement_E001');

    if ( $student['university2'] == 'UdS') {
            $uni2status	= $_unistatus1;
            $uni2status2 = $_unistatus12;
			$uni2status3 = $_unistatus13;
			$uni2status4 = $_unistatus14;
            $uni2name	= $_uniname1;
            $uni2name2 = $_uniname12;
            $uni2lang	= $_unilang1;
            $uni2country= $_unicountry1;
    } elseif ($student['university2'] == 'LTU') {
            $uni2status	= $_unistatus2;
            $uni2status2 = $_unistatus22;
			$uni2status3 = $_unistatus23;
			$uni2status4 = $_unistatus24;
            $uni2name	= $_uniname2;
            $uni2name2 = $_uniname22;
            $uni2lang	= $_unilang2;
            $uni2country= $_unicountry2;
    } elseif ($student['university2'] == 'UPC') {
            $uni2status	= $_unistatus3;
            $uni2status2 = $_unistatus32;
			$uni2status3 = $_unistatus33;
			$uni2status4 = $_unistatus34;
            $uni2name	= $_uniname3;
            $uni2name2 = $_uniname32;
            $uni2lang	= $_unilang3;
            $uni2country	= $_unicountry3;
    } elseif ($student['university2'] == 'UL') {
            $uni2status	= $_unistatus4;
            $uni2status2 = $_unistatus42;
			$uni2status3 = $_unistatus43;
			$uni2status4 = $_unistatus44;
            $uni2name	= $_uniname4;
            $uni2name2 = $_uniname42;
            $uni2lang	= $_unilang4;
            $uni2country= $_unicountry4;
    } else die ('No known University. Contact the administrator. Errorcode: supplement_E002');

    if ( $fourthuni == 1) {
            $uni3status = $_unistatus1;
            $uni3status2 = $_unistatus12;
            $uni3status3 = $_unistatus13;
            $uni3status4 = $_unistatus14;
            $uni3name   = $_uniname1;
            $uni3name2 = $_uniname12;
            $uni3lang   = $_unilang1;
            $uni3country= $_unicountry1;
    } elseif ($fourthuni == 2) {
            $uni3status = $_unistatus2;
            $uni3status2 = $_unistatus22;
            $uni3status3 = $_unistatus23;
            $uni3status4 = $_unistatus24;
            $uni3name   = $_uniname2;
            $uni3name2 = $_uniname22;
            $uni3lang   = $_unilang2;
            $uni3country= $_unicountry2;
    } elseif ($fourthuni == 3) {
            $uni3status = $_unistatus3;
            $uni3status2 = $_unistatus32;
            $uni3status3 = $_unistatus33;
            $uni3status4 = $_unistatus34;
            $uni3name   = $_uniname3;
            $uni3name2 = $_uniname32;
            $uni3lang   = $_unilang3;
            $uni3country    = $_unicountry3;
    } elseif ($fourthuni == 4) {
            $uni3status = $_unistatus4;
            $uni3status2 = $_unistatus42;
            $uni3status3 = $_unistatus43;
            $uni3status4 = $_unistatus44;
            $uni3name   = $_uniname4;
            $uni3name2 = $_uniname42;
            $uni3lang   = $_unilang4;
            $uni3country= $_unicountry4;
    } else die ('No known University. Contact the administrator. Errorcode: supplement_E003');



class fpdf_amase extends fpdi {
            function rand() {
                    $this->SetXY(26, $this->GetY() );   // SETS THE X AND Y OF THE THE SECOND PAGE
            }
    }

$pdf= new fpdf_amase();

$pagecount = $pdf->setSourceFile("./pdf/Diploma Supplement4.pdf");

$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
$pdf->useTemplate($tplidx);
$pdf->AddFont('Univers55','','univers55.php');
$pdf->AddFont('Univers55','B','univers55b.php');
$pdf->AddFont('Univers57cn','','univers57cn.php');
$pdf->AddFont('Univers57cn','B','univers57cnb.php');

$pdf->SetFont('Univers55','B',9);

$studFullName = " " . utf8_decode($student['vorname']) . " " . utf8_decode($student['nachname']) . 
	" has followed the academic path outlined below:";
$nameLen    =  $pdf->GetStringWidth($studFullName);

$pdf->SetXY(45, 94); 
$pdf->Write(10, "Family Name: ".$student['vorname']);

$pdf->SetXY(45, 106); 
$pdf->Write(10, "Given Name: ".$student['nachname']);

$pdf->SetXY(45, 118); 
$pdf->Write(10, "Date of Birth (day/month/year): ".$student['birth_date']);

$pdf->SetXY(45, 124); 
$pdf->Write(10, "Place of Birth: ".$student['birth_place']);

$pdf->SetXY(45, 131); 
$pdf->Write(10, "Nationality: ".$student['nationality']);

$pdf->SetXY(89, 143); 
$pdf->Write(10, $student['university1'].": ".$student['matrikel1']);

$pdf->SetXY(90, 149); 
$pdf->Write(10, $student['university2'].": ".$student['matrikel2']);

$pdf->SetXY(
	/*112-$nameLen*/ 32, 157); 
$pdf->Write(10, $studFullName);


$pdf->SetFont('Univers55','',9);
for ($i=0; $i < 2; $i++) { 
    $offset = $i*16+5;
    $pdf->SetXY(62, 176+$offset); 
    $pdf->Write(10, $uni1name);
    $pdf->SetXY(62, 180+$offset); 
    $pdf->Write(10, $uni1name2);
	
    $pdf->SetXY(95, 173+$offset); 
    $pdf->Write(10, $uni1status);
    $pdf->SetXY(95, 176+$offset); 
    $pdf->Write(10, $uni1status2);
	$pdf->SetXY(95, 179+$offset); 
    $pdf->Write(10, $uni1status3);
    $pdf->SetXY(95, 182+$offset); 
    $pdf->Write(10, $uni1status4);
	
    $pdf->SetXY(140, 178+$offset); 
    $pdf->Write(10, $uni1country);
    $pdf->SetXY(165, 178+$offset); 
    $pdf->Write(10, $uni1lang);
}


    $offset = 0*22+5;
    $pdf->SetXY(62, 211+$offset); 
    $pdf->Write(10, $uni2name);
    $pdf->SetXY(62, 215+$offset); 
    $pdf->Write(10, $uni2name2);
	
    $pdf->SetXY(95, 206+$offset); 
    $pdf->Write(10, $uni2status);
    $pdf->SetXY(95, 209+$offset); 
    $pdf->Write(10, $uni2status2);
	$pdf->SetXY(95, 212+$offset); 
    $pdf->Write(10, $uni2status3);
    $pdf->SetXY(95, 215+$offset); 
    $pdf->Write(10, $uni2status4);
	
    $pdf->SetXY(140, 212+$offset); 
    $pdf->Write(10, $uni2country);
    $pdf->SetXY(165, 212+$offset); 
    $pdf->Write(10, $uni2lang);


    $offset = 1*22+5;
    $pdf->SetXY(62, 211+$offset); 
    $pdf->Write(10, $uni3name);
    $pdf->SetXY(62, 215+$offset); 
    $pdf->Write(10, $uni3name2);
    
    $pdf->SetXY(95, 206+$offset); 
    $pdf->Write(10, $uni3status);
    $pdf->SetXY(95, 209+$offset); 
    $pdf->Write(10, $uni3status2);
    $pdf->SetXY(95, 212+$offset); 
    $pdf->Write(10, $uni3status3);
    $pdf->SetXY(95, 215+$offset); 
    $pdf->Write(10, $uni3status4);
    
    $pdf->SetXY(140, 212+$offset); 
    $pdf->Write(10, $uni3country);
    $pdf->SetXY(165, 212+$offset); 
    $pdf->Write(10, $uni3lang);

    $tplidx = $pdf->ImportPage(2);
    $pdf->addPage();
    $pdf->useTemplate($tplidx);

    $pdf->SetFont('Univers55','B',10);

    $pdf->SetXY(33, 48); 
    $pdf->Write(10, $issuedate);

for ($i=3; $i < 6; $i++) { 
    $tplidx = $pdf->ImportPage($i);
    $pdf->addPage();
    $pdf->useTemplate($tplidx);
}

    $tplidx = $pdf->ImportPage(6);
    $pdf->addPage();
    $pdf->useTemplate($tplidx);

    $pdf->SetFont('Univers55','',8);

    $pdf->SetXY(45, 70); 
    $pdf->Write(10, $line1);

    $pdf->SetXY(45, 73); 
    $pdf->Write(10, "Master Urkunde / Diploma UdS - ".$line2);

    $pdf->SetXY(45, 76); 
    $pdf->Write(10, "Zeugnis / Official Transcript UdS - ".$line3);

    $pdf->SetXY(45, 79); 
    $pdf->Write(10, "Transcript of Records UdS - ".$line4);

    $pdf->SetXY(45, 82); 
    $pdf->Write(10, "Master’s Degree Certificate UdS - ".$line5);

    $pdf->SetXY(45, 85); 
    $pdf->Write(10, $line6choice." - ".$line6);

    $pdf->SetXY(45, 88); 
    $pdf->Write(10, $line7);

    $pdf->SetFont('Univers55','B',10);

    $pdf->SetXY(48, 105); 
    $pdf->Write(10, $signdate);

for ($i=7; $i < 9; $i++) { 
    $tplidx = $pdf->ImportPage($i);
    $pdf->addPage();
    $pdf->useTemplate($tplidx);
}

$pdf->Output();


?>