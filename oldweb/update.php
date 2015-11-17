<?php
    define('_VALID_INCLUDE', TRUE);
    require_once "modul.inc.php";
    require_once "config.inc.php";
    //require "edit.common.php";
    //require_once "./register/validation.inc.php";
	// Return a JSON object with PHP4:
	require_once('JSON.php');
	
    session_name('amasebase');
    session_start();
    check_login();
	
	$json = new Services_JSON();
    
    $json_return_text = "";
    
    function check_post_array($datenbank) {
        //  Überprüft per POST übermittelte Formulare auf Gültigkeit
        
        $post_array_size = count($_POST)-1; // Maximale Indexzahl des Arrays bestimmen

        // Array aus Tabellennamen und gepostetem Inhalt vergleichen
        $array_diff = array_diff_assoc(array_keys($_POST),($_SESSION['sql_table_header'])); 
              
        // POST Daten + dont_show_in_form == tabellen headern - table, submit
        if (!(array() == array_diff(array("table", "Submit"), 
            ((array)array_diff((array)array_merge((array)array_keys((array)$_POST),(array)$dont_show_in_form) , 
            (array)$_SESSION['sql_table_header'])))) &&  is_clean_digit($_POST['table'])) {
            return $json_return_text = "Die übergebenen Daten stimmen nicht mit der Tabellenvorgabe überein. Abbruch.";
        }

        // Element Submit aus dem array löschen; Table in Var übernehmen; Table löschen
        unset($_POST['Submit']);    
        unset($_POST['table']);
        unset ($_POST['datum']);

        #### STATUS:
        if ( isset($_POST['status']) && is_clean_digit($_POST['status']) && ($_POST['status']<=2) ) {
            // failed:
            if ($_POST['status'] == 3) $_POST['credits'] = '0.0';
            
            elseif ($_POST['status'] == 2) {    //Passed
                if ($_SESSION['html_template'] == GRADES_TEMPLATE) {
                    $_POST['local_grade'] = 'passed';       ##### I changed this from none to passed. For clarity.
                    //$_POST['ects_grade'] = 'not set';  
                }
            } elseif ($_POST['status'] == 1) {  // Grade
                
            } elseif ($_POST['status'] == 0) {  // Open
                if ($_SESSION['html_template'] == GRADES_TEMPLATE) {
                    $_POST['local_grade'] = 'open';  ##### I changed this from none to open. For clarity.
                    $_POST['ects_grade'] = 'not set';                    
                } else if ($_SESSION['html_template'] == THESIS_TEMPLATE) {
                    $_POST['local_grade_master'] = 'none';
                    $_POST['ects_grade'] = 'not set';
                }
            } else {
                return $json_return_text = "The given status is not valid!";
            }
        }		

        //  Examination date concatenation
        if (isset($_POST['ex_date_years']) && isset($_POST['ex_date_months']) && isset($_POST['ex_date_days'])) {
            $_POST['ex_date'] = $_POST["ex_date_years"]."-".$_POST["ex_date_months"]."-".$_POST["ex_date_days"];
            unset($_POST["ex_date_years"]);
            unset($_POST["ex_date_months"]);
            unset($_POST["ex_date_days"]);
        }            

        //  Examination email
        if (isset($_POST['email'])) {
            if (is_valid_email($_POST['email'])) ;
            else {
                return $json_return_text = "Student's email is invalid.";
            }
        }           
        
        //  Birth date concatenation
        if (isset($_POST['birth_date_years']) && isset($_POST['birth_date_months']) && isset($_POST['birth_date_days'])) {
            $_POST['birth_date'] = $_POST["birth_date_years"]."-".$_POST["birth_date_months"]."-".$_POST["birth_date_days"];
            unset($_POST["birth_date_years"]);
            unset($_POST["birth_date_months"]);
            unset($_POST["birth_date_days"]);
        }	
        
        if (isset($_POST['projectname'])) $_POST['projectname'] = trim($_POST['projectname']);

        if (isset($_POST['name'])) {    // Ist name gesetzt?
            // Wenn die ID == eine Zahl und template== Noten, dann entferne name. Wieso? Nachträgliche
            // Namensänderungen sind nicht erlaubt, wenn bereits eine Prüfung läuft!
            if ( ($_SESSION['edit_id'] != "new") && ($_SESSION['html_template']==1) ) {
                unset($_POST['name']);
            }else {
                // Ist der Übertragene Name sauber?
                // dem Namensindex wird jetzt wieder ein Name zugewiesen
                if (is_clean_digit($_POST['name'])) {
                    // Die ID wird von NAME nach STUDENT_ID geschoben
                    // Der Name wird mit dem echten Namen aus dem Students Array belegt
                    $_POST['name'] = $_SESSION['students_only'][$_POST['name']];
                } else {
                    return $json_return_text = "Studentname is invalid.";
                }
            }
        }

        ###### GRADES TABLE, COURSES
        if (isset($_POST['cat1'])) {
            $_POST['university'] = $_POST['cat1'];
            unset($_POST['cat1']);
        }
        ob_start(); //avoids data to be displayed
        include 'ajaxdata2.php';
        ob_end_clean();
        if (isset($_POST['cat2'])) {
            mysql_select_db($datenbank) OR die("Database is unreachable/does not exist - Error #22u - SQL-Message: " . mysql_error());
            $query = "SELECT id, modules_tracks, ects FROM amase_courses WHERE local_description = '$cat2'";
            $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
            $invCourses = array();
            while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $ects_grades = $row[ects];
                $modul = $row[modules_tracks];
            }            
            $_POST['coursename'] = $_POST['cat2'];
            if ($_POST['modul'] == 0) $_POST['modul'] = $modul;
            else ;
            if ($_POST['credits'] == "") $_POST['credits'] = $ects_grades;
            else ;
            unset($_POST['cat2']);
        }
    ###### GRADES TABLE, COURSES
        
        if (isset($_POST['vorname'])) { // Ist der Student gültig? (students tabelle)
            if (!(is_valid_name($_POST['vorname'])))  {
                return $json_return_text = "Firstname is invalid.";
            }
        }

        if (isset($_POST['nachname'])) { // Ist der Student gültig? (students tabelle)
            if (!(is_valid_name($_POST['nachname'])))  {
                return $json_return_text = "Family name is invalid.";
            }
        }
        
        if ($_SESSION['html_template'] == USERS_TEMPLATE) {
            if (($_SESSION['edit_id']) == "new") 
            {
                $_POST['passwort'] = md5($_POST['passwort']);
            }
            else
            {
                $query = "SELECT passwort FROM amase_users WHERE email = '" . $_POST['email'] . "'";
                $result = mysql_query($query) OR die("Query nicht erfolgreich - Error #24 - SQL-Message: " . mysql_error());
                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    if ($row['passwort'] != $_POST['passwort']) {
                        $_POST['passwort'] = md5($_POST['passwort']);
                    }
                    else
                    {
                        unset($_POST['passwort']);
                    }
                }              
            }
        }
        
