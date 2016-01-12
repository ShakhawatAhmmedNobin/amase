<?php

    defined('_VALID_INCLUDE') or die('Direct access not allowed (modul).');
    require_once "config.inc.php";
    require_once "security.inc.php";
	
    ######## Maintenance Funktion
    if ( is_file("maintenance") && ($_SERVER['REMOTE_ADDR'] != "134.96.95.187") ) {
            html_head();
            echo "<body>\n";
            show_logo();
            echo "<br><br>";
            $hinweis = "Das AmaseBASE-System wird momentan gewartet. Bitte versuchen Sie es später wieder.<br><br>";
            $hinweis .=  "The AmaseBASE-System is unavailable due to maintenance activity. Please try again later";
            box(false, $hinweis, 0, 0);
            include "footer.html";
            exit;
    }
    ######## Maintenance Funktion

	function html_head($ajax_set=false) {
		global $xajax;
		global $version;		
	?><html lang="en">
<head>
    <meta charset="utf-8" /> 
    <title>AmaseBase System - 2014</title>
    <link rel="stylesheet" type="text/css" href="style.css" media="screen">
    <link rel="stylesheet" type="text/css" href="print.css" media="print">
    <link rel="shortcut icon" href="icons/ico.ico" />
    <script src="jquery-1.11.1.min.js" type="text/javascript"></script>
    <script src="functions.js" type="text/javascript"></script>
</head>
<?php
	}
	
	function show_logo() {  //  Logo laden und anzeigen
            global $InstallPath;
            echo "<img src=\"" . $InstallPath . "/head_logo.png\" alt=\"AmaseBase\">";
	}
        
        function show_info($template) {
            echo "<img src=\"./icons/infoMain.png\" alt=\"Read the infos about this page\" title=\"Page Info\" onclick=\"javascript:poptastic2('infoTemplates.php?template=" . $template . "')\">\n";                        
        }
	
	function is_active_userid($user_id) {	// Pruefen, ob genannter Benutzer eingeloggt ist											// Debug
            if ($user_id == -1) { die("User-ID not set. Dataset could not be matched with owner."); }
            return ($user_id == $_SESSION['user_ID']);	
	}
	
	function is_valid_date($date) {
            if (!isset($date) || $date=="") return false;
   
            list($yy,$mm,$dd)=explode("-",$date);

            if ($dd!="" && $mm!="" && $yy!="") return checkdate($mm,$dd,$yy);
            else return false;	
	}
	
	function germandate_from_sqldate($date) {
            if (!isset($date) || $date=="") {
	        return false;
            }
            list($yy,$mm,$dd)=explode("-",$date);
            return("$dd.$mm.$yy");
	}
	
	function build_infobox_list($linktext, $text) { // Linktext: klickbar, Titel der box, text der Box
		echo "<a class=\"infobox\" href=\"#INFO\">" . $linktext . "<span>";
		$text = explode(", ", $text);
		$box = "<b>Allowed symbols for a valid '" . preg_replace("/<br>/" , " " , $linktext) . "':</b><br>";
		$box .= "\n<ul>\n";
		
		foreach ($text as $item) {
			$box .= "  <li>";
			$box .= $item;
			$box .= "  </li>\n";
		}
		
		$box .= "</ul>\n";
		$box .= "</span></a>";
		return ($box);
	}
	
	 /**
	 * Wandelt SQL Timestamp in lesbares Format um 
	 *
	 * @author $Author: Daniel $ 
	 * @version $Revision: 46 $
	 * @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	 **/
	function timestamp_mysql2german($date) {
        
    	$stamp = sprintf("%02d.%02d.%04d", substr($date, 6, 2), substr($date, 4, 2), substr($date, 0, 4));
    	$stamp .= " - ";
    	$stamp .= sprintf("%02d:%02d:%02d", substr($date, 8, 2), substr($date, 10, 2), substr($date, 12, 2));
    	return date('d.m.Y H:i:s', strtotime($date));
	}

	function homelink($linktext, $target) { //Gibt einen Linkbutton zur Hauptseite zuruck. Der Linktext kann frei bestimmt werden mit $name
		// require "config.inc.php";
		global $InstallPath;
		$forwardtarget = "index.php?template=". $target;	
		echo "<input type=button value=\"$linktext\" onclick=\"window.location='" . $InstallPath .  "/$forwardtarget'\">";
	}

	/**
	 * box()
	 * Zeigt eine Informationsbox an
	 *
	 * @author $Author: Daniel $ 
	 * @version $Revision: 46 $
	 * @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
 	 * @param bool 	$modus 		OK oder ERROR = true oder false
	 * @param text 	$text 		Hinweistext, der angezeigt wird
	 * @param int 	$link_back 	0=kein Link, 1=Schlieï¿½en Button, 2=Link zur Portalseite, 3=backlink
	 * @param int	$target_id  Template ID	
	 * @return
	 **/
	function box($modus, $text, $link_back=2, $target_id=0) {
		echo "<table width=\"300\" border=\"0\" align=\"center\" cellpadding=\"6\" cellspacing=\"0\" class=\"box\">\n";
  		echo "<tr><td>\n";
  		echo "<div align=\"center\">";

		if ($modus==true) {
			echo "<img src=\"./icons/ok.gif\" alt=\"Ok\" align=\"top\">";
		} else {
			echo "<img src=\"./icons/error.gif\" alt=\"Error\">";
		}
		echo "</div></td>\n";
		echo "<td nowrap>&nbsp;&nbsp;" . $text . "</td>";
		
		### kein Link
		if ($link_back == 0) {
			echo "<tr><td colspan=\"2\"></td></tr>";
			
		### Schlieï¿½en Button
		} elseif ($link_back == 1)  {
			echo "<tr><td colspan=\"2\"><div align=\"center\"><br><br><input type=\"button\" value=\"Please click here to close the window\" onclick=\"opener.focus();opener.location.href='index.php?template=" . $_SESSION['html_template']. "';window.close()\"><br></div></td></tr>";

		### zurï¿½ck-Button
		} elseif ($link_back == 3)  {
			echo "<tr><td colspan=\"2\"><div align=\"center\"><br><br><input type=\"button\" value=\"back\" onclick=\"history.back()\"><br></div></td></tr>";


		### alles andere Link zur Hauptseite
		} else {
			if (!(is_null($target_id))) {
				echo "<tr><td colspan=\"2\"><div align=\"center\"><br><br><input type=\"button\" value=\"OK\" onclick=\"location.href='index.php?template=$target_id'\"><br></div></td></tr>";
			}	else {
				echo "<tr><td colspan=\"2\"><div align=\"center\"><br><br><input type=\"button\" value=\"OK\" onclick=\"location.href='index.php?template=0'\"><br></div></td></tr>";	
			}
		}
		echo "</table><br>\n";
	}
	
	/**
	 * Zeigt eine Fehlermeldung an mit uebergebenem Text
	 * @param string $text 			Ausgabetext der Fehlermeldung
	 * @param int 	 $linkback 		0=kein Link, 1=Schlieï¿½en Button, 2=Link zur Portalseite, 3=backlink
	 * @param mixed  $target_id 	Template-ID der Hauptseite
	 * @return void
	 **/
	function warn($text, $linkback, $target_id) {
		box(false, $text, $linkback, $target_id);
	}

	/**
	 * Zeigt eine Hinweismeldung, die den User ï¿½ber eine erfolgreiche Operation informiert
	 *
	 * @author $Author: Daniel $ 
	 * @version $Revision: 46 $
	 * @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	 * @param string $text 			Ausgabetext der Hinweisbox
	 * @param int 	 $linkback 		Soll ein Link zurï¿½ck zur Hauptseite angezeigt werden?
	 * @param mixed	 $target_id 	Template-ID der Hauptseite
	 * @return void
	 **/
	function ok($text, $linkback, $target_id) {
		box(true, $text, $linkback, $target_id);
	}



	/**
	 * Gibt aus, ob ein query erfolgreich war
	 *
	 * @author $Author: Daniel $ 
	 * @version $Revision: 46 $
	 * @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	 * @param bool 	$result	 	update erfolgreich?
	 * @param mixed $text 		Ausgabetext fï¿½r die Box
	 * @param bool 	$linkback 	Soll ein Link zurï¿½ck zur Hauptseite angezeigt werden?
	 * @param mixed $target_id 	Template-ID der Hauptseite
	 * @return
	 **/
	function print_query_result($result, $text, $linkback, $target_id) {
		echo ($result) ? ok($text . " successful", $linkback, $target_id) : warn("$text not successful", $linkback, $target_id);
	}


	/**
	* Gibt an, ob ein Benutzer eingeloggt ist
	*
	* @author $Author: Daniel $ 
	* @version $Revision: 46 $
	* @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	* @param void
	* @return bool Ist der Benutzer eingeloggt?
	* 
	*	**/

	function is_logged_in() {
		// echo "fettes flag angemeldet: " . $_SESSION['angemeldet']  . " fette user_ID: ".$_SESSION['user_ID']; 
		return ( isset($_SESSION['angemeldet']) && ($_SESSION['user_ID']!="-1") );
	}

        //  Gibt eine Fehlermeldung aus, wenn kein Benutzer eingeloggt ist und beendet das Script
	function check_login() {
		if (!(is_logged_in() )) die ("You need to be logged in to perform this action / Sie muessen eingeloggt sein um diese Aktion durchfuehren zu koennen");
	}

        // Zeigt Login Box, wenn Benutzer nicht angemeldet / zeigt Logout Button wenn Benutzer angemeldet
	function show_login() {
		if (is_logged_in()) {   // Wenn session offen und benutzer angemeldet, dann zeige logout button
                    echo "<p>Welcome <b>" . $_SESSION["fullname"] . "</b>! You are logged in as <b>" . $_SESSION['user_status'] . "</b> | <a class=\"logout\" href=\"logout.php\">Logout</a></p>" ;
		} else {    //Sonst zeige login Formular
                    echo "";
                    echo "<form id=\"form1\" name=\"form1\" method=\"post\" action=\"index.php\">";
                    echo "username: <input name=\"username\" type=\"text\" maxlength=\"50\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "password: <input name=\"passwort\" type=\"password\" maxlength=\"30\">&nbsp;&nbsp;";
                    echo "<input type=\"submit\" name=\"Submit\" value=\"Login\">";
                    echo "</form>";
		}
	}


	// Generiert eine Liste mit den Namen der SQL Spalten anhand der Template-ID
	function build_cols_array($template) {
		
		global $tabellen;
				
		$result = mysql_query("SELECT * FROM " . $tabellen[$template] . " WHERE 1=0;");
		$count = mysql_num_fields($result);

		for ($i = 0; $i < $count; $i++){
    		$header[] = mysql_field_name($result, $i);
		}
		return array ($header, count(get_template($template)));
	}


	/**
	* Emuliert die Funktion array_combine aus PHP5: nimmt 2 Arrays und fï¿½gt sie zu einem Schlï¿½ssel=>Wert Array zusammen
	*
	* @author $Author: Daniel $ 
	* @version $Revision: 46 $
	* @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	* @param array $keys Array mit Schlï¿½sseln
	* @param array $vals Array mit Werten
	* @return bool true=string ist ok; false=string ist fehlerhaft
	**/
	function array_combine_php5( $keys, $vals ) {
 		 // var_dump ($keys);															// Debug Zeile
 		 // var_dump ($vals);															// Debug Zeile
 		
 		$elementzahl = max( count( $keys ), count( $vals ) );
 		$r = array();
 		for( $i=0; $i<$elementzahl; $i++ ) {
  			// echo "i= $i; <br>";														//Debug Zeile
  			$r[ $keys[ $i ] ] = $vals[ $i ];
 		}
 		return $r;
	}


	/**
	* Enthï¿½lt der String nur Buchstaben und ist 3-10 Zeichen lang?
	*
	* @author $Author: Daniel $ 
	* @version $Revision: 46 $
	* @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	* @param string $text zu testender Text
	* @return bool true=string ist ok; false=string ist fehlerhaft
	**/
	function is_clean ($text) {
		setlocale(LC_CTYPE,"de_DE.ISO-8859-1");
		$check = (preg_match("#^[\w\s]{1,50}$#i" ,$text));
	
		return $check;
	}
	
	/**
	* Enthï¿½lt der String nur Zahlen und Buchstaben und ist 4-20 Zeichen lang?
	*
	* @author $Author: Daniel $ 
	* @version $Revision: 46 $
	* @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	* @param string $text zu testender Text
	* @return bool true=string ist ok; false=string ist fehlerhaft
	**/
	function is_clean_login ($text) {
		return preg_match("#^[A-Za-z0-9]{4,20}$#" ,$text);
	}

        //  Prueft einen Tag ob dieser eine Zahl ist und gib true oder false zurueck
	function is_clean_digit ($digit) {
		$check = (preg_match("#^[0-9]+$#" ,$digit));
		return $check;  // return bool true=string ist eine Zahl; false=string ist keine Zahl
	}

        //Saubert einen Tag von SQL Syntax und gibt eine Zahl zwischen 1-3 Stellen aus, ansonsten 0
	function make_clean_digit ($digit) {
		return (is_clean_digit($digit)) ? $digit : 0;
	}

        // SQL Intrusion Protection
	function clean_from_sql ($v) {
            static $search = array("--","#",";","insert","drop","dump","select","update","delete","like","grant","revoke","xp_",'"',"'", "=", "\*", "<", ">", "\$");
            foreach ($search as $s) {
                $v=preg_replace("/".sql_regcase($s)."/","",$v);
            }
            return mysql_real_escape_string($v);
	}

	/**
	* Zeigt die ï¿½berschrift in der letzten Spalte - "Verwaltung"  - an
	*
	* @author $Author: Daniel $ 
	* @version $Revision: 46 $
	* @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	* @param void
	* @return void
	**/
    function print_admin_col_header () {
		if (is_logged_in()) {
	    	echo "    <TH><div class=\"not_linked\">Actions&nbsp;</div></TH>\n";
		}
	}

    /**
     * Zeigt die letzte Spalte "Actions" an
     **/ 
    function print_admin_col ($id, $tabelle, $user_id="-2", $student_id="-1") {
        if (is_logged_in()) {
            echo "<td>\n";
            if ( ($_SESSION["user_ID"] == $user_id) || ($_SESSION['user_status'] == "supervisor") ) {
                echo "<img class=\"actions\" src=\"./icons/delete.png\" ALT=\"delete Record\" 
                    TITLE=\"delete\" onclick=\"alert_delete($id,$tabelle)\">&nbsp;";
                echo "<img class=\"actions\" src=\"./icons/edit.png\" ALT=\"edit Record\" 
                    TITLE=\"edit\" onclick=\"javascript:poptastic('edit.php?id=$id&amp;mode=edit')\">";
                
                if ($_SESSION['html_template'] == STUDENTS_TEMPLATE) {
                    echo "<img class=\"actions\" src=\"./icons/record.png\" alt=\"Prints all records of the selected student\" 
                        title=\"transcript of records\" onclick=\"javascript:poptastic('transcript.php?id=" . $student_id. "')\">\n";                        
                    echo "<img class=\"actions\" src=\"./icons/info.png\" alt=\"Student information\" 
                        title=\"Student information\" onclick=\"info_popup(" . $student_id . ")\">\n";
                    echo "<img class=\"actions\" src=\"./icons/thumbs_down.png\" alt=\"drop out student\" 
                        title=\"drop out student\" onclick=\"studentStatus(" . $student_id . ", 'drop')\">\n";
                
                } elseif ($_SESSION['html_template'] == GRADES_TEMPLATE) {
                    echo "<img class=\"actions\" src=\"./icons/record.png\" alt=\"Prints all records of the selected student\" 
                        title=\"transcript of records\" onclick=\"javascript:poptastic('transcript.php?id=" . $student_id. "')\">\n";                                            
                    echo "<img class=\"actions\" src=\"./icons/form.png\" alt=\"generate announcement\" 
                        title=\"generate announcement\" 
                        onclick=\"javascript:poptastic('generate_pdf.php?studid=" . $student_id . "&amp;datid=$id')\">\n";
                
                } elseif ($_SESSION['html_template'] == THESIS_TEMPLATE) {
                    echo "<a href=\"ma_deg_cert.php?id=" . $student_id . "\" alt=\"Masters degree certificate\" 
                        title=\"Masters degree certificate\">Certificate</a>";
                    echo "<br/>";
                    echo "<a href=\"transcript.php?id=" . $student_id . "\" alt=\"German Transcript\" title=\"German Transcript\">Transcript</a>";
                    echo "<br/>";
                    echo "<a href=\"zeugnis.php?id=" . $student_id . "\" alt=\"German Zeugnis\" title=\"German Zeugnis\">Zeugnis</a> ";
                    echo "<a href=\"zeugnis-long.php?id=" . $student_id . "\" alt=\"German Long Zeugnis\" title=\"German Long Zeugnis\">(X)</a> ";
                    echo "<br/>";
                    echo "<a href=\"supplement.php?id=" . $student_id . "\" alt=\"Diploma Supplement\" 
                        title=\"Diploma Supplement\" target=\"_blanc\">Supplement</a>";
                    echo "<br/>";
                    echo "<a href=\"urkunde.php?id=" . $student_id . "\" alt=\"Master Urkunde\" title=\"Master Urkunde\">M. Urkunde</a>";
                    
                } elseif (!isset($_SESSION['html_template'])){
                    echo "<img class=\"actions\" src=\"./icons/record.png\" alt=\"Prints all records of the selected student\" 
                        title=\"transcript of records\" onclick=\"javascript:poptastic('transcript.php?id=" . $student_id. "')\">\n";                        
                    echo "<img class=\"actions\" src=\"./icons/info.png\" alt=\"Student information\" 
                        title=\"Student information\" onclick=\"info_popup(" . $student_id . ")\">\n";                                 
                
                }
            }else{                   
                echo "<img class=\"actions\" src=\"./icons/info.png\" alt=\"Student information\" 
                    title=\"Student information\" onclick=\"info_popup(" . $student_id . ")\">\n";
            }
            echo "</td>\n";
        }
    }
    
    /**
     * Gibt die Tabellenueberschriften aus und formatiert sie
     **/ 
    function print_table_header ($table_cols_list, $tablenames, $template) {
            global $no_col_links;   // Was sind keine Spalten?
            global $hint_col_element;   // Welche Cols verstecken? (Verdammt, das sollte eigentlich hide heiï¿½en)
            global $admin_only_col; // Was darf nur der Admin sehen?

            echo "<tr class='tableHeader' >\n";
            $size = count ($table_cols_list);

            for ($i = 0; $i < $size; $i++) {
                if (!(in_array($table_cols_list[$i], $hint_col_element))) { // Pruefen welche Spalten versteckt werden sollen
                        if ((  (!(in_array($table_cols_list[$i], $admin_only_col))) || is_logged_in()) && true) {

                                // Wenn Spaltenname angeklickt wurde, dann wird er ausgegraut
                                if (isset($_GET['ordnung']) && ($tablenames[$i] == ($_GET['ordnung']))) {
                                        echo "<TH><div class=\"active_col\">$table_cols_list[$i]</div></TH>\n";
                                } else {    // ...ansonsten wird er zum Link

                                    //Wenn der Spaltenname nicht verlinkt werden soll wird das hier berï¿½cksichtigt
                                    if (in_array($table_cols_list[$i], $no_col_links)) {
                                            echo "<th><div class=\"not_linked\">$table_cols_list[$i]</div></TH>\n";
                                    } else {
                                            $link = "<TH><div class=\"col\"><a href=\"". $_SERVER['PHP_SELF'] . "?template=" . $template . "&amp;ordnung=" . $tablenames[$i];
                                    if ( isset($_GET['limit']) && isset ($_GET['with']) ) {
                                            $link .= "&amp;limit=" . ($_GET['limit']) . "&amp;with=" . ($_GET['with']);
                                    }
                                    $link .= "\">" . $table_cols_list[$i] . "</a></div></th>\n";
                                    echo $link;
                                    }
                                }
                        } else ;
                }
            }
            print_admin_col_header() ;
            echo "  </TR>\n";
    }

	/**
	 * Hebt einen Autor FETT hervor, wenn nach diesem sortiert ist
	 *
	 * @author $Author: Daniel $ 
	 * @version $Revision: 46 $
	 * @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	 * @param string $autor Aktueller Autor
	 * @param string $autor_highlight Gesuchter Autor
	 * @return string Autor FETT oder normal
	 **/
	function highlight_autor($autor, $autor_highlight) {
		if ($autor == $autor_highlight){
			return "<b>" . $autor . "</b>";
		} else {
			return $autor;
		}
	}

	/**
	 * Erzeugt die Liste der Co-Autoren
	 *
	 * @author $Author: Daniel $ 
	 * @version $Revision: 46 $
	 * @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
	 * @param array $coautoren Liste von Coautoren einer Publikation
	 * @param int $color Farbe des Datensatzes
	 * @param int $template Das aktuelle Template
	 * @param int $autor_highlight Autor, der hervorgehoben werden soll
	 * @return string Gibt formatierte Autorenliste zurï¿½ck
	 **/
	function set_coautor_links ($coautoren, $color, $template, $autor_highlight) {
		$coautoren_liste = explode(",", $coautoren);
		$autor_string = "";
		$list_size = count($coautoren_liste);
		$ordnung ="hauptelement";

		for ($i = 0; $i < $list_size; $i++) {
			$autor_string .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?template=" . $template . "&amp;ordnung=$ordnung&amp;limit=nebenelement&amp;with=" . urlencode(trim($coautoren_liste[$i])) . "\">" . highlight_autor(trim($coautoren_liste[$i]), $autor_highlight) . "</a>";
			// Komma nach jedem Element einfï¿½gen, wenn es nicht das letzte ist
			if (!($i == $list_size -1)) { $autor_string .= ", ";}
			// Pretty sourceprinter
			// echo "\n";
		}
		return $autor_string;
	}

	function print_table_body ($result, $template, $autor_highlight) {
            
		function colorizeRow ($status) {
            // Set a color for each row based on its status value:
            if ($status=="open") 
                return "<tr class=\"open\">\n";
            else if ($status=="failed") 
                return "<tr class=\"failed\">\n";
            //else if ($status == "grade") return "<tr class=\"grade\">\n";
            else 
                return "<tr>\n";
		}
        
        /**
         * Sets a color for each row based on the student-status
         * Available statuses:
         *      completed: green color
         *      dropped: red color
         *      open: the normal line-color
         **/
        function colorizeRowStudentsTable ($id, $studentStatus) {
            if ($studentStatus == "abc")
            {
                return "<tr id='nr$id' class='completed'>\n"; // use the id for finding the line with javascript
            }
            else if ($studentStatus == "drop")
            {
                return "<tr id='nr$id' class='dropped'>\n";
            }
            else
            {
                return "<tr id='nr$id'>\n";
            }
		}
		
        if (!(isset($_SESSION['html_template'] ))) { 
            $_SESSION['html_template']  = STUDENTS_TEMPLATE; 
        }
        
        if (!(isset($_GET["ordnung"]))) { 
            $_GET["ordnung"] = "id"; 
        }

  		function fill_default ($cell, $index) { // Fuellt Zellen mit Verlinktem Eintrag oder leere Zellen mit &nbsp; auf
			if ($cell == "") return "<td>&nbsp;</td>\n";
			else return "<td><a href=\"" . $_SERVER['PHP_SELF'] . "?template=" . $_SESSION['html_template']  . 
                "&amp;ordnung=" . $_GET["ordnung"] . "&amp;limit=" . $index . "&amp;with=" . 
                urlencode($cell) . "\">" . $cell . "</a></td>\n";
		}          
	
		function go_obstart() {
            // Testet, ob der Browser GZIP unterstützt
            
			$encode = $_SERVER['HTTP_ACCEPT_ENCODING'];
			if (ereg("gzip", $encode)) ob_start("ob_gzhandler");
			else ob_start();
		}                 
                
        ob_start();
        ################################################# Students 0
		if ($_SESSION['html_template'] == STUDENTS_TEMPLATE) {
                    if ((isset($_SESSION['user_status'])) && ($_SESSION['user_status'] == "supervisor")) {
                        $userId = $_SESSION["user_ID"];
                        $resultSupervisor = mysql_query("SELECT firma, email FROM amase_users WHERE id=$userId;");
                        if ($resultSupervisor) {
                            $rowUser = mysql_fetch_assoc($resultSupervisor);
                        }
                    }
                        while($row = mysql_fetch_assoc($result)) {
                            echo colorizeRowStudentsTable($row["id"], $row["complete"]); // colorize the whole line based on student's status.
                            echo "<td>" . $row["id"] . "</td>\n";
                            echo fill_default($row["nachname"], "nachname", $row["id"] );
                            echo fill_default($row["vorname"], "vorname" ) ;
                            echo fill_default($row["gender"], "gender" ) ;
                            echo fill_default($row["university1"], "university1");			
                            echo fill_default($row["matrikel1"], "matrikel1"); 
                            echo fill_default($row["university2"], "university2");			
                            echo fill_default($row["matrikel2"], "matrikel2"); 
                            echo fill_default($row["universitymaster"], "universitymaster");
                            echo fill_default($row["start_semester"], "start_semester");
                 ############## Complete and Term columns calculations           
                            if ((isset($_SESSION['user_status'])) && ($_SESSION['user_status'] == "supervisor") 
                                && $row["complete"] != "drop") 
                            {
                                echo "<td><table>";
                                $resultLights = greenRedLightTik ($row["id"], $row["university1"], $row["university2"], $rowUser);
                                if (isset($_GET["submit1$row[id]"])) {
                                    mysql_query("UPDATE amase_students SET complete=CONCAT(complete, 'a') WHERE id=$row[id];");
                                    $resultLights[0] = "<img src=\"./icons/green.png\" title=\"Ready First University\">";
                                }
                                if (isset($_GET["submit2$row[id]"])) {
                                    mysql_query("UPDATE amase_students SET complete=CONCAT(complete, 'b') WHERE id=$row[id];");
                                    $resultLights[1] = "<img src=\"./icons/green.png\" title=\"Ready Second University\">";
                                }
                                if (isset($_GET["submit3$row[id]"])) {
                                    mysql_query("UPDATE amase_students SET complete=CONCAT(complete, 'c') WHERE id=$row[id];");
                                    $resultLights[2] = "<img src=\"./icons/green.png\" title=\"Ready Participation Fees\">";
                                }                                    
                                echo "<tr><td><form>$resultLights[0]</form></td><td><form>$resultLights[1]</form></td><td>$resultLights[2]</td></tr>";
                                echo "</table></td>\n";
                            
                                $i = 0;
                                foreach ($resultLights as $key => $value) {
                                    if (strstr($value, "green")) {
                                        $i++;
                                    }
                                }
                                if ($i < 3) 
                                {
                                    echo "<td>". diff_semester($row["start_semester"], get_current_semester()). "</td>\n"; 
                                }
                                else 
                                {
                                    echo "<td>Completed</td>\n"; 
                                }
                            } 
                            else 
                            {
                                echo "<td></td><td></td>";
                            }
                            echo fill_default($row["birth_date"], "birth_date");			
                            echo fill_default($row["birth_place"], "birth_place"); 
                            echo fill_default($row["country_birth"], "counry_birth");
                            echo fill_default($row["nationality"], "nationality");
                            echo "<td><a href=\"mailto:" . $row["email"] . "\">" . $row["email"] . "</a></td>\n";
                            echo "<td>" . $row["wer"] . "</td>\n";
                            echo "<td>" . timestamp_mysql2german($row["datum"]) . "</td>\n";
 
                            print_admin_col($row["id"], $template, $row["author_id"], $row["id"]);
                            echo "</tr>\n";
			}		
                ################################################# Grades 1
		} elseif ($_SESSION['html_template'] == GRADES_TEMPLATE) {
            
            // In order to present 50 rows per pages:
            $numrows = mysql_num_rows($result);
            /*
			* TODO:
			* Check if a search was applied.
			* Uses an hack because changing pages does not remember the search.
			* numrows < 4000 Ist gegen Overflow bei leerer Suche.
			* @marvin
            */
            if(isset($_SESSION['wasFiltered'])&&$_SESSION['wasFiltered']){

            	$totalpages = 1;
            	$currentpage = 1;
            	$rowsperpage = $numrows;
            	$queryLimit = "LIMIT 1000";
            }else{
            	$rowsperpage = 50;
                $totalpages = ceil($numrows / $rowsperpage);
                isset($_GET['currentpage']) && is_numeric($_GET['currentpage']) ? ($currentpage = (int) $_GET['currentpage']) : ($currentpage = 1);
                if ($currentpage > $totalpages) $currentpage = $totalpages;
                if ($currentpage < 1) $currentpage = 1;
                $offset = ($currentpage - 1) * $rowsperpage;
            	$queryLimit = "LIMIT $offset, $rowsperpage";
            }

            if ( (isset($_GET['limit'])) && (isset($_GET['with'])) ) {
                mysql_query("SELECT * FROM amase_grades WHERE " . ($_GET['limit']). "='".  $_GET['with'] . "' ORDER BY " . $_GET['ordnung'] . " DESC " . $queryLimit ) or die('<p class="error">Unable to connect to the database at this time.</p>');
            } else {
                if (isset($_POST['search'])) {
                }else if (isset($_GET['ordnung'])) {
                    $result = mysql_query("SELECT * FROM amase_grades ORDER BY " . $_GET['ordnung'] . " DESC " . $queryLimit ) or die('<p class="error">Unable to connect to the database at this time.</p>');
                } else {
                    $result = mysql_query("SELECT * FROM amase_grades " . $queryLimit) or die('<p class="error">Unable to connect to the database at this time.</p>');
                } 
            }
            $num_results = mysql_num_rows($result); 
            while($row = mysql_fetch_assoc($result)) { // iterate over the results:
                
                echo colorizeRow($row["status"]);   
                echo "<td>" . $row["id"] . "</td>\n";
                echo fill_default($row["name"], "name");
                echo fill_default($row["university"], "university");
                echo fill_default($row["coursename"], "coursename");			
                echo "<td>" . $row["ex_date"] . "</td>\n"; 
                echo fill_default($row["modul"], "modul");
                    
                // 	LOCAL GRADE und ECTS GRADE ANZEIGEN
                if ($row["status"]=="open") { 
                        echo "<TD>open</TD>\n"; // local grade
                        echo "<TD>open</TD>\n"; // ECTS grade
                        echo "<TD>" . $row["credits"] . "</TD>\n" ; // ECTS credits
                        
                } elseif  ($row["status"]=="grade") {
                        echo "<TD>" . $row["local_grade"]. "</TD>\n";
                        echo "    <TD>" . $row["ects_grade"] . "</TD>\n";
                        echo '    <TD>' . $row["credits"] . "</TD>\n" ;
                        
                } elseif  ($row["status"]=="passed") {
                        echo "    <TD>passed</TD>\n";
                        echo "    <TD>none</TD>\n";
                        echo '    <TD>' . $row["credits"] . "</TD>\n" ;
                        
                } elseif  ($row["status"]=="failed") {
                        echo "	  <TD>" . $row["local_grade"]. "</TD>\n";
                        echo "    <TD>" . $row["ects_grade"] . "</TD>\n";
                        
                        echo "    <TD>none</TD>\n";
                } else {
                        echo "    <TD>error</TD>\n";
                        echo "    <TD>error</TD>\n";
                        echo "    <TD>error</TD>\n";
                }

                echo "    <TD>" . $row["try"] . "</TD>\n";
                echo "    <TD>" . $row["wer"] . "</TD>\n";
                echo "    <TD>" . timestamp_mysql2german($row["datum"]) . "</TD>\n";

                // Wenn Admin eingeloggt, dann zeige Tools für jeden Datensatz an
                print_admin_col($row["id"], $template, $row["author_id"], $row["student_id"]); 
                echo "  </tr>\n";
			}
            global $html_pages;
            $html_pages = "";
			if(isset($_SESSION['wasFiltered'])&&$_SESSION['wasFiltered']){
				$html_pages .= '<div id = "pages">';
				$html_pages .= "<p>You searched for:</p><p>".$_POST['search']."</p>";
                $html_pages .= '</div>';
			}else{
                $range = 3;
                $html_pages .= '<div id = "pages">';
                if ($currentpage > 1) {
                    $html_pages .= " <a href='?template=1&currentpage=1'><<</a> ";
                    $prevpage = $currentpage - 1;
                    $html_pages .= " <a href='?template=1&currentpage=$prevpage'><</a> ";
                }
                for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
                    if (($x > 0) && ($x <= $totalpages)) {
                        if ($x == $currentpage) $html_pages .= " <b>[$x]</b> ";
                        else $html_pages .= " <a href='?template=1&currentpage=$x'>$x</a> ";
                    }
                }
                if (($currentpage != $totalpages) && ($num_results != 0)) {
                    $nextpage = $currentpage + 1;
                    $html_pages .= " <a href='?template=1&currentpage=$nextpage'>></a> ";
                    $html_pages .= " <a href='?template=1&currentpage=$totalpages'>>></a> ";
                }
                $html_pages .= '</div>';
        	}
            echo $html_pages;
		}                         
        ################################################# Master Project 2
        elseif ($_SESSION['html_template'] == THESIS_TEMPLATE) {	
            while($row = mysql_fetch_assoc($result)) {
                if ((!($row["examiner_grade1"]) || !($row["examiner_grade2"])) && ($row["university"] == "UdS")) {
                    $row["status"] = "open";
                }
                echo colorizeRow($row["status"]);   // Zeilenfarbe festlegen
                echo "<td>" . $row["id"] . "</td>\n";
                echo fill_default($row["name"], "name" ) ;
                echo fill_default($row["projectname"], "project" ) ;
                echo fill_default($row["ex_date"], "ex_date" ) ;
                echo fill_default($row["university"], "university" ) ;
                echo fill_default($row["examiner1"], "examiner1" ) ;
                echo fill_default($row["examiner2"], "examiner2" ) ;
                echo fill_default($row["examiner_grade1"], "examiner_grade1" ) ;
                echo fill_default($row["examiner_grade2"], "examiner_grade2" ) ;
                if ($row["status"]=="open") {
                    echo "<td>open</td>\n"; // Local GRADE							
                    echo "<td>open</td>\n"; // ECTS GRADE							
                    echo '<td>' . $row["credits_master"] . "</td>\n"; // Credit Points
                } elseif ($row["status"]=="grade") {
                    echo "<td>" . $row["local_grade_master"]. "</td>\n";
                    echo "<td>" . $row["ects_grade"] . "</td>\n";
                    echo "<td>" . $row["credits_master"] . "</td>\n";
                } elseif  ($row["status"]=="failed") {
                    echo "<td>" . $row["local_grade_master"]. "</td>\n";
                    echo "<td>" . $row["ects_grade"] . "</td>\n";
                    echo "<td>none</td>\n";
                } else {
                    echo "<td>error</td>\n";
                    echo "<td>error</td>\n";
                    echo "<td>error</td>\n";
                }
                echo fill_default($row["try"], "try" ) ;
                echo fill_default($row["wer"], "wer" ) ;
                echo "<td>" . timestamp_mysql2german($row["datum"]) . "</td>\n";
                print_admin_col($row["id"], $template, $row["author_id"], $row["student_id"]); // Wenn Admin eingeloggt, dann zeige Tools fuer jeden Datensatz an
                echo " </tr>\n";
            }
        } 
        ################################################# Courses 3                
        else if ($_SESSION['html_template'] == COURSES_TEMPLATE){
                while($row = mysql_fetch_assoc($result)) {
                    echo "<tr>\n";
                    echo "<td>" . $row["id"] . "</td>\n";
                    echo fill_default($row["modules_tracks"], "modules_tracks");
                    echo fill_default($row["english_description"], "english_description" ) ;
                    echo fill_default($row["local_description"], "local_description" ) ;
                    echo fill_default($row["code"], "code");			
                    echo fill_default($row["ects"], "ects"); 
                    echo fill_default($row["university"], "university");			
                    echo fill_default($row["semester"], "semester"); 
                    echo "<td>" . $row["wer"] . "</td>\n";
                    echo "<td>" . timestamp_mysql2german($row["datum"]) . "</td>\n";
                    print_admin_col($row["id"], $template, $row["author_id"], $row["id"]);
                    echo "</tr>\n";
                }                
        } else if ($_SESSION['html_template'] == USERS_TEMPLATE) {
            
            while($row = mysql_fetch_assoc($result)) {
                echo "<tr>\n";
                echo "<td>" . $row["id"] . "</td>\n";
                echo fill_default($row["fullname"], "fullname");
                echo fill_default($row["firma"], "firma" ) ;
                echo "<td><a href=\"mailto:" . $row["email"] . "\">" . $row["email"] . "</a></td>\n";
                echo "<td>*********</td>\n";
                echo fill_default($row["status"], "status");
                echo "<td>" . $row["wer"] . "</td>\n";
                echo "<td>" . timestamp_mysql2german($row["datum"]) . "</td>\n";

                // Wenn Admin eingeloggt, dann zeige Tools für jeden Datensatz an
                print_admin_col($row["id"], $template, $row["author_id"], $row["id"]);
                echo "</tr>\n";
            }
            
        } else {
            warn("This template does not exist", 2, 0);
        }

        ob_end_flush();	// Puffer ausgeben

	}

    // Setzt die Farbe fuer den Datensatz
    function set_data_color($art) {
        if ($art==3) return "#000000"; // schwarz
        else return "#0000FF"; // blau = in press, accepted, submitted
    }

    function get_template($mode) {
        global $templateArray;
        if ( $mode>=0 && $mode<sizeof($templateArray) ) {
            return ( $templateArray[$mode] );
        } else {
            warn("Error #20 - Datenmodus existiert nicht / data mode not set",0,0);
            die;
        }
    }


 	/**
 	 * Erzeugt ein Dropdown Menue in Formularen
 	 * @param string 	$formname Namen der Dropdownbox fuer die Weiterverarbeitung
 	 * @param array 	$key_data_array Key=>Data Liste fuer die Anzeige und den Wert der Dropdownboxelemente 
 	 * @param int		$selected_id 	Gibt den bereits vorgewï¿½hlten Eintrag an
 	 * @param string 	$db_value 		Der Wert aus der Datenbank
  	 * @param int		$TabIndex		Index des Feldes zur Tabulatorauswahl
 	 * @return string 	$dropdown 		fertiges Dropdownmenue als String
 	 **/
	function print_dropdown_menu($formname, $key_data_array, $db_value, $TabIndex) {
            if ($formname == "university1") $dropdown = "<select name=\"$formname\" id=\"$formname\" size=\"1\" tabindex=\"$TabIndex\" onChange=\"firstSecondUni()\">\n";
            else $dropdown = "<select name=\"$formname\" id=\"$formname\" size=\"1\"  tabindex=\"$TabIndex\">\n";
            
            if ($formname == "ex_date_years" && !$db_value) { // Check if the form element is the examination year. Then present the current year as selected:
                // I also check if there is a db_value. If there is not, then we are on "new" mode. Only then do the iteration:
                foreach ($key_data_array as $key => $wert) {
                    if ($wert == CURRENT_YEAR) $dropdown .= "<option selected class=\"preselected\" value=\"$key\">$wert</option>\n";
                    else $dropdown .= "<option value=\"$key\">$wert</option>\n";
                }
            } else {
                foreach ($key_data_array as $key => $wert) {
                    if ($db_value == $wert) $dropdown .= "<option selected class=\"preselected\" value=\"$key\">$wert</option>\n";
                    else $dropdown .= "<option value=\"$key\">$wert</option>\n";
                }
            }
            $dropdown .= "</select>";
            return $dropdown;
	}

    // /**
    //  * Experimentelle Funktion zur Korrektur von Formulardaten
    //  *
    //   * @author $Author: Daniel $ 
    // * @version $Revision: 46 $
    // * @date $Date: 2008-10-01 15:11:31 +0200 (Mi, 01 Okt 2008) $
    //   * @param array $blacklist Liste der Variablen, die erneut eingegeben mï¿½ssen
    //  * @return void
    //  **/
    // function reload_form ($blacklist) {

    //    // HTML Template laden
    //    $html_template_array = get_template($_SESSION['html_template']);
    //    $template_size = count($html_template_array);


    //    echo "<form name=\"form1\" method=\"post\" action=\"update.php\">";
    //    echo "<table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">";

    //    for ($i=0; $i<$template_size; $i++) {
    //          $headername = ($_SESSION['sql_table_header'][$i]);
    //        echo "	<tr>\n";
    //        echo "		<td>$html_template_array[$i]</td>\n";
    //        echo "		<td><input type=\"text\" name=\"" .($_SESSION['sql_table_header'][$i]) . "\"  value=\"". $_POST[$headername]  ."\"  size=\"90\" /></td>\n";
    //        echo "	</tr>\n";
    //      }
    //    echo "	<tr>";
    //    echo "		<td><br><br><input type=\"submit\" name=\"Submit\" value=\"Abschicken\" /></td>";
    //    echo "		<td><br><br><input type=\"reset\" name=\"Reset\" value=\"Lï¿½schen\" /></td>";		
    //    echo "		<td><br><br>" .homelink("Abbruch") . "</td>";
    //    echo "	</tr></table>\n";
    //    echo "</form>\n";
    //}

    function last_updated($file) {
        $format="j.n.Y, G:i";
        return(date($format, filemtime($file))); 
    }

    function show_fulltext_search($template) {
    ?>
            <form id="searchform" name="searchform" method="post" action="index.php?template=<?php echo $template; ?>">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Search: <input name="search" type="text" size="20" maxlength="50" value="<?php echo (isset($_POST["search"])) ? $_POST["search"]: "" ; ?>">
                    <input type="submit" name="Submit" value="search">
            </form>	
    <?php
    }

    // Complete column in the "Students" table.
    function greenRedLightTik ($id, $university1, $university2,$user) {
        $result = mysql_query("SELECT complete, university1, university2 FROM amase_students WHERE id=$id;");
        if ($result) {
            $row = mysql_fetch_assoc($result);
        }              
        if ($user["firma"] == $university1) {
            if (strstr($row["complete"], "a")) $firstButton = "<img src=\"./icons/green.png\" title=\"Ready First University\">";
            else $firstButton = "<img class=\"actions\" src=\"./icons/agt_action_success-22.png\" title=\"\" onclick=\"alert_complete($id, 1)\" title=\"Complete the First University\">";
        } else {
            if (strstr($row["complete"], "a")) $firstButton = "<img src=\"./icons/green.png\" title=\"Ready First University\">";
            else $firstButton = "<img src=\"./icons/red.png\" title=\"Not ready First University\">";                
        } 
        if ($user["firma"] == $university2) {
            if (strstr($row["complete"], "b")) $secondButton = "<img src=\"./icons/green.png\" title=\"Ready Second University\">";
            else $secondButton = "<img class=\"actions\" src=\"./icons/agt_action_success-22.png\" onclick=\"alert_complete($id, 2)\" title=\"Complete the Second University\">";                
        } else {
            if (strstr($row["complete"], "b")) $secondButton = "<img src=\"./icons/green.png\" title=\"Ready Second University\">";
            else $secondButton = "<img src=\"./icons/red.png\" title=\"Not ready Second University\">";                 
        }
        if ($user["email"] == "f.soldera@matsci.uni-sb.de") {
            if (strstr($row["complete"], "c")) $thirdButton = "<img src=\"./icons/green.png\" title=\"Ready Tuition Fees\">";
            else $thirdButton = "<img class=\"actions\" src=\"./icons/agt_action_success-22.png\" onclick=\"alert_complete($id, 3)\" title=\"Complete the Tuition Fees\">";                
        } else {
            if (strstr($row["complete"], "c")) $thirdButton = "<img src=\"./icons/green.png\" title=\"Ready Tuition Fees\">";
            else $thirdButton = "<img src=\"./icons/red.png\" title=\"Not ready Tuition Fees\">";                 
        }            
        return array($firstButton, $secondButton, $thirdButton);
    }    
    
    
	function db_query($query) {
 	 	$args  = func_get_args();
	  	$query = array_shift($args);
	  	$query = str_replace("?", "%s", $query);
  		$args  = array_map('mysql_real_escape_string', $args);
  		array_unshift($args,$query);
  		$query = call_user_func_array('sprintf',$args);
  		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
  		return $result;
	}
	
	function is_valid_name ($text, $pflichtfeld=true) {
            setlocale(LC_CTYPE,"de_DE.ISO-8859-15");
            $check = (preg_match("/^[ a-zA-ZßöäüÖÄÜæé ]*/" ,$text));  // #^[A-Za-zÀ-ÖØ-öø-ÿ '\-\.]{0,60}$#
            

            if ($pflichtfeld) {
                $check = ((strlen($text)>1) && $check);
            }
            return $check;
	}
	
	function is_valid_grade ($text, $pflichtfeld=true) {
            $check = (preg_match("#^[\tA-Z0-9\-_.:*]{0,12}$#i" ,$text));
            if ($pflichtfeld) {
                $check = ((strlen($text)>1) && $check);
            }
            return $check;
	}
	
	function is_valid_matrikel ($text, $pflichtfeld=true) {
            $check = (preg_match("#^[\tA-Z0-9\-_.:*]{0,20}$#i" ,$text));
            if ($pflichtfeld) {
                $check = ( (strlen($text)>1) && $check);
            }
            return $check;
	}
	
	
	function is_valid_coursename ($text, $pflichtfeld=true) {
            setlocale(LC_CTYPE,"de_DE.ISO-8859-15");
            $check = $text; //   (preg_match("#^[0-9A-Za-zÀ-ÖØ-öø-ÿ '\-\()[].]{0,2000}$#" ,$text));
            if ($pflichtfeld) {
                $check = ((strlen($text)>1) && $check);
            }
            return $check;
	}

	function get_current_semester () {  // Ermittelt das laufende Semester
		return calc_semester(date("Y"), date("n"));
	}        
        
        function calc_semester ($jahr, $monat) {
            $semester = "";
            $korrfaktor = 0;
            $SS_von = 4;
            $SS_bis = 9;

            if ( (date("n") >= $SS_von) && (date("n") <= $SS_bis )) $semester = "SS";   // vor August und nach Maerz ist Sommersemester
            else {
                $semester = "WS";
                if ( ($monat >= 1) && ($monat <= $SS_von ) ) {  // Wenn wir schon im neuen Jahr sind (nach Silvester) muessen wir Jahr-1 rechnen)
                    $korrfaktor = 1;
                }
                // Wenn Wintersemester, dann zaehlt das letzte Jahr ;)			
            }
            return (($jahr-$korrfaktor). $semester);
	}
	
	// Ermittelt die Anzahl der Studiensemester
	function diff_semester ($semester1, $semester2) {
            $ret = "not set";

            // Studienjahr
            $jahr1 = substr($semester1, 0,4);
            $jahr2 = substr($semester2, 0,4);

            // Prefix vor der Semesterangabe
            $part1 = substr($semester1, 4,2);
            $part2 = substr($semester2, 4,2);


                if ($part1 == $part2) $ret = ($jahr2 - $jahr1)*2+1;
                else {
                    if ($part1 == "SS")	$ret = ($jahr2 - $jahr1)*2+2;
                    else $ret = ($jahr2 - $jahr1)*2;
                }
            return ($ret);
	}

		
	function is_valid_sql_date($date){
    	if (!isset($date) || $date=="")
	    {
        	return false;
    	}
   
    	list($yy,$mm,$dd)=explode("-",$date);
    	if ($dd!="" && $mm!="" && $yy!="")
    	{
        	return checkdate($mm,$dd,$yy);
    	}
   
    	return false;
	}

		
	function is_valid_email($email) {
            if ($email != "") { // RegEx begin
                    $nonascii      = "\x80-\xff"; # Non-ASCII-Chars are not allowed

                    $nqtext        = "[^\\\\$nonascii\015\012\"]";
                    $qchar         = "\\\\[^$nonascii]";

                    $normuser      = '[a-zA-Z0-9][a-zA-Z0-9_.-]*';
                    $quotedstring  = "\"(?:$nqtext|$qchar)+\"";
                    $user_part     = "(?:$normuser|$quotedstring)";

                    $dom_mainpart  = '[a-zA-Z0-9][a-zA-Z0-9._-]*\\.';
                    $dom_subpart   = '(?:[a-zA-Z0-9][a-zA-Z0-9._-]*\\.)*';
                    $dom_tldpart   = '[a-zA-Z]{2,5}';
                    $domain_part   = "$dom_subpart$dom_mainpart$dom_tldpart";

                    $regex         = "$user_part\@$domain_part";
                // RegEx end
                    return (preg_match("/^$regex$/",$email)) ;
            } else {
                    return (true);
            }
	}

	function is_valid_pass($pass) {
		$check = (preg_match("#^[a-zA-Z0-9]{1,30}$#" ,$pass));
		return $check;
	}

	function is_valid_number($num) {
		$check = (preg_match("#^[0-9]+$#" ,$num));
		return $check;
	}
	
	function is_valid_creditpoint($num) {
		$check = (preg_match("#^(\d{1,2}(\.|\,)(5|0)|\d{1,2})$#" ,$num));
		return $check;
	}

	function getpass() {
		$newpass = "";
		$laenge=8;
		$string="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz123456789";

		mt_srand((double)microtime()*1000000);

		for ($i=1; $i <= $laenge; $i++) {
			$newpass .= substr($string, mt_rand(0,strlen($string)-1), 1);
		}

		return $newpass;
	}    

	function convert_grade($grade, $university){

        if($grade == "passed"){
            return $grade;
        }

		if($university=="LTU")
				{ 
				   if( $grade >= '5.0')
				      {
					   return '1.0';
					   }
				  elseif( $grade >= '4.0')
				      {
					   return '1.7';
					   }
				 elseif( $grade >= '3.0')
				      {
					   return '2.3';
					   }
				 elseif( $grade >= '2.0')
				      {
					   return '3.0';
					   }
			     elseif( $grade >= '1.0')
				      {
					   return '3.7';
					   }
				}
				elseif($university=="UPC")
				{ 
				   if( $grade >= '9.8' || $grade >= '9.9' || $grade >= '10.0')
				      {
					   return '1.0';
					   }
				  elseif( $grade >= '9.2' || $grade >= '9.3' || $grade >= '9.4' || $grade >= '9.5' || $grade >= '9.6' || $grade >= '9.7' )
				      {
					   return '1.3';
					   }
				 elseif( $grade >= '8.6' || $grade >= '8.7' || $grade >= '8.8' || $grade >= '8.9' || $grade >= '9.0' || $grade >= '9.1' )
				      {
					   return '1.7';
					   }
				 elseif( $grade >= '8.1' || $grade >= '8.2' || $grade >= '8.3' || $grade >= '8.4' || $grade >= '8.5' || $grade >= '8.6' )
				      {
					   return '2.0';
					   }
			     elseif( $grade >= '7.5' || $grade >= '7.6' || $grade >= '7.7' || $grade >= '7.8' || $grade >= '7.9' || $grade >= '8.0' )
				      {
					   return '2.3';
					   }
				elseif( $grade >= '7.0' || $grade >= '7.1' || $grade >= '7.2' || $grade >= '7.3' || $grade >= '7.4')
				      {
					   return '2.7';
					   }
				elseif( $grade >= '6.5' || $grade >= '6.6' || $grade >= '6.7' || $grade >= '6.8' || $grade >= '6.9')
				      {
					   return '3.0';
					   }
				elseif( $grade >= '5.9' || $grade >= '6.0' || $grade >= '6.1' || $grade >= '6.2' || $grade >= '6.3' || $grade >= '6.4' )
				      {
					   return '3.3';
					   }
				elseif($grade >= '5.3' || $grade >= '5.4' || $grade >= '5.5' || $grade >= '5.6' || $grade >= '5.7' || $grade >= '5.8' )
				      {
					   return '3.7';
					   }
				elseif($grade >= '5.0' || $grade >= '5.1' || $grade >= '5.2' )
				      {
					   return '4.0';
					   }
				}
				elseif($university=="UL")
				{
				if( $grade >= '16.0')
				      {
					   return '1.0';
					   }
				  elseif( $grade >= '15.0')
				      {
					   return '1.3';
					   }
				 elseif( $grade >= '14.0')
				      {
					   return '2.0';
					   }
				 elseif( $grade >= '13.0')
				      {
					   return '2.3';
					   }
			     elseif( $grade >= '12.0')
				      {
					   return '3.0';
					   }
				elseif( $grade >= '11.0')
				      {
					   return '3.3';
					   }
				elseif( $grade >= '10.0')
				      {
					   return '4.0';
					   }
				}
				elseif($university=="UDS")
				{
				if( $grade >= '5.0')
				      {
					   return '5.0';
					   }
				  elseif( $grade >= '4.0')
				      {
					   return '4.0';
					   }
				 elseif( $grade >= '3.7')
				      {
					   return '3.7';
					   }
				 elseif( $grade >= '3.3')
				      {
					   return '3.3';
					   }
			     elseif( $grade >= '3.0')
				      {
					   return '3.0';
					   }
				 elseif( $grade >= '2.7')
				      {
					   return '2.7';
					   }
				 elseif( $grade >= '2.3')
				      {
					   return '2.3';
					   }
			     elseif( $grade >= '2.0')
				      {
					   return '2.0';
					   }
				 elseif( $grade >= '1.7')
				      {
					   return '1.7';
					   }
				 elseif( $grade >= '1.3')
				      {
					   return '1.3';
					   }
			     elseif( $grade >= '1.0')
				      {
					   return '1.0';
					   }
				
				}

	}

?>