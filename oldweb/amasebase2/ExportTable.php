<?php

    define('_VALID_INCLUDE', TRUE); // Include Berechtigung setzen
    
    header('Content-type: text/html; charset=UTF-8 '); // Header mit Zeichencodierung senden
	
    require "config.inc.php";
    require "modul.inc.php";
    require "register/validation.inc.php";
  
    static $debug_mode = false; // Debug Modus

    session_name('amasebase');  // Session beginnen
    session_start();


    $csv_text = $_POST['csv_text'];
    $file = $_POST['csv_file_name'];
    
    $CSV_DEL = "CSV_DEL";
    $CSV_SEP = "CSV_SEP";
    $CSV_LINE = "CSV_LINE";
    $csv_text = str_replace("\"", "", $csv_text);
    $csv_text = str_replace("$CSV_SEP", ",", $csv_text);
    $csv_text = str_replace("$CSV_LINE", "\r\n", $csv_text);
    $csv_text = str_replace("$CSV_DEL", "\"", $csv_text);

    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Type: application/x-download');
    header('Content-Length: '.strlen($csv_text));
    header('Content-Disposition: attachment; filename="'.$file.'"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo $csv_text;
?>