<?php
	define('_VALID_INCLUDE', TRUE);
	require_once "modul.inc.php";
	require_once "config.inc.php";
	
	if (!($db_link)) die ($database_noConnectionToServer_Error21 . mysql_error());
	mysql_select_db($datenbank) OR die($database_cannotConnect_Error22 . mysql_error()); // Datenbank auswählen
        
	session_name('amasebase');
	session_start();
	check_login();
	
	html_head(true); // Ajax im Head einschalten 
	
	echo "<body  id=\"blueBG\">";
	
	// Array erzeigen, wenn wir in der Grades  ODER MASTER sind
	if ($_SESSION['html_template'] == GRADES_TEMPLATE 
		|| $_SESSION['html_template'] == THESIS_TEMPLATE) {
	
		// Alle Studenten in ein Array laden
		$result = mysql_query("SELECT id, vorname, nachname, university1, university2, birth_date FROM amase_students WHERE complete != 'abc' ORDER BY nachname") 
			OR die($database_errorSelect_Error24 . mysql_error());
			
		$student_array = $student_id_array = $students_only = array();

		while($row = mysql_fetch_assoc($result)) {
			$student_array[] = $row['nachname'] . " " . $row['vorname'] . " (". $row['university1'] ." - ". $row['university2'] . ")" 
				. " - " . $row['birth_date'];
			$student_id_array[$row['nachname']. ", " . $row['vorname']] = $row['id']; 
			$students_only[] =  $row['nachname'] . ", " . $row['vorname'];
		}
		// Studentenarray in das Globale Dropdownarray für "Grades" UND "MASTER" einfügen, damit man bei NEW und EDIT
		// eine Auswahlliste an Studenten erscheint.
		$global_dropdown_array[1]['name'] = $student_array;
		$global_dropdown_array[2]['name'] = $student_array;  

		// Globales Array mit STUDENT_ID => STUDENTENNAME in der Session erstellen
		$_SESSION['student_id_array'] = $student_id_array;
		$_SESSION['students_only'] = $students_only;
	}		
		
	$mode = $_GET['mode'];

	// Input Variablen übernehmen und säubern
	$tabelle = ( isset($_SESSION['html_template']) && is_clean_digit($_SESSION['html_template']) ) ? ($_SESSION['html_template']) : die("table id not set");

	$html_template_array = get_template($tabelle); // HTML Template laden
	

	if (($_SESSION['html_template'] == STUDENTS_TEMPLATE) && ($_SESSION['user_status'] != "supervisor")) {
		box(false, "Only supervisors are allowed to add/modify student's master data.", 1, $_SESSION['html_template']);
		die();
	}

	if ($mode != "new") { // ID bestimmen ob Zahlenwert oder "new"
		$_SESSION['edit_id'] =  ( isset($_GET['id']) && is_clean_digit($_GET['id']) ) ?  $_GET['id'] : die("ID not set");
	} else {
		$_SESSION['edit_id'] = "new"; // Wenn ein Benutzer kein Supervisor ist und einen Studenten hinzufügen will -> error
	}
	
	$sql_datensatz = [];
	if ($mode == "edit") {
		$query .= " " . $tabellen[$tabelle] . " WHERE id='" . $_SESSION['edit_id'] . "'"; //Mysql Query vorbereiten das den entsprechenden Datensatz aus der Datenbank auswählt
		
		$result = mysql_query($query) OR die($database_queryNoSuccess_Error23 . mysql_error()); //Abfrage starten

		$count = mysql_num_fields($result) OR die($database_noID_Error25 . mysql_error()); // Felder im Query zählen

		$sql_datensatz = mysql_fetch_assoc($result) OR die($database_noIDSet_Error26 . mysql_error()); // Ergebnis der Abfrage in ein Array verpacken
		// Überprüfen ob aktueller Benutzer auch der Eigentümer des Artikels ist
		if (!(is_active_userid($sql_datensatz["author_id"]) || 
			($_SESSION['user_status'] == "supervisor") ||
            (is_active_userid(32) &&
             (($_SESSION['university'] == "LTU") || ($_SESSION['university1'] == "LTU") || ($_SESSION['university2'] == "LTU"))
             )
             )) { 
			die (box(false, "Editieren fremder Datensätze nicht möglich", 1, 0)); 
		}
	}

	##### Datensatz editieren
	if (($mode == "new") || ($mode == "edit")) {
	
		echo "<div id =\"secondColumn\">";
		echo "<div id=\"logo\">";
		show_logo();
		echo "</div>";
		echo "</div>";
								
		$dropdown_array = $global_dropdown_array[$_SESSION['html_template']]; // from config.inc.php

		if ($mode == "edit") unset($dropdown_array['name']); // Dropdownbox für Name deaktivieren, wenn es nur editiert wird
			
		$_SESSION["edit_datensatz"] = $sql_datensatz; // for the update.php
            
		echo "<form name='form1' id='entry_form' action='update.php' method='POST'>\n";
        echo "<div id='messageForm'></div>";
		echo "<table width=\"800\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"texttable\">\n";

		function buildFields($html_template_array, $dont_show_in_form, $dropdown_array, $mode, $sql_datensatz, $parse_date_months) {
            
            $template_size = count($html_template_array);
            for ($i=0; $i<$template_size; $i++) {
                $headername = ($_SESSION['sql_table_header'][$i]); // Wenn das Feld automatisch ausgefüllt wird, dann wird es nicht angezeigt
                
                if (!(in_array($headername, $dont_show_in_form))) { //from modul.inc.php
                    
                    echo "<tr>\n";
                    echo "<td width=\"25%\">";
                    echo str_replace("<br/>", " ", $html_template_array[$i]); // Label des Feldes
                    echo "</td>\n";
                    
                    echo "<td width=\"45%\">";

                    // Dropdownmenü erzeugen 
                    if (isset($dropdown_array[$headername]) || $headername == "country_birth" 
                        || $headername == "coursename" || $headername == "nationality" || $headername == "geburtsland") {
                        
                        $key_data_array = $dropdown_array[$headername];
                        if (($headername == "status") && ($_SESSION['html_template'] == GRADES_TEMPLATE)) {
                            $ajax_status_js = "onchange=\"xajax_ajax_check_status(document.getElementById('status').value, 'status');\"";
                            echo print_dropdown_menu($headername, $key_data_array, $sql_datensatz[$headername], $i, $ajax_status_js);
                        } else if (($headername == "university")) {
                            if (($_SESSION['html_template'] == THESIS_TEMPLATE)) {
                                $ajax_status_js = "onchange=\"xajax_ajax_check_university(document.getElementById('university').value, document.getElementById('status').value, 'university');\"";
                                echo print_dropdown_menu($headername, $key_data_array, $sql_datensatz[$headername], $i, $ajax_status_js);
                            } else if ($_SESSION['html_template'] == GRADES_TEMPLATE) {
                                if ($mode == "new") {
                                    echo "<select id=\"cat1\" name=\"cat1\" onchange=\"checkSelected('cat1')\" size=\"1\">
										    <option value=\"0\"></option>
										    <option value=\"1\">LTU</option>
										    <option value=\"2\">UdS</option>
										    <option value=\"3\">UPC</option>
										    <option value=\"4\">UL</option>
										    </select>";
                                    echo "<div id='div2'></div>";
                                } else if ($mode == "edit") {
                                    echo print_dropdown_menu($headername, $key_data_array, $sql_datensatz[$headername], $i);
                                }
                            } else {
                                echo print_dropdown_menu($headername, $key_data_array, $sql_datensatz[$headername], $i);
                            }
                        } else {
                            // EDIT/ NEW DATES:
                            if ($headername == "birth_date" || $headername == "ex_date") {
                                if ($mode == "new") echo print_dropdown_menu($headername."_years", $key_data_array["years"], $sql_datensatz[$headername], $i) . print_dropdown_menu($headername."_months", $key_data_array["months"], $sql_datensatz[$headername], $i) . print_dropdown_menu($headername."_days", $key_data_array["days"], $sql_datensatz[$headername], $i);
                                else if ($mode == "edit") {
                                    $dateForEdit = explode("-", $sql_datensatz[$headername]);
                                    echo print_dropdown_menu($headername."_years", $key_data_array["years"], $dateForEdit[0], $i) . print_dropdown_menu($headername."_months", $key_data_array["months"], $parse_date_months[$dateForEdit[1]], $i) . print_dropdown_menu($headername."_days", $key_data_array["days"], $dateForEdit[2], $i);
                                }

                            } else if ($headername == "country_birth") {
                                $query = "SELECT country FROM amase_countries_en";
                                $result = mysql_query($query) OR die($database_queryNoSuccess_Error23 . mysql_error());
                                $invCountries = array();
                                $invCountries[] = "";
                                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                    $invCountries[$row["country"]] = $row["country"];
                                }
                                echo print_dropdown_menu($headername, $invCountries, $sql_datensatz[$headername], $i);                                                
                            } else if ($headername == "nationality") {
                                $query = "SELECT nationality FROM amase_nationalities";
                                $result = mysql_query($query) OR die($database_queryNoSuccess_Error23 . mysql_error());
                                $invNationality = array();
                                $invNationality[] = "";
                                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                    $invNationality[$row["nationality"]] = $row["nationality"];
                                }
                                echo print_dropdown_menu($headername, $invNationality, $sql_datensatz[$headername], $i);                                                
                            } else if ($headername == "geburtsland") {
                                $query = "SELECT de FROM amase_countries";
                                $result = mysql_query($query) OR die($database_queryNoSuccess_Error23 . mysql_error());
                                $invLaender = array();
                                $invLaender[] = "";
                                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                    $invLaender[$row["de"]] = $row["de"];
                                }
                                echo print_dropdown_menu($headername, $invLaender, $sql_datensatz[$headername], $i);
                                
                            } else if ($headername == "coursename" && $mode == "new") { // If the column name is the course name then present
                                // both the dropdown box but also an input field for inserting the course name manually
                                echo "<div id=\"div1\"><select id=\"cat2\"  name=\"cat2\" size=\"1\"><option value=\"0\"></option></select>";
                                if ($_SESSION['user_status'] == "supervisor") {
                                    echo "<br/><input type='text' name='language_course' id='language_course' value='' size='100' tabindex=\"$i\" placeholder='Write here only if you want to edit the name of the course'>";
                                }
                            } else if ($headername == "coursename" && $mode == "edit") {
                                echo "<input type=\"text\" name=\"$headername\" id=\"$headername\" value=\"". $sql_datensatz[$headername]  ."\" size=\"100\" tabindex=\"$i\">";
                            } else if ($headername == "modul" && $mode == "edit") {
                                echo print_dropdown_menu($headername, $key_data_array, $sql_datensatz[$headername], $i);
                            }
                            else echo print_dropdown_menu($headername, $key_data_array, $sql_datensatz[$headername], $i);
                        }
                        ####### Dropdownmenü erzeugen

                    ####### Normale Texteingabebox
                    } else {
                        if ($headername == "modul") {
                            //
                        }
                        if (($_SESSION['sql_table_header'][$i] == "projectname")) {
                            echo "<textarea name=\"$headername\" id=\"$headername\" cols=\"40\" rows=\"3\" tabindex=\"$i\">\n" . $sql_datensatz[$headername]. "\n</textarea>\n";
                        } else if (($_SESSION['sql_table_header'][$i]) == "name") { // Wenn das Textfeld "name" ist, dann graue box aus
                            echo "<input class=\"readonly\" type=\"text\" name=\"$headername\" id=\"$headername\" value=\"". $sql_datensatz[$headername]  ."\" size=\"40\" disabled tabindex=\"$i\">";
                        } else {
                            echo "<input type=\"text\" name=\"$headername\" id=\"$headername\" value=\"". $sql_datensatz[$headername]  ."\" size=\"40\" tabindex=\"$i\">";
                        }
                        echo "</td>\n";
                        echo "<td width=\"30%\"></td>\n";
                    }
                    ####### Normale Texteingabebox
                    "</tr>\n";
                }
            }
		}

	    buildFields($html_template_array, $dont_show_in_form, $dropdown_array, $mode, $sql_datensatz, $parse_date_months);

        echo "<tr>\n";
        echo "<td><br><br><input type='submit' value='Submit'/></td>\n";
        echo "<td><br><br><input type=\"button\" value=\"Clear all fields\" onclick=\"clear_form()\">\n";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type=\"button\" value=\"Close window and refresh table\" onclick=\"self.close();window.opener.location.reload(true);\">";
        echo "</td>\n";
        if ($_SESSION['html_template'] == GRADES_TEMPLATE) {
            echo "<td><div id=\"editGradesMessage\">If you don't write anything to the Module or to the Credits field, then the system will automatically take the presented values</div></td>";
        }
        echo "</tr>\n";
        echo "</table>\n";
        echo "</form>\n";
	
    ####### Datensatz löschen
	} elseif ($mode == "delete") {
            // Überprüfen ob aktueller Benutzer auch der Eigentümer des Artikels ist		
            $query = "SELECT * FROM " . $tabellen[$tabelle] . " WHERE id='" . $_SESSION['edit_id'] . "'";
            $result = mysql_query($query) OR die($database_updateNoSuccess_Error27 . mysql_error());
            $sql_datensatz = mysql_fetch_assoc($result);

            if (!( is_active_userid($sql_datensatz["author_id"]) || ($_SESSION['user_status'] == "supervisor")) ) { 
				die (box(false, "Could not delete selected data", 2, 0)); 
			}
            /////////////////////////////////////////////////////////////////////////
            $query = "DELETE FROM " . $tabellen[$tabelle] . " WHERE ID='" . $_SESSION['edit_id'] . "'";
            $result = mysql_query($query) OR die($database_deleteNoSuccess_Error28 . mysql_error());
            /////////////////////////////////////////////////////////////////////////

            log_this ($_SESSION['username'], DELETE, $_SESSION['edit_id'] ,$sql_datensatz);
            print_query_result($result, "Deletion", 2, $_SESSION['html_template']);
    ####### Modus nicht gesetzt
	} else { 
		echo warn("Error: Mode not set", 2, 0);
	}
	
	echo "</body></html>";
?>