<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 18.06.15
 * Time: 12:47
 */

function make_connection(){
    /*
     * AmaseBase Zugansdaten für später
     * static $host = "localhost";
     * static $name = "d004ec9e";
     * static $pass = "2LgjBsowNI0SEHsBQlVS";
     * static $datenbank = "d004ec9e"
     * */
    //$host = "localhost";
    $host = "eusmat.net";
    $user = "d01e8562";
    $password = "QtGUrBcqHYgM2w9L";
    $database = "d01e8562";

    $mysqli = new mysqli($host, $user, $password, $database);

    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    return $mysqli;
}