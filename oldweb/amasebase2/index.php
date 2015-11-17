<?php
    error_reporting(E_ALL);    // Debugging Modus
    // error_reporting(E_ALL ^ E_NOTICE);   // Runtime Modus

    define('_VALID_INCLUDE', TRUE); // Include Berechtigung setzen
    
	require "OutputFunctions.php";

    header('Content-Type:text/html; charset=UTF-8'); // Header mit Zeichencodierung senden
	
    // config Daten
    require "config.inc.php";
    require "modul.inc.php";
    require "register/validation.inc.php";
  
    static $debug_mode = false; // Debug Modus

    // Session beginnen
    session_name('amasebase');
    session_start();

    // Einige Variablen belegen
    $hostname = $_SERVER['HTTP_HOST'];
    $autor_highlight = "";

    // Welches Template soll geladen werden?
    if ( isset($_GET['template']) && (is_clean_digit($_GET['template'])) ) $template = $_GET['template'];
    else $template = 0;
    // Query setzen
    $query .= $tabellen[$template];
	
    // Datenbankverbindung aufbauen
    // zum mySQL-Server verbinden
    if (!($db_link)) die ("Could not connect to database - Error #21 - SQL-Message: ". mysql_error());

    // Datenbank auswählen
    mysql_select_db($datenbank) OR die("Database does not exist or is unreachable - Error #22 - SQL-Message: " . mysql_error());
    // Tabelle auf existenz prüfen & Anzahl der Datensätze ermitteln
    $result = mysql_query("SELECT * FROM $tabellen[$template]") OR die("table not existing - Error #24 - SQL-Message: " . mysql_error());
    $num_rows_default = mysql_num_rows($result); 	   
    
    // Überprüfe ob user und passwort ok sind
    // Username und Passwort gesetzt? Wenn ja überprüfe auf Konsistenz. Wenn nicht Clean setze user + pass leeren string. wird unten wichtig
    if ( isset($_POST['username']) && isset($_POST['passwort']) ) {    	
    	$_POST['username'] = strtolower($_POST['username']);
    	if (is_valid_email($_POST['username']) && is_valid_pass($_POST['passwort'])) {
			$result = mysql_query("SELECT * FROM amase_users WHERE email='" . $_POST['username'] . "'") OR die("user database does not exist - Error #25 - SQL-Message: " . mysql_error());
	   		$result = mysql_fetch_assoc($result);
	    	
	   		if ($result['passwort'] == md5($_POST['passwort']) ) {
                            session_regenerate_id() OR die("could not generate session-ID");
                            $_SESSION['angemeldet'] = true;
                            $_SESSION['fullname'] = $result['fullname'];
                            $_SESSION['username'] =  $result['email'];
                            $_SESSION['user_ID'] = $result['id'];
                            $_SESSION['user_status'] = $result['status'];
                            log_user(1);
                            unset ($passwort);
                            unset ($username);
                            unset ($passwort);
                            if ($debug_mode) echo "Anmeldung erfolgreich";
			} else {
                            unset ($username);
                            unset ($passwort);
                            box(false, "wrong password or username", 0, NULL);
			}
    	} else {
            box(false, "username or password contais invalid characters", 0, NULL);
		}
	unset($result);
    }


    // Session angemeldet flag setzen und user ID
    if (!(isset($_SESSION['angemeldet'])))  {
	$_SESSION['angemeldet'] = false;
	$_SESSION['user_ID'] = "-1";    // ID des aktuellen Benutzers
    }

    html_head(false);   // HTML HEADER SCHREIBEN
?>

<body>
    <div id ="topBox">
        <div id ="infoBox">
            <?php 
            if (!(isset($_GET['template']))) $_GET['template'] = 0;
            show_info($_GET['template']); ?>
        </div>
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
                    $link= " <a href=\"" . $_SERVER['PHP_SELF'] . "?template=" . $i . "\">" . $TemplateItem. "</a>";
                    if ($i == $template) $link = " <b>" . " <a class=\"active\" href=\"" . $_SERVER['PHP_SELF'] . "?template=" . $i . "\">" . $TemplateItem. "</a>" . " </b>"; 	
                    echo $link . "&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;";
                }                 
                echo " <a href=\"statistics.php\">Statistics</a>";
            ?>
        </div>
        <div id ="search">
            <?php show_fulltext_search($template); ?>
        </div>
    </div>
