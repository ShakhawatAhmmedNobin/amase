<?php
	// defined('_VALID_INCLUDE') or die('Direct access not allowed. (edit.common)');
	define('_VALID_INCLUDE', TRUE);
 
	require_once ("./xajax/xajax.inc.php");

	session_name('amasebase');
	session_start();

	$xajax = new xajax("edit.server.php");

	// Schnittstellendefinition für Funktionen aus edit.server.php
	$xajax->registerFunction("ajax_check_name");
	$xajax->registerFunction("ajax_check_number");
	$xajax->registerFunction("ajax_check_matrikel");
	$xajax->registerFunction("ajax_check_grade");
	$xajax->registerFunction("ajax_check_creditpoints");
	$xajax->registerFunction("ajax_check_email");
	$xajax->registerFunction("ajax_check_status");
	$xajax->registerFunction("ajax_check_university");        
?>