<?php
    define('_VALID_INCLUDE', TRUE);
    require('modul.inc.php');
    require_once "config.inc.php";
    
    session_name('amasebase');
    session_start();
    check_login();
    html_head(false);   // HTML HEADER SCHREIBEN
    if ( isset($_SESSION['html_template']) )  {
        $html_template = $_SESSION['html_template'];
    } else {
        box(false, "Datenmaske fehlerhaft / datatype missmatch", false);
        exit;
    }
    $template = $_GET["template"];
    global $infoTemplates;
    echo "<body class=\"bgForOutput\">";
    echo "<div id=\"infoPage\">" . $infoTemplates[$template] . "</div>";
    echo "</body>";