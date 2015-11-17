<?php
    define('_VALID_INCLUDE', TRUE); // Include Berechtigung setzen
    
    header('Content-type: text/html; charset=UTF-8 '); // Header mit Zeichencodierung senden
	
    // config Daten
    require "config.inc.php";
    require "modul.inc.php";
    require "register/validation.inc.php";
  
    static $debug_mode = false; // Debug Modus
    
    error_reporting(0); // to avoid exposing the warning about division by zero.

    // Session beginnen
    session_name('amasebase');
    session_start();

    // Einige Variablen belegen
    $hostname = $_SERVER['https_HOST'];
    $autor_highlight = "";
    
    html_head(false);   // HTML HEADER SCHREIBEN
    
// Now present the header and its contents:
?>    
    <body>
    <div id ="topBox">
        <div id ="loginForm">
            <?php show_login(); ?>
        </div>
    </div>
    <div id ="secondColumn">
        <div id="logo">
            <?php show_logo(); ?>
        </div>
    </div>
    <div id="thirdColumn">
        <div id="navi">
            <?php
                foreach($template_names as $i=>$TemplateItem) {
                    $link= " <a href=\"" . "https://amase-master.net/amasebase2/" . "?template=" . $i . "\">" . $TemplateItem. "</a>";
                    if (($i == $template) && (str_replace(".php","",basename($_SERVER['REQUEST_URI'])) != "statistics")) $link = " <b>" . " <a class=\"active\" href=\"" . "https://amase-master.net/amasebase2/" . "?template=" . $i . "\">" . $TemplateItem. "</a>" . " </b>"; 	
                    echo $link . "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                } if (str_replace(".php","",basename($_SERVER['REQUEST_URI'])) == "statistics") echo "<b><a class=\"active\" href=\"statistics.php\">Statistics</a></b>";
                  else echo "<a href=\"statistics.php\">Statistics</a>";
            ?>
        </div>
    </div>
        
<?php 

    # Connect to the database
    if (!($db_link)) die ("Could not connect to database - Error #21 - SQL-Message: ". mysql_error());
    mysql_select_db($datenbank) OR die("Database does not exist or is unreachable - Error #22 - SQL-Message: " . mysql_error());
    # Connect to the database
    
    # Computations of how many students study on each university and with what choice. First or second university
    echo "<table width=\"300\" border=\"1\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"tabellen_layout\" style=\"margin: 15px auto 30px auto; clear: both; \">\n";
    echo "<tr class='tableHeader'><th>University</th><th>First choice</th><th>Second choice</th></tr>";
    foreach ($universities as $university) {
        $row = array();
        for ($i=1; $i<=2; $i++) {
            $query_count_students = "SELECT COUNT(id) AS count
                                 FROM amase_statistics 
                                 WHERE university = " . $university[1] . " 
                                 AND choice = " . $i . "";
            $result_for_universities_count = mysql_query($query_count_students) OR die("statistics.php - Error 2: " . mysql_error());
            
            $row[$i] = mysql_fetch_assoc($result_for_universities_count);
           

        } 
        echo "<tr><td>$university[0]</td><td>" . $row[1][count] . "</td><td>" . $row[2][count] . "</td></tr>";
    }
    echo "</table>";
    # Computations of how many students study on each university and with what choice. First or second university
    
        
    foreach ($universities as $university) {
        echo "<table width=\"300\" border=\"1\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"tabellen_layout\" style=\"float: left; margin: 0 10px;\">\n";
        echo "<tr class='tableHeader' ><th>$university[0]</th><th></th><th></th></tr>";
        echo "<tr><th>Name</th><th>Average</th><th>Choice</th></tr>";
    
        $query_for_uds = "SELECT lastname, firstname, average_grade, choice 
                          FROM amase_statistics 
                          WHERE university = " . $university[1] ." 
                          ORDER BY average_grade " . $university[2] . "";
        $result_for_uds = mysql_query($query_for_uds) OR die("statistics.php - Error 1: " . mysql_error());
        
        while($row = mysql_fetch_assoc($result_for_uds)) {
            echo "<tr><td>".utf8_encode($row[lastname]) . " " . utf8_encode($row[firstname])."</td><td>$row[average_grade]</td><td>$row[choice]</td></tr>";
                
        }
    
        echo "</table>";
    }
    
          echo "<br/><br/>";
