<?php
/*
 * INCLUDED FUNCTIONS: getcolor, ajax_check_name, ajax_check_number, ajax_check_email, ajax_check_matrikel, ajax_check_grade, ajax_check_creditpoints, ajax_check_status
 */				
	define('_VALID_INCLUDE', TRUE);
	
	require_once "edit.common.php";
	require_once "modul.inc.php";
	require_once "register/validation.inc.php";
	
	is_logged_in();
	
	// Farben für die Textgestaltung festlegen
	$Color = "#fff";
	$ErrorColor = "#FFD4D4";
	$PassedColor = "#D4FFD4";
	$DisableColor = "#D4D0C8";
	
	function getcolor ($wert) {
		global $Color, $ErrorColor, $PassedColor;
		if ($wert) {
			$Color = $PassedColor;
			$response = "OK";
		} else {
			$Color = $ErrorColor;
			$response = "please correct this field";
		}
		return array($Color, $response);	
	}

	function ajax_check_name ($name, $woher) {
		$name = utf8_decode($name);	
	
		$objResponse = new xajaxResponse();
		list($Color, $response) = getcolor(is_valid_name($name, true));

		$objResponse->addAssign($woher,"style.backgroundColor", $Color);
		$objResponse->addAssign(($woher . "_div"),"innerHTML",$response);
							
		return $objResponse;
	}
	
	function ajax_check_number ($number, $woher) {
		$objResponse = new xajaxResponse();
		list($Color, $response) = getcolor(is_valid_number($number));
		
		$objResponse->addAssign($woher,"style.backgroundColor", $Color);
		$objResponse->addAssign(($woher . "_div"),"innerHTML",$response);
				
		return $objResponse;
	}

	function ajax_check_email ($email, $woher) {
		$email = utf8_decode($email);
		
		$objResponse = new xajaxResponse();
		list($Color, $response) = getcolor(is_valid_email($email));
		
		$objResponse->addAssign($woher,"style.backgroundColor", $Color);
		$objResponse->addAssign(($woher . "_div"),"innerHTML",$response);	
		
		return $objResponse;

	}

	function ajax_check_matrikel ($matrikel, $woher) {
		$objResponse = new xajaxResponse();
		list($Color, $response) = getcolor(is_valid_matrikel($matrikel, true));
		
		$objResponse->addAssign($woher,"style.backgroundColor", $Color);
		$objResponse->addAssign(($woher . "_div"),"innerHTML",$response);
		
		return $objResponse;
	}

	function ajax_check_grade ($grade, $woher) {
		$objResponse = new xajaxResponse();
		list($Color, $response) = getcolor(is_valid_grade($grade, true));
		
		$objResponse->addAssign($woher,"style.backgroundColor", $Color);
		$objResponse->addAssign(($woher . "_div"),"innerHTML",$response);
		
		return $objResponse;
	}
	
	function ajax_check_creditpoints ($points, $woher) {
		$objResponse = new xajaxResponse();
		list($Color, $response) = getcolor(is_valid_creditpoint($points, true));
		
		$objResponse->addAssign($woher,"style.backgroundColor", $Color);
		$objResponse->addAssign(($woher . "_div"),"innerHTML",$response);
		
		return $objResponse;
	}
	
	function ajax_check_status ($status) {
		global $DisableColor;
		$objResponse = new xajaxResponse();
		// 0=> open, 1=> grade, 2=> passed, 3=>failed
		if ($status == 0) { // Farben richtig setzen, bevor es deaktiviert wird
			$objResponse->addAssign("local_grade","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("ects_grade","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("credits","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $DisableColor);
			
			$objResponse->addAssign("local_grade", "disabled", true);
			$objResponse->addAssign("ects_grade", "disabled", true);
			$objResponse->addAssign("credits", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", true);
			$objResponse->addAssign("examiner_grade2", "disabled", true);
			
			$objResponse->addAssign("local_grade_div","innerHTML","");
			$objResponse->addAssign("ects_grade_div","innerHTML","");
			$objResponse->addAssign("credits_div","innerHTML","");
			$objResponse->addAssign("examiner_grade1_div","innerHTML","");
			$objResponse->addAssign("examiner_grade2_div","innerHTML","");
			
		} elseif ($status == 1) { // Grade
			$objResponse->addAssign("local_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("ects_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("credits","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $Color);
			
			$objResponse->addAssign("local_grade", "disabled", false);
			$objResponse->addAssign("ects_grade", "disabled", false);
			$objResponse->addAssign("credits", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner_grade2", "disabled", false);
			
		} elseif ($status == 2) { // passed
			$objResponse->addAssign("local_grade","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("ects_grade","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("credits","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $Color);
			
			$objResponse->addAssign("local_grade", "disabled", true);
			$objResponse->addAssign("ects_grade", "disabled", true);
			$objResponse->addAssign("credits", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner_grade2", "disabled", false);

			$objResponse->addAssign("local_grade_div","innerHTML","");
			$objResponse->addAssign("ects_grade_div","innerHTML","");
		
		} elseif ($status == 3) { // failed
			$objResponse->addAssign("local_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("ects_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("credits","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $Color);
			
			$objResponse->addAssign("local_grade", "disabled", false);
			$objResponse->addAssign("ects_grade", "disabled", false);
			$objResponse->addAssign("credits", "disabled", true);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner_grade2", "disabled", false);

			$objResponse->addAssign("credits_div","innerHTML","");
		}
		return $objResponse;
	}

	function ajax_check_university ($university='', $status='') {
		global $DisableColor;
		$objResponse = new xajaxResponse();
		if ($status == 0) {
			$objResponse->addAssign("ects_grade","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $DisableColor);
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $DisableColor);
                        $objResponse->addAssign("examiner1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner2","style.backgroundColor", $Color);
                        
			$objResponse->addAssign("ects_grade", "disabled", true);
                        $objResponse->addAssign("examiner1", "disabled", false);
                        $objResponse->addAssign("examiner2", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", true);
			$objResponse->addAssign("examiner_grade2", "disabled", true);
			
			$objResponse->addAssign("ects_grade_div","innerHTML","");
			$objResponse->addAssign("examiner_grade1_div","innerHTML","");
			$objResponse->addAssign("examiner_grade2_div","innerHTML","");  
                        
		} elseif (($university == 1 && $status == 1) || ($university == 4 && $status == 1)) { // Grade
			$objResponse->addAssign("ects_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner2","style.backgroundColor", $DisableColor);                                
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $DisableColor);
			
			$objResponse->addAssign("ects_grade", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner2", "disabled", true);                       
			$objResponse->addAssign("examiner_grade2", "disabled", true);
		} elseif (($university == 2 && $status == 1) || ($university == 3 && $status == 1)) { // Grade
			$objResponse->addAssign("ects_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner2","style.backgroundColor", $Color);                                
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $Color);
			
			$objResponse->addAssign("ects_grade", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner2", "disabled", false);                       
			$objResponse->addAssign("examiner_grade2", "disabled", false);
		} elseif (($university == 0 && $status == 1)) { // Grade
			$objResponse->addAssign("ects_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner2","style.backgroundColor", $Color);                                
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $Color);
			
			$objResponse->addAssign("ects_grade", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner2", "disabled", false);                       
			$objResponse->addAssign("examiner_grade2", "disabled", false);
		} elseif (($university == 1 && $status == 2) || ($university == 4 && $status == 2)) { // failed
			$objResponse->addAssign("ects_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner2","style.backgroundColor", $DisableColor);                        
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $DisableColor);
			
			$objResponse->addAssign("ects_grade", "disabled", false);
                        $objResponse->addAssign("examiner2", "disabled", true);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner_grade2", "disabled", true);

			$objResponse->addAssign("credits_div","innerHTML","");
		} elseif (($university == 2 && $status == 2) || ($university == 3 && $status == 2)) { // failed
			$objResponse->addAssign("ects_grade","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner_grade1","style.backgroundColor", $Color);
			$objResponse->addAssign("examiner2","style.backgroundColor", $Color);                        
			$objResponse->addAssign("examiner_grade2","style.backgroundColor", $Color);
			
			$objResponse->addAssign("ects_grade", "disabled", false);
                        $objResponse->addAssign("examiner2", "disabled", false);
			$objResponse->addAssign("examiner_grade1", "disabled", false);
			$objResponse->addAssign("examiner_grade2", "disabled", false);

			$objResponse->addAssign("credits_div","innerHTML","");
		} 
               
		return $objResponse;
	}
        
	$xajax->processRequests();
?>