### Template 1 - Grades        
        // Check if the language_course field is submitted
        if (isset($_POST['language_course']) && ($_POST['language_course'] != "")) {
            $_POST['coursename'] = $_POST['language_course'];
            
        }

        // Takes the average of the two grades. This is only for the UdS university.
        if (($_SESSION['html_template'] == THESIS_TEMPLATE) && ($_POST["university"] == 2)) { 
            $i = 0;
            $grade1 = floatval($_POST['examiner_grade1']);
            $grade2 = floatval($_POST['examiner_grade2']);
            if (isset($grade1)) $i++;
            if ($grade2 != 0) $i++;
            $sumGrade = $grade1 + $grade2;
            $_POST["local_grade_master"] = round($sumGrade / $i, 1);
        } else if (($_SESSION['html_template'] == THESIS_TEMPLATE) && ($_POST["university"] != 2)) { // for the rest universities
            $_POST["local_grade_master"] = $_POST['examiner_grade1'];
        }
        
        if (($_SESSION['html_template'] == THESIS_TEMPLATE) && ($_POST["university"] == 1)) { // for the LTU university
            $_POST['examiner_grade2'] = $_POST['examiner_grade1'];
            $_POST['examiner2'] = $_POST['examiner1'];
            $_POST["local_grade_master"] = $_POST['examiner_grade1'];
        }
        
        if ($_SESSION['html_template'] == THESIS_TEMPLATE) 
            $_POST["credits_master"] = 30.0; ###### ALWAYS 30 CREDIT POINTS FOR A MASTER THESIS
        
        foreach ($_POST as $key=>$wert) {
            $_POST[$key] = clean_from_sql($wert); // from config.inc.php
        }
        
        return "";
    }

	function implode_with_keys($glue, $array, $valwrap='') {
    /**
     * Fügt Update Syntax aus einem Array mit key='wert' zusammen (trennzeichen, array, klammerausdruck)
     *
     * @param string $glue Trennzeichen für Werte
     * @param array $array Liste von Werten
     * @param string $valwrap Klammerart um den Ausdruck
     **/
    
	foreach($array AS $key => $value) {
	    $ret[] = $key."=".$valwrap.$value.$valwrap;
	}
	    return implode($glue, $ret);
	}

	function check_id() {
        // Überprüft ob eine ID in die Session übergeben wurde
        if (!(isset($_SESSION['edit_id']))) {
            $json_return_text = "Record ID is invalid. SQL Intrusion? Logged.";
            exit;
        }
	}

    // Call the main function of the file:
    $json_return_text = check_post_array($datenbank);
    if ($json_return_text == "") { 
          mysql_select_db($datenbank) OR die($database_cannotConnect_Error22 . mysql_error());		
          
          mysql_query("SET character_set_connection=utf8");
          mysql_query("SET character_set_client=utf8");
          mysql_query("set character set 'utf8'");
          mysql_query("SET NAMES 'utf8'");
          
          // User aus Datenbank aussuchen
          $query = "SELECT id, fullname FROM amase_users WHERE email='" . $_SESSION['username'] . "';";
          $result = mysql_query($query) OR die("$text fehlgeschlagen. SQL Server offline? - Error #25u - SQL-Message: " . mysql_error());
          $result = mysql_fetch_row($result);
          
          if ($_SESSION['user_status'] != "supervisor") { // Überprüfen ob aktueller Benutzer auch der Eigentümer des Artikels ist
              if ((($_SESSION['edit_id']) != "new") && (!(is_active_userid($result[0])))) { // Debug
                  die ( $json_return_text = "Your are not allowed to change foreign data records"); 
              }
          }		
          $_POST['wer']= $result[1];
          $_POST['author_id'] = $result[0];

          #### ID aus den Dropdowns filtern und zum Werte zuweisen

          if ( $_SESSION['html_template'] == GRADES_TEMPLATE || $_SESSION['html_template'] == THESIS_TEMPLATE ) {
              // Dropdownarray Namensliste mit den Namen füllen, die in edit.php
              // dynamisch ermittelt und in die Session geschrieben wurden

              if (isset($_SESSION['student_id_array'])) {
                  // Wenn ein neuer Datensatz in GRADES eingefügt wird, dann wird die StudentenID bestimmt und NUR dann
                  if (($_SESSION['edit_id'] == "new")) {
                      $_POST['student_id'] = $_SESSION['student_id_array'][$_POST['name']];
                  }      
                  
                  //unset ($_SESSION['student_id_array']);
                  //unset ($_SESSION['student_array']);						
              } else {
                  $json_return_text = "could not load studentlist. aborting. ERROR #26u";
              }
          }
          
          // Nummernzuordnung im Dropdownarray umkehren und wieder Namen zuordnen
          // Für jedes Array werden die Elemente aus der Globalen Vorlage in config.php geladen
          foreach ($is_dropdown_box as $value) {
              if (is_clean_digit($_POST[$value])) {
                  $auswahlliste = $global_dropdown_array[$_SESSION['html_template']][$value]; // Dropdownliste aus der Konfiguration laden
                  $_POST[$value]= $auswahlliste[$_POST[$value]]; // POST-Wert-ID mit dem echten Wert belegen 
              }
          }
          
          if ($_SESSION['html_template'] == GRADES_TEMPLATE) {
              unset($_POST['language_course']);
              $query = "SELECT id FROM amase_courses WHERE local_description='" . $_POST[coursename] . 
                  "' AND modules_tracks = '" . $_POST[modul] . "';";
              $result = mysql_query($query) OR die("$text fehlgeschlagen. SQL Server offline? - Error #25u - SQL-Message: " . mysql_error());
              $result = mysql_fetch_row($result);
              $_POST["course_id"] = $result[0];
          }       
          
          #### ID aus den Dropdowns filtern und zum Werte zuweisen	
          // reload_form(true); // <-- Könnte später dazu dienen Eingaben zu Korrigieren

          $sql_ready_post = implode_with_keys(", ", $_POST, "'");
          $sql_ready_post .= ", datum=NOW()";
          
          ####### Neuen Datensatz anlegen
          if (($_SESSION['edit_id']) == "new") {

              $text = "Insert";
              $query = "INSERT INTO " .  $tabellen[$_SESSION['html_template']] . " SET ". $sql_ready_post . ";";
              mysql_select_db($datenbank) OR die($database_cannotConnect_Error22 . mysql_error());
              $result = mysql_query($query) OR die("$text not successful. SQL Server offline? - Error #25u - SQL-Message: " . mysql_error());
              log_this($_SESSION['username'], INSERT, mysql_insert_id(), $_POST);
              
              ####### Alten Datensatz updaten 		
          } elseif (is_clean_digit($_SESSION['edit_id'])) {
              
              $text = "Update";
              // ### Wird der Datensatz verschoben?
              // if ($newtable == $_SESSION['html_template']) {

              $query = "UPDATE " .  $tabellen[$_SESSION['html_template']] . " SET ". $sql_ready_post . 
                  " WHERE id='" . $_SESSION['edit_id'] . "';";
              mysql_select_db($datenbank) OR die($database_cannotConnect_Error22 . mysql_error());
              $result = mysql_query($query) OR die("$text not successful. SQL Server offline? - Error #25u - SQL-Message: " . mysql_error());

              log_this ($_SESSION['username'], CHANGE, $_SESSION['edit_id'], $_POST, $_SESSION["edit_datensatz"]);
              unset($_SESSION["edit_datensatz"]);

          } else {
              // Wenn edit_id einen unbekannten Wert hat
              
              $json_return_text = "Record-ID Incorrect. Aborting...";
              unset($_SESSION['edit_id']);
              include "logout.php";
              exit;
          }
          
          //unset($_SESSION['edit_id']); // Edit ID löschen aus Sicherheitsgründen      
          
          $json_return_text = $result ? $text . " successful!" : $text . "not successful!";
      
     } else {
        //
     }
       
    $json_return = array();
    $json_return["text"] = $json_return_text;
    $json_return["mode"] = is_clean_digit($_SESSION['edit_id']) ? "edit" : "new";

    echo $json->encode($json_return);//json_encode($json_return);
    
?>