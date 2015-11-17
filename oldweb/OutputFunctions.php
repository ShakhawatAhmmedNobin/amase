<?php
defined('_VALID_INCLUDE') or die('Direct access not allowed.');
define('_VALID_OUTPUT', TRUE); // Output Berechtigung setzen

// we will do our own error handling
error_reporting(0);

// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars){
    // timestamp for the error entry
    $dt = date("d.m.Y H:i s (T)");

    // define an assoc array of error string
    // in reality the only entries we should
    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING and E_USER_NOTICE
    $errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );
    // set of errors for which a var trace will be saved
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
    
    $err = "<errorentry>\n";
    $err .= "\t<datetime>" . $dt . "</datetime>\n";
    $err .= "\t<errornum>" . $errno . "</errornum>\n";
    $err .= "\t<errortype>" . $errortype[$errno] . " [".$errno."]</errortype>\n";
    $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
    $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
    $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

    if (in_array($errno, $user_errors)) {
        $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
    }
    $err .= "</errorentry>\n\n";
    
    // for testing
    // echo $err;

    if (strpos($errmsg,'deprecated') !== false) {
        return;
    }

    // save to the error log, and e-mail me if there is a critical user error
    //error_log($err, 3, "/usr/local/php4/error.log");
    mail("marvin@marv-productions.de", "Amasebase Error", $err);
    
}

//trigger_error("Incorrect parameters, arrays expected", E_USER_ERROR);
$old_error_handler = set_error_handler("userErrorHandler");

$html = '';
$header = 'Content-Type:text/html; charset=UTF-8';
$headOpening = '';
$headClosing = '';
$bodyOpening = '';
$bodyClosing = '';

function type($opening){
	$header = $opening;
}

function head($opening, $closing = ""){
	$headOpening = $headOpening.$opening;
	$headClosing = $closing.$headClosing;
}

function body($opening, $closing = ""){
	$bodyOpening = $bodyOpening.$opening;
	$bodyClosing = $closing.$bodyClosing;
}

function toHTML(){
	return $html = $headOpening.$headClosing.$bodyOpening.$bodyClosing;
}

function finalize(){
	header($header);
	echo toHTML();
}

?>
