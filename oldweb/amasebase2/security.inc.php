<?php
require_once "config.inc.php";
/**
 * Security Modul für Logging
 *
 * @author 	$Author: Daniel $
 * @version $Revision: 44 $
 * @version	$Id: security.inc.php 44 2008-05-30 12:12:07Z Daniel $
 **/


/**
 * Logging für Benutzeraktionen
 *
 * @param	$username		STRING	Benutzername
 * @param	$action_id		INT		Aktion (0=changed, 1=new, 2=deleted)
 * @param	$datensatz		INT		Datensatznummer
 * @param 	$daten			ARRAY	Array mit neuen Daten
 * @param 	$original		ARRAY	Array mit originalen Daten
 **/
function log_this($username, $action_id, $datensatz, $daten, $original=array()) {
	global $tabellen, $db_link, $datenbank;

	$action = array("change", "new", "delete");
	$differenz = (array_diff_assoc($daten, $original));

	$ddiff = "";
	$faction = $action[$action_id];
	foreach ($differenz as $key=>$value) {
		$ddiff .= ($key . ": ");
			
		if ( array_key_exists($key, $original) ) { $ddiff .= $original[$key] . "->";}
		$ddiff .= ( $value  . ";; ");
	}

	if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21se - SQL-Message: ". mysql_error()); }
	$query = "INSERT INTO `amase_log` ( `id` , `datum` , `name` , `ip` , `datensatz` , `dtabelle` , `dstatus` , `ddiff` ) VALUES ('', NOW( ) , '" . $_SESSION['fullname'] . "' , '" .$_SERVER['REMOTE_ADDR'] . "' , '" . $datensatz . "', '". $tabellen[$_SESSION['html_template']] ."', '". $faction ."', '". $ddiff ."');";
        mysql_select_db($datenbank) OR die("Database is unreachable/does not exist - Error #22se - SQL-Message: " . mysql_error());
	mysql_query($query) OR die("SQL Server offline? - Error #25se - SQL-Message: " . mysql_error());
}


/**
 * Logging für Benutzeranmeldung
 *
 * @param	$username	STRING	Benutzername
 * @param	$username	INT		Status (1=Login, 0=Logout)
 **/
function log_user($status=1) {
	global $db_link, $datenbank;
	
	$ustatus = ( ($status==0) ? "logoff" : "login" ); 
	if (!($db_link)) { die ("Es konnte keine Verbindung zum Server aufgebaut werden - Error #21se - SQL-Message: ". mysql_error()); }
	$query = "INSERT INTO `amase_userlog` ( `id` , `datum` , `name` , `uip` , `ustatus`) VALUES ('', NOW( ) , '" . $_SESSION['fullname'] . "' , '" .$_SERVER['REMOTE_ADDR'] . "' , '". $ustatus ."');";
	mysql_select_db($datenbank) OR die("Database is unreachable/does not exist - Error #22se - SQL-Message: " . mysql_error());
	mysql_query($query) OR die("SQL Server offline? - Error #25se - SQL-Message: " . mysql_error());
}


/**
 * Logging für Benutzeranmeldung
 *
 * @param	$username	STRING	Benutzername
 * @param	$username	INT		Status (1=Login, 0=Logout)
 **/
function user_logout() {
	session_name("amasebase");
	session_start();
	log_user(0);
	$_SESSION = array();
	 
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();

	$hostname = $_SERVER['HTTP_HOST'];
	$path = dirname($_SERVER['PHP_SELF']);
	$ProtocolPrefix = isset($_SERVER['HTTPS']) ? "https://" : "http://";

	header('Location: '. $ProtocolPrefix.$hostname.($path == '/' ? '' : $path).'/index.php');
}
?>