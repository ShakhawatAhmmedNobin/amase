<?php
    $CSV_DEL = "CSV_DEL";
    $CSV_SEP = "CSV_SEP";
    $CSV_LINE = "CSV_LINE";
    $date = date("d.m.Y");
    if (isset($_GET['template'])) {
    switch ($_GET['template']) {
   ###### STUDENTS        
        case 0:
            $query = "SELECT id, nachname, vorname, gender, university1, matrikel1, university2, matrikel2, universitymaster, start_semester, 
                complete, birth_date, birth_place, country_birth, geburtsort, geburtsland, nationality, email, wer FROM amase_students ORDER BY id";
            $result = mysql_query($query) OR die("Kein Datensatz vorhanden - Error #23 - SQL-Message: " . mysql_error());   // Datenbankanfrage senden
            $csv_output = 
            $CSV_DEL."Id".$CSV_DEL.$CSV_SEP.$CSV_DEL."Last Name".$CSV_DEL.$CSV_SEP.$CSV_DEL."First Name".$CSV_DEL.$CSV_SEP.$CSV_DEL."Gender".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."First University".$CSV_DEL.$CSV_SEP.$CSV_DEL."Fistr Uni Matrikel".$CSV_DEL.$CSV_SEP.$CSV_DEL."Second University".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Second Uni Matrikel".$CSV_DEL.$CSV_SEP.$CSV_DEL."Master Thesis University".$CSV_DEL.$CSV_SEP.$CSV_DEL."Start Semester".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Current Semester".$CSV_DEL.$CSV_SEP.$CSV_DEL."Complete".$CSV_DEL.$CSV_SEP.$CSV_DEL."Date of birth".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Country of birth".$CSV_DEL.$CSV_SEP.$CSV_DEL."Geburtsort".$CSV_DEL.$CSV_SEP.$CSV_DEL."Geburtsland".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Nationality".$CSV_DEL.$CSV_SEP.$CSV_DEL."Email".$CSV_DEL.$CSV_SEP.$CSV_DEL."Wer".$CSV_DEL.$CSV_LINE;
            while($row = mysql_fetch_assoc($result)) {
                $csv_output .= 
                    $CSV_DEL.$row['id'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['nachname'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['vorname'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['gender'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['university1'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['matrikel1'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['university2'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['matrikel2'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['universitymaster'].$CSV_DEL.$CSV_SEP.               
                    $CSV_DEL.$row['start_semester'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['complete'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['birth_date'].$CSV_DEL.$CSV_SEP.   
                    $CSV_DEL.$row['birth_place'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['country_birth'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['geburtsort'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['geburtsland'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['nationality'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['email'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['wer'].$CSV_DEL.$CSV_LINE;
            }
            $file = "Amase_Base_Students_".$date.".csv";
            $csv_text = $csv_output;
            break;
   ###### GRADES            
        case 1:
            $query = "SELECT id, name, university, coursename, status, ex_date, modul, local_grade, ects_grade, credits, try FROM amase_grades ORDER BY id";
            $result = mysql_query($query) OR die("Kein Datensatz vorhanden - Error #23 - SQL-Message: " . mysql_error());   // Datenbankanfrage senden
            $csv_output = 
            $CSV_DEL."Id".$CSV_DEL.$CSV_SEP.$CSV_DEL."Name".$CSV_DEL.$CSV_SEP.$CSV_DEL."University".$CSV_DEL.$CSV_SEP.$CSV_DEL."Coursename".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Status".$CSV_DEL.$CSV_SEP.$CSV_DEL."Exam. Date".$CSV_DEL.$CSV_SEP.$CSV_DEL."Module".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Local Grade".$CSV_DEL.$CSV_SEP.$CSV_DEL."ECTS Grade".$CSV_DEL.$CSV_SEP.$CSV_DEL."Credits".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Try".$CSV_DEL.$CSV_LINE;
            while($row = mysql_fetch_assoc($result)) {
                $csv_output .= 
                    $CSV_DEL.$row['id'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['name'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['university'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['coursename'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['status'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['ex_date'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['modul'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['local_grade'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['ects_grade'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['credits'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['try'].$CSV_DEL.$CSV_LINE;
            }
            $file = "Amase_Base_Grades_".$date.".csv";
            $csv_text = $csv_output;
            break; 
   ###### MASTER THESIS       
        case 2:
            $query = "SELECT id, name, status, projectname, ex_date, university, examiner1, examiner2, examiner_grade1, examiner_grade2, local_grade_master, ects_grade, credits_master, try FROM amase_master ORDER BY id";
            $result = mysql_query($query) OR die("Kein Datensatz vorhanden - Error #23 - SQL-Message: " . mysql_error());   // Datenbankanfrage senden
            $csv_output = 
            $CSV_DEL."Id".$CSV_DEL.$CSV_SEP.$CSV_DEL."Name".$CSV_DEL.$CSV_SEP.$CSV_DEL."Status".$CSV_DEL.$CSV_SEP.$CSV_DEL."Master Thesis name".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Exam. Date".$CSV_DEL.$CSV_SEP.$CSV_DEL."University".$CSV_DEL.$CSV_SEP.$CSV_DEL."Examiner 1".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Examiner 2".$CSV_DEL.$CSV_SEP.$CSV_DEL."Examiner 1 Grade".$CSV_DEL.$CSV_SEP.$CSV_DEL."Examiner 2 Grade".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Local Grade".$CSV_DEL.$CSV_SEP.$CSV_DEL."ECTS Grade".$CSV_DEL.$CSV_SEP.$CSV_DEL."Credits Points".$CSV_DEL.$CSV_SEP.$CSV_DEL."Try".$CSV_DEL.$CSV_LINE;
            while($row = mysql_fetch_assoc($result)) {
                $csv_output .= 
                    $CSV_DEL.$row['id'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['name'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['status'].$CSV_DEL.$CSV_SEP.$CSV_DEL.preg_replace("/[\n\r]/","",$row['projectname']).$CSV_DEL.$CSV_SEP. // evicts the new lines from the projectname
                    $CSV_DEL.$row['ex_date'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['university'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['examiner1'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['examiner2'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['examiner_grade1'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['examiner_grade2'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['local_grade_master'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['ects_grade'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['credits_master'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['try'].$CSV_DEL.$CSV_LINE;
            }
            $file = "Amase_Base_Master_".$date.".csv";
            $csv_text = $csv_output;
            break;  
   ###### COURSES         
        case 3:
            $query = "SELECT id, modules_tracks, english_description, local_description, code, ects, university, semester FROM amase_courses ORDER BY id";
            $result = mysql_query($query) OR die("Kein Datensatz vorhanden - Error #23 - SQL-Message: " . mysql_error());   // Datenbankanfrage senden
            $csv_output = 
                $CSV_DEL."Id".$CSV_DEL.$CSV_SEP.$CSV_DEL."Module/ Track".$CSV_DEL.$CSV_SEP.$CSV_DEL."English Description".$CSV_DEL.$CSV_SEP.$CSV_DEL."Local Description".$CSV_DEL.$CSV_SEP.
                $CSV_DEL."Code".$CSV_DEL.$CSV_SEP.$CSV_DEL."ECTS points".$CSV_DEL.$CSV_SEP.$CSV_DEL."University".$CSV_DEL.$CSV_SEP.
                $CSV_DEL."Semester".$CSV_DEL.$CSV_LINE;
            while($row = mysql_fetch_assoc($result)) {
                $csv_output .= 
                    $CSV_DEL.$row['id'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['modules_tracks'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['english_description'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['local_description'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['code'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['ects'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['university'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['semester'].$CSV_DEL.$CSV_LINE;
            }
            $file = "Amase_Base_Courses_".$date.".csv";
            $csv_text = $csv_output;
            $csv_text = str_replace("'", " ",$csv_text);
            break; 
   ###### USERS             
        case 4:
            $query = "SELECT id, fullname, firma, email, status FROM amase_users ORDER BY id";
            $result = mysql_query($query) OR die("Kein Datensatz vorhanden - Error #23 - SQL-Message: " . mysql_error());   // Datenbankanfrage senden
            $csv_output = 
                $CSV_DEL."Id".$CSV_DEL.$CSV_SEP.
                $CSV_DEL."Fullname".$CSV_DEL.$CSV_SEP.
                $CSV_DEL."University".$CSV_DEL.$CSV_SEP.
                $CSV_DEL."Email".$CSV_DEL.$CSV_SEP.
                $CSV_DEL."Status".$CSV_DEL.$CSV_SEP.
                $CSV_DEL."ECTS points".$CSV_DEL.$CSV_LINE;
            while($row = mysql_fetch_assoc($result)) {
                $csv_output .= 
                    $CSV_DEL.$row['id'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['fullname'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['firma'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['email'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['status'].$CSV_DEL.$CSV_LINE;
            }
            $file = "Amase_Base_Users_".$date.".csv";
            $csv_text = $csv_output;
            $csv_text = str_replace("'", " ",$csv_text);
            break; 
}
    } else {
            $query = "SELECT id, nachname, vorname, gender, university1, matrikel1, university2, matrikel2, universitymaster, start_semester, 
                complete, birth_date, birth_place, country_birth, geburtsort, geburtsland, nationality, email, wer FROM amase_students ORDER BY id";
            $result = mysql_query($query) OR die("Kein Datensatz vorhanden - Error #23 - SQL-Message: " . mysql_error());   // Datenbankanfrage senden
            $csv_output = 
            $CSV_DEL."Id".$CSV_DEL.$CSV_SEP.$CSV_DEL."Last Name".$CSV_DEL.$CSV_SEP.$CSV_DEL."First Name".$CSV_DEL.$CSV_SEP.$CSV_DEL."Gender".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."First University".$CSV_DEL.$CSV_SEP.$CSV_DEL."Fistr Uni Matrikel".$CSV_DEL.$CSV_SEP.$CSV_DEL."Second University".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Second Uni Matrikel".$CSV_DEL.$CSV_SEP.$CSV_DEL."Master Thesis University".$CSV_DEL.$CSV_SEP.$CSV_DEL."Start Semester".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Current Semester".$CSV_DEL.$CSV_SEP.$CSV_DEL."Complete".$CSV_DEL.$CSV_SEP.$CSV_DEL."Date of birth".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Country of birth".$CSV_DEL.$CSV_SEP.$CSV_DEL."Geburtsort".$CSV_DEL.$CSV_SEP.$CSV_DEL."Geburtsland".$CSV_DEL.$CSV_SEP.
            $CSV_DEL."Nationality".$CSV_DEL.$CSV_SEP.$CSV_DEL."Email".$CSV_DEL.$CSV_SEP.$CSV_DEL."Wer".$CSV_DEL.$CSV_LINE;
            while($row = mysql_fetch_assoc($result)) {
                $csv_output .= 
                    $CSV_DEL.$row['id'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['nachname'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['vorname'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['gender'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['university1'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['matrikel1'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['university2'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['matrikel2'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['universitymaster'].$CSV_DEL.$CSV_SEP.               
                    $CSV_DEL.$row['start_semester'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['complete'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['birth_date'].$CSV_DEL.$CSV_SEP.   
                    $CSV_DEL.$row['birth_place'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['country_birth'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['geburtsort'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['geburtsland'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['nationality'].$CSV_DEL.$CSV_SEP.$CSV_DEL.$row['email'].$CSV_DEL.$CSV_SEP.
                    $CSV_DEL.$row['wer'].$CSV_DEL.$CSV_LINE;
            }
            $file = "Amase_Base_Students_".$date.".csv";
            $csv_text = $csv_output;
    }