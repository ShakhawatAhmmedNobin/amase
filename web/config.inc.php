<?php
/**
 * General purpose file that contains all the commonly used variables from the system
 * Here you can find the drop-down boxes that the system uses, the template names, the template columns, 
 * the search fields, the text for the infoboxes etc...
 */
	//error_reporting(E_ALL ^ E_DEPRECATED);
    require("db.inc.php");
	
	/* System Messages: */
	$database_noConnectionToServer_Error21 = "Es konnte keine Verbindung zum Server aufgebaut werden - Error #21 - SQL-Message: ";
	$database_cannotConnect_Error22 = "Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: ";
	$database_queryNoSuccess_Error23 = "Query nicht erfolgreich - Error #23 - SQL-Message: ";
	$database_errorSelect_Error24 = "SELECT fehlgeschlagen - Error #24 - SQL-Message: ";
	$database_noID_Error25 = "no proper ID set - Error #25 - SQL-Message: ";
	$database_noIDSet_Error26 = "ID not set - Error #26 - SQL-Message: ";
	$database_updateNoSuccess_Error27 = "Update not successful. SQL Server offline? - Error #27 - SQL-Message: ";
	$database_deleteNoSuccess_Error28 = "Delete not successful. SQL Server offline? - Error #28 - SQL-Message: ";
	/* System Messages: */
	
   $db_link = mysql_connect($host, $name, $pass);  // link to the database
   mysql_query("SET NAMES 'utf8'");
   mysql_query("SET CHARACTER SET utf8 ");
   mysql_set_charset('utf8',$db_link);
    
    ### Dont's   
    // Elements that should not be presented in the Tables
    $hint_col_element = array("Link", "Author_ID", "User_ID", "Status", "Geburtsort", "Geburtsland", 
        "course_id", "LanguageCourse", "passwort");
    
    // Elements that should not be presented in the new/ edit forms 
    $dont_show_in_form = array("id", "wer", "datum", "author_id", "student_id", "Author_ID", "User_ID", "study_semester", 
        "complete", "local_grade_master", "credits_master", "course_id");

    $no_col_links = array ("Nummer");   // Columns that should be no link

    $admin_only_col = array();  // columns only for the admins
    ### Dont's  

    $pfad = "/amasebase2";  // Unterverzeichnis des Programms auf dem Webserver z.b. /amasebase

    $ProtocolPrefix = isset($_SERVER['HTTPS']) ? "https://" : "http://";    // Protokoll bestimmen ob HTTPS oder HTTP

    $InstallPath = $ProtocolPrefix . $_SERVER['HTTP_HOST'] . $pfad; //Pfadangabe
    
    define('CURRENT_YEAR', date("Y")); // define the current year. Used by the application
    define('STUDENTS_TEMPLATE', 0);
    define('GRADES_TEMPLATE', 1); 
    define('THESIS_TEMPLATE', 2);
    define('COURSES_TEMPLATE', 3);
    define('USERS_TEMPLATE', 4);
    
    static $version = "AmaseBASE System 2015. <br/>Design & Development: Daniel Henry, Christos Monogios, Marvin Hofmann.";
    $footnote = $version;
    
    $universities = array(
        array("UdS", 1, "ASC"),
        array("LTU", 2, "DESC"),
        array("UPC", 3, "DESC"),
        array("UL", 4, "DESC")
    );

    #### Templates
    static $template_names= array ("Students", "Grades", "Master's Thesis", "Courses", "Users");
    static $tabellen = array("amase_students", "amase_grades", "amase_master", "amase_courses", "amase_users");
    
    $template0 = array("ID", "Last Name", "First<br/>Name", "Gender", "First<br/>Uni", "First<br/>Stud.-ID", "Second<br/>Uni", "Second<br/>Stud.-ID", 
                       "Master<br/>Thesis Uni","Start<br/>Semester", "Complete", "Term", "Date of<br/>birth", "Birthplace", "Country of<br/>birth", 
                       "Geburtsort", "Geburtsland", "Nationality", "E-Mail", "Added<br/>by", "Author_ID" ,"Time<br/>added");
    $template1 = array("ID", "Student", "University", "Course Name", "course_id", "Status", "User_ID", "Exam.<br/>Date", 
                       "Module", "Local<br/>Grade", "Ects<br/>Grade", "Credits", "Attempt", "Added by", "Time<br/>Added", "Author_ID");
    $template2 = array("ID", "Student",  "Status", "User_ID", "Title of Thesis", "Exam.<br/>Date", "University", "1st examiner", "2nd examiner", 
                       "1st grade", "2nd grade", "Local<br/>Grade", "Ects<br>Grade", "Credits", "Attempt", "Author", "Author_ID", "Time<br/>added");
    $template3 = array("ID", "Modules/Tracks",  "English<br/>Description", "Local<br/>Description", "Code", "ETCS", "University", "Semester", 
                       "Added by", "Author_ID", "Time<br/>Added");        
    $template4 = array("ID", "Name", "Institution", "E-Mail", "Passwort", "User-Status", "Added by", "Author_ID", "Time<br/>Added");
    $templateArray = array($template0, $template1, $template2, $template3, $template4);		
    #### Templates

    // Suchfelder f�r Volltextsuche in Tabellen. For template 0, 1, 2, 3
    $search_string = array( "id, vorname, nachname, country_birth, birth_place, email", "id, name, coursename, modul, university" , 
                            "id, name, projectname, wer", "english_description, local_description, code, university",
                            "fullname, firma, status");
    
    // Texts used for the Info button on the upper left side of each template. Key represents the template , value the html text
    static $infoTemplates = array(
        "0" => "<h1>STUDENTS TABLE</h1>On the student table you can see the information about the AMASE students. 
                New characteristics of this page are: <ul><li><strong>Complete: </strong> By clicking on the green 
                check button, you are able to confirm that the specific student succesfully completed his or her studies 
                at your university. This is a very important information for the general committee of AMASE. The first 
                button is for the first university that the student attends, the second for the second university and 
                the third for the tuition fees that the student has to pay.</li><li><img src=\"icons/info.png\" /><strong> 
                Student Information</strong>: With the new version of the system, you are able to click on this image 
                and see all the information about the student gathered together. The sucessfully passed courses are 
                presented and also the master thesis, if the user sucessfully wrote one.</li></ul>",
        "1" => "<h1>GRADES TABLE</h1>On the grades table you can find all the submitted exams that the students participated
                in. 50 lines per page are presented, so that the page loads faster. New characteristics of this page 
                are: <ul><li><strong>Automated course insertion: </strong> If you click on the \"new entry\" button 
                you have to pick the student you want, then give the university that he/ she studies at and then based 
                on the university a list with the appropriate courses is presented. If you choose a course you are going 
                to see in which module/ track it belongs and also how many credit points this course has. If you dont 
                write anything in the Module and Credits input, the system is going to use the already presented values. 
                If you want to change those values then you have to also change the Module and/ or the Credits input.</li></ul>",
        "2" => "<h1>MASTER'S THESIS TABLE</h1>On the master thesis' table you can find all the registered  master thesis 
                for the AMASE students. New characteristics of this page are: <ul><li><strong>Examiner's grade:</strong> 
                If you are from LTU, UL or UPC university, you only have to fill in the name and the grade of the first 
                examiner</li></ul>",
        "3" => "<h1>COURSES TABLE</h1>This is a new table and contains all the courses for all the AMASE universities. 
                You are able to use the \"new entry\" button and enter new courses for your university. Those courses are 
                going to be used for the list that you use when you are entering new grades for your students to the 
                GRADES table",
        "4" => "<h1>USERS TABLE</h1>A table where you can see all the users that have access to our system. You can click 
                on the email address of one user and directly send him an email.");    
    
    static $StandardOrdnung = "datum";  // Spalte nach der per Default sortiert wird

################# Dropdownelemente
    // Drop-down box elements
    static $is_dropdown_box = array("name", "university", "local_grade", "university1", "university2", "try", "gender", "status", "country_birth", 
                                    "birth_date", "start_semester", "modul", "coursename", "universitymaster", "nationality", "semester", 
                                    "modules_tracks", "geburtsland");
    
    static $parse_try = array ("1st attempt", "2nd attempt", "3rd attempt", "Free attempt"); // attempt for courses
    static $parse_try_master = array ("1st attempt", "2nd attempt", "3rd attempt"); // attempt for master thesis


    //for new exam regulations 2011 - added by manpreet
    static $parse_modul_new = array ("", "Module 1", "Module 2", "Module 3", "Module 4", "Module I: Structure & Properties", "Module II: Materials Characterization", 
            "Module III: Materials Engineering & Processing Technologies", 
            "Track 1: Advanced Metallic Materials - Design, characterization and processing", 
            "Track 2: Polymers and Composites - Modelling, processing & tailored properties",
            "Track 3: High Performing Surfaces - Coating, structuring & functionalization", 
            "Track 4: Materials Engineering and Manufacturing Technologies", 
            "Track 5: Bio/Nanomaterials (including special applications)",
            "Special courses", "Language", "Zusaetzliche Leistung", "Freiwillige Zusatzkurse",
            "Voluntary Courses"); // added by manpreet
	
	
    // All the modules/ tracks/ languages
    static $parse_modul = array ("","Module 1", "Module 2", "Module 3", "Module 4", "Language", "Special courses", 
            "Module I: Structure & Properties", "Module II: Materials Characterization", 
            "Module III: Materials Engineering & Processing Technologies", 
            "Track 1: Advanced Metallic Materials - Design, characterization and processing", 
            "Track 2: Polymers and Composites - Modelling, processing & tailored properties",
            "Track 3: High Performing Surfaces - Coating, structuring & functionalization", 
            "Track 4: Materials Engineering and Manufacturing Technologies", 
            "Track 5: Bio/Nanomaterials (including special applications)",
			"Zusaetzliche Leistung", "Freiwillige Zusatzkurse","Voluntary Courses");
    
        // Notendruck f�r Master-Zeugnis
    static $PrintTranscriptCourses = array ("Module 1", "Module 2", "Module 3", "Module 4", "Language", "Special courses", "Module I", "Module II", 
            "Module III", "Track 1: Advanced Metallic Materials - Design, characterization and processing", 
            "Track 2: Polymers and Composites - Modelling, processing & tailored properties",
            "Track 3: High Performing Surfaces - Coating, structuring & functionalization", 
            "Track 4: Materials Engineering and Manufacturing Technologies", 
            "Track 5: Bio/Nanomaterials (including special applications)");   
    
    // old variable with the modules
    static $parse_modules_tracks = array("Module I. Structure & Properties", 
            "Module II. Materials Characterization", 
            "Module III. Materials Engineering & Processing Technologies", 
            "Track 1: Advanced Metallic Materials - Design, characterization and processing", 
            "Track 2: Polymers and Composites - Modelling, processing & tailored properties", 
            "Track 3: High Performing Surfaces - Coating, structuring & functionalization");
    
    static $parse_uni = array('', "LTU", "UdS", "UPC", "UL");
    static $parse_gender = array('' => '', "male", "female");
    static $parse_status = array("open", "grade", "passed", "failed"); // status of a grade
    static $parse_status_master = array("open", "grade", "failed"); // status of a master thesis
    
    static $parse_ects_grade = array("not set"=>"not set", "A"=>"A", "B"=>"B", "C"=>"C", "D"=>"D", "E"=>"E", "F"=>"F", "FX"=>"FX"); // ECTS note
    static $parse_start_semester = array("2005WS", "2006WS", "2007WS", "2008WS", "2009WS", "2010WS", "2011WS", "2012WS", "2013WS", "2014WS",
                                         "2015WS", "2016WS", "2017WS", "2018WS", "2019WS", "2020WS", "2021WS", "2022WS", "2023WS", "2024WS",
                                         "2025WS", "2026WS", "2027WS", "2028WS", "2029WS", "2030WS", "2031WS", "2032WS", "2033WS", "2034WS",);
    static $parse_semester = array("1", "2", "3");
    
    static $parse_date_months = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', 
                                      '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');
    
    static $parse_date_days = array();
    for ($i=1; $i<=31; $i++) {
        if($i < 10) $parse_date_days["0$i"] = "0$i";
        else $parse_date_days["$i"] = "$i";
    }
    
    static $parse_date_years = array();
    for ($i=1960; $i<=2020; $i++) {
        $parse_date_years["$i"] = "$i";
    }
    
    static $parse_date_years_ex = array();
    for ($i=2006; $i<=2035; $i++) {
        $parse_date_years_ex["$i"] = "$i";
    }
    
    static $status_users = array('user', 'supervisor');
    
    $parse_date = array("days" => $parse_date_days, "months" => $parse_date_months, "years" => $parse_date_years);
    $parse_date_ex = array("days" => $parse_date_days, "months" => $parse_date_months, "years" => $parse_date_years_ex);
    
    // Dropdowns in Students
    $dropdown_array_0 = array("start_semester" => $parse_start_semester, 
                              "university1"=>$parse_uni, 
                              "university2"=>$parse_uni, 
                              "universitymaster" => $parse_uni, 
                              "gender"=>$parse_gender, 
                              "birth_date" => $parse_date);
    
    // Dropdowns in Grades  
    $dropdown_array_1 = array("university"=>$parse_uni, 
                              "try"=>$parse_try, 
                              "status"=>$parse_status, 
                              "ects_grade"=>$parse_ects_grade,
                              "ex_date" => $parse_date_ex, 
                              "modul" => $parse_modul);

    // Dropdowns in Master Thesis
    $dropdown_array_2 = array("status"=>$parse_status_master, "university"=>$parse_uni, 
        "ects_grade"=>$parse_ects_grade, "try"=>$parse_try_master, "ex_date" => $parse_date_ex);

    // Dropdowns in Courses
    $dropdown_array_3 = array("modules_tracks" => $parse_modul, "university" => $parse_uni, "semester" => $parse_semester);
    
    // Dropdowns in Users
    $dropdown_array_4 = array("status" => $status_users);
    
    $global_dropdown_array = array(0=>$dropdown_array_0, 1=>$dropdown_array_1, 
        2=>$dropdown_array_2, 3=>$dropdown_array_3, 4=>$dropdown_array_4);
######### Dropdownelemente
   
    $query = "SELECT * FROM ";  // Default Query

    // Konstanten f�r Logging-Funktion (log_this)
    define('CHANGE', 0);
    define('INSERT', 1);
    define('DELETE', 2);
    
    static $supervisors = array("LTU" => "bertil.carlsson@ltu.se", "UdS" => "amase@ps-ntf.uni-saarland.de ", 
        "UL" => "Dominique.Stirnemann@eeigm.inpl-nancy.fr", "UPC" => "carlos.oriol@upc.edu",);

?>