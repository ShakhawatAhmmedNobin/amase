<?php

error_reporting(E_ALL);

// Include Berechtigung setzen
define('_VALID_INCLUDE', TRUE);

// Includes aufrufen
 require "../config.inc.php";
 require "../modul.inc.php";
 require "validation.inc.php";

function implode_with_keys($glue, $array, $valwrap='') {
	foreach($array AS $key => $value) {
    	$ret[] = $key."=".$valwrap.$value.$valwrap;
    }
   	return implode($glue, $ret);
 }

function backlink() {
	echo "<br><br><INPUT TYPE=BUTTON VALUE=\"back\" onclick=\"self.location.href='index.php'\">";
}

function echo_if_set($var) {
	echo isset($var) ? $var : "";
}

 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $version; ?> - Registrierung</title>
<style type="text/css" media="screen">
<!--
 @import url("../style.css"); 
-->
</style>
</head>

<body>


<?php
show_logo();


$class_key = " class=\"error\"";

if (isset($_POST['check'])) {

	$validation_list= array();

	(is_valid_name($_POST["name"], true)) ? NULL : $validation_list["name"] 				= $class_key;
	(is_valid_name($_POST["uni1"], true)) ? NULL : $validation_list["uni1"]					= $class_key; 		
	(is_valid_number($_POST["matrikel1"], true)) ? NULL : $validation_list["matrikel1"]		= $class_key;
	(is_valid_name($_POST["uni2"], true)) ? NULL : $validation_list["uni2"] 				= $class_key;
	(is_valid_number($_POST["matrikel2"], true)) ?  NULL : $validation_list["matrikel2"] 	= $class_key;
	(is_valid_sql_date($_POST["birthdate"], true)) ? NULL : $validation_list["birthdate"] 	= $class_key;
	(is_valid_name($_POST["birthplace"], true)) ? NULL : $validation_list["birthplace"] 	= $class_key;
	(is_valid_name($_POST["nationality"], true)) ?NULL :  $validation_list["nationality"] 	= $class_key;
	(is_valid_email($_POST["email1"], false)) ? NULL : $validation_list["email1"] 			= $class_key;
	
	
	if (count($validation_list) == 0) {
		
		$query = "" ;
		$sql_ready_post= array();

//static $fieldnames=array("name", "university1", "matrikel1", "university2", "matrikel2", "birth_date", "birth_place", "nationality", "email");		
//		foreach ($fieldnames as $key) {
		
		$sql_ready_post["name"] = $_POST["name"];
		$sql_ready_post["university1"] = $_POST["uni1"];
		$sql_ready_post["matrikel1"] = $_POST["matrikel1"];
		$sql_ready_post["university2"] = $_POST["uni2"];
		$sql_ready_post["matrikel2"] = $_POST["matrikel2"];
		$sql_ready_post["birth_date"] = $_POST["birthdate"];
		$sql_ready_post["birth_place"] = $_POST["birthplace"];
		$sql_ready_post["nationality"] = $_POST["nationality"];
		$sql_ready_post["email"] = $_POST["email1"];
//		}
		$sql_ready_post = implode_with_keys(", ", $sql_ready_post, "'");
		$query = "INSERT INTO students SET ". $sql_ready_post . ";";	
		
		mysql_select_db($datenbank) OR die("Datenbank nicht vorhanden/erreichbar - Error #22 - SQL-Message: " . mysql_error());
		
		echo "<table width=\"700\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"texttable\">";
		echo "<tr><td>";

		$result = mysql_query($query);
		
		if ($result) {
			echo "Das Einfügen von <b>". $_POST["name"] . "</b> war erfolgreich.<br /><br />";			
			backlink();
		} else {
			echo "Registrierung fehlgeschlagen. Der Student existiert bereits in der Datenbank.<br>Falls Sie Hilfe benötigen, wenden Sie sich bitte an d.henry@mx.uni-saarland.de <br>";
			echo mysql_error();
			backlink();
		}
		echo "</td></tr></table>";          
        die();
	}
}
?>
  <table width="700" border="0" align="center" cellpadding="0" cellspacing="0" class="texttable">
  <tr>
    <td><div align="center"> <span class="ueberschrift_page">Studentenregistrierung</span></div>
      <p>Alle Felder m&uuml;ssen vollst&auml;ndig und den Vorgaben in Klammern entsprechend ausgef&uuml;llt werden.<br />
      </p>
      <form id="form1" name="form1" method="post" action="index.php">
        <table width="440" border="0" align="center" cellpadding="4" cellspacing="0" class="errortable">
          <tr<?php echo isset($validation_list["name"]) ? $validation_list["name"] : "" ; ?>>
            <td width="63%">Name, Vorname (z.B. Henry, Daniel)</td>
            <td width="37%"><input type="text" name="name" value="<?php echo isset($_POST["name"]) ? $_POST["name"]: "" ; ?>"/></td>
          </tr>
          <tr<?php echo isset($validation_list["uni1"]) ? $validation_list["uni1"] : "" ; ?>>
            <td>1. Universit&auml;t (z.B. Nancy) </td>
            <td><input type="text" name="uni1" value="<?php echo isset($_POST["uni1"]) ? $_POST["uni1"]: "" ; ?>"/></td>
          </tr>
		  <tr<?php echo isset($validation_list["matrikel1"]) ? $validation_list["matrikel1"] : "" ; ?>>
            <td>1. Matrikelnummer (z.B. 1234567) </td>
            <td><input type="text" name="matrikel1" value="<?php echo isset($_POST["matrikel1"]) ? $_POST["matrikel1"]: "" ; ?>"/></td>
          </tr>
          <tr<?php echo isset($validation_list["uni2"]) ? $validation_list["uni2"] : "" ; ?>>
            <td>2. Universit&auml;t (z.B. Saarbr&uuml;cken) </td>
            <td><input type="text" name="uni2" value="<?php echo isset($_POST["uni2"]) ? $_POST["uni2"]: "" ; ?>"/></td>
          </tr>
          <tr<?php echo isset($validation_list["matrikel2"]) ? $validation_list["matrikel2"] : "" ; ?>>
            <td>2. Matrikelnummer (z.B. 1234567) </td>
            <td><input type="text" name="matrikel2" value="<?php echo isset($_POST["matrikel2"]) ? $_POST["matrikel2"]: "" ; ?>"/></td>
          </tr>
          <tr<?php echo isset($validation_list["birthdate"]) ? $validation_list["birthdate"] : "" ; ?>>
            <td>Geburtsdatum (Format: YYYY-MM-DD) </td>
            <td><input type="text" name="birthdate" value="<?php echo isset($_POST["birthdate"]) ? $_POST["birthdate"]: "" ; ?>"/></td>
          </tr>
	      <tr<?php echo isset($validation_list["birthplace"]) ? $validation_list["birthplace"] : "" ; ?>>
            <td>Geburtsort (z.B. Berlin) </td>
            <td><input type="text" name="birthplace" value="<?php echo isset($_POST["birthplace"]) ? $_POST["birthplace"]: "" ; ?>"/></td>
          </tr>
	      <tr<?php echo isset($validation_list["nationality"]) ? $validation_list["nationality"] : "" ; ?>>
            <td>Nationalit&auml;t (z.B. spanisch) </td>
            <td><input type="text" name="nationality" value="<?php echo isset($_POST["nationality"]) ? $_POST["nationality"]: "" ; ?>"/></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr<?php echo isset($validation_list["email1"]) ? $validation_list["email1"] : "" ; ?>>
            <td>eMailadresse (z.B. ) </td>
            <td><input type="text" name="email1" value="<?php echo isset($_POST["email1"]) ? $_POST["email1"]: "" ;; ?>"/></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td height="33">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><input name="submit" type="submit" value="Senden" /></td>
            <td><input name="reset" type="reset" value="Zur&uuml;cksetzen" />
                <input type="hidden" name="check" value="yes" /></td>
          </tr>
        </table>
        <p>&nbsp;</p>
      </form>
      <p><?php homelink("zurück zur Datenbank", 0); ?></p>
    <p>&nbsp;</p></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>

</html>