<?php
    list($HEADER, $HEADERSIZE) = build_cols_array($template);   // Spaltennamen aus Datenbank lesen
    // Assoziatives Array erstellen, das als Schlüssel die tabellennamen und als Inhalt das Spaltentemplate hat
    // var_dump($HEADER);
    // var_dump(get_template($template));
    $sqlcols_and_tabletemplates = array_combine_php5($HEADER,get_template($template));

    // Wichtige Informationen in die aktuelle Sessionvariable stopfen
    $_SESSION['sqlcols_and_tabletemplates'] = $sqlcols_and_tabletemplates;
    $_SESSION['sql_table_header'] = $HEADER;
    $_SESSION['sql_table_headersize'] =	$HEADERSIZE;
    $_SESSION['html_template'] = $template;

    // SQL- und PHP-Intrusion-Syntax aus Benutzereingabe filtern
    // Sortierkriterium von SQL Syntax befreien
    if (isset($_GET['with'])) $_GET['with'] = clean_from_sql($_GET['with']);

    if (isset($_GET['limit'])) { if (!(in_array($_GET['limit'], $HEADER))) { unset($_GET['limit']); } } // Wenn übergebene Spalten-Einschränkung kein Spaltenname ist, dann lösche Einschränkung

    if (isset($_GET['ordnung'])) {  // Wenn übergebene Spaltenordnung kein Spaltenname ist, dann lösche Ordnung
            if (!(in_array($_GET['ordnung'], $HEADER))) { unset($_GET['ordnung']); }
    } else {
            if ($_SESSION['html_template'] != 4) $_GET['ordnung'] = $StandardOrdnung;
    }

//    if (is_logged_in()) {   // Zeige "hinzufügen", wenn eingeloggt
//            if ($template == 0) $new_entry = "newstud.png"; // For the students table
//            else $new_entry = "newrec.png"; // For the Grades, Master Thesis, Courses tables
//            
//    }
    echo "<div id=\"newEntry\"><a href=\"javascript:poptastic('edit.php?mode=new')\">new entry</a></div>";

	// Themengebiet eingegrenzt?
	if ( (isset($_GET['limit'])) && (isset($_GET['with'])) ) {
            $query .= " WHERE " . ($_GET['limit']). "='".  $_GET['with'] . "'";
    }

    // Ordnung gesetzt?
    if (isset($_GET['ordnung']))  $query .= " ORDER BY " . $_GET['ordnung'] . " DESC";
    
########### Searching
    if (isset($_POST['search']) && ( strlen($_POST['search']) >= 2 )) {
        $search = clean_from_sql($_POST['search']); // Von schädlichem Code befreien

        $SuchString_array = explode("," , $search_string[$template]);   // Die Suchspalten aus config berücksichtigen

        $query = "SELECT * FROM $tabellen[$template] WHERE ";   // Select Statement für den Query vorbereiten

        $query .= array_pop($SuchString_array) . " LIKE '%$search%'";   // Das letzte Element zur Suche hinzufügen. Wenns das einzige war, dann ist das Array leer		 

        foreach ($SuchString_array as $SuchString) {    // Für jede weitere Spalte, in der gesucht wird, verlängert sich die SQL Anweisung
                $query .= " OR $SuchString LIKE '%$search%'";
        }	
        $query .= ";";
        $_SESSION['wasFiltered'] = true;
    }else{
        $_SESSION['wasFiltered'] = false;
    }
    
    $result = mysql_query($query) OR die("Kein Datensatz vorhanden - Error #23 - SQL-Message: " . mysql_error());   // Datenbankanfrage senden
    $num_rows_selected = mysql_num_rows($result);

    // TABLE aufbauen
?>


    
<?php         
   ####### Aktuelle Anzeigeoptionen
        echo "<div id=\"extras\">";
	echo "<p>";
	echo  "View: <b>" . $num_rows_selected ."/". $num_rows_default ."</b> records";
	echo "</p>";
        echo "</div>";
    ####### Aktuelle Anzeigeoptionen

    ####### View FULL Database
        echo "<br/><br/><br/><br/><br/>"; 

    ####### Publikationsliste ausgeben
        echo "<table width=\"1200\" border=\"1\" align=\"center\" cellpadding=\"5\" cellspacing=\"0\" class=\"tabellen_layout\">\n";

        print_table_header(get_template($template), $HEADER, $template);

	print_table_body($result, $template, $autor_highlight);
	echo "</table><br/>";
    
    /*Seitenzahlen auch unten angeben*/
    if(isset($html_pages)){
        echo $html_pages;
    }
    
    echo "<br/>"; 
        
    include_once "ExportTableColumns.php";
    
      if ((isset($_SESSION['user_status'])) && ($_SESSION['user_status'] == "supervisor")) { ?>
        <form name="ExportTable" id="ExportTable" action="ExportTable.php" method="post">
          <input type="hidden" name="csv_text" id="csv_text" value='<?php print_r($csv_text) ?>' />
          <input type="hidden" name="csv_file_name" id="csv_file_name" value='<?php echo $file ?>' />
          <input type="submit" value="Export Table" />
        </form>
      <?php     
      }     
      echo "<br/><br/>";
      echo "<div id=\"pagesDown\"></div>";
      include "footer.html";
?>
