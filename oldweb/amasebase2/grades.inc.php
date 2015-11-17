<?php
/*
 * Created on 07.02.2008
 *
 * @author 	$Author: Daniel $ 
 * @version $Revision: 38 $
 * @date 	$Date: 2008-05-25 17:02:42 +0200 (So, 25 Mai 2008) $
 * @id 		$Id: grades.inc.php 38 2008-05-25 15:02:42Z Daniel $
 */
  
  	static $inpl2norm = array("20"=>1, "19"=>0.95, "18"=>0.9, "17"=>0.85, "16"=>0.8, "15"=>0.75, "14"=>0.70, "13"=>0.65, "12"=>0.60, "11"=>0.55, "10"=>0.50, "9"=>0.45, "8"=>0.4, "7"=>0.35, "6"=>0.3, "5"=>0.25, "4"=>0.2, "3"=>0.15, "2"=>0.5, "1"=>0);
	static $uds2norm = array("1"=>1, "1.3"=>0.95, "1.7"=>0.89, "2"=>0.83, "2.3"=>0.78, "2.7"=>0.72, "3"=>0.67, "3.3"=>0.61, "3.7"=>0.56, "4"=>0.5); 	
	static $ltu2norm = array("5"=>1, "4"=>0.8, "3"=>0.6, "G"=>0.5); 	
	static $upc2norm = array("10"=>1, "9.5"=>0.95, "9"=>0.9, "8.5"=>0.85, "8"=>0.8, "7.5"=>0.75, "7"=>0.7, "6.5"=>0.65, "6"=>0.6, "5.5"=>0.55, "5"=>0.5); 	
    
  	function isValidGrade ($uni, $grade) {
		global $inpl2norm, $uds2norm, $ltu2norm, $upc2norm;
		
		if ( !is_string($uni) ) { die ("isValidGrade: datatypes missmatch (uni) - ". gettype($uni) ); }
		if ( !is_float($grade) ) { die ("isValidGrade: datatypes missmatch (grade) - ". gettype($grade)); }
		
		// echo "<br>grade: $grade - $uni<br>";
		
		switch ($uni) {
			case "UL":
				if (array_key_exists("$grade", $inpl2norm)) {
					return true;
				}
				break;
		
	 		case "UdS":
				if (array_key_exists("$grade", $uds2norm)) {
					return true;
				}
				break;
				
	 		case "LTU":
				if (array_key_exists("$grade", $ltu2norm)) {
					return true;
				}
				break;	 		
						
	 		case "UPC":
				if (array_key_exists("$grade", $upc2norm)) {
					return true;
				}
				break;	
		}
		return (false);  		
  	}
 
 	function gradeConvert ($uni, $grade) {
		global $inpl2norm, $uds2norm, $ltu2norm, $upc2norm;
		
		if ( !is_string($uni) ) { die ("gradeConvert: datatypes missmatch (uni) - ". gettype($uni) ); }
		if ( !is_float($grade) ) { die ("gradeConvert: datatypes missmatch (grade) - ". gettype($grade)); }
		
		switch ($uni) {
			case "UL":
				if (isValidGrade($uni, $grade)) { return $inpl2norm["$grade"]; }
				break;
		
	 		case "UdS":
				if (isValidGrade($uni, $grade)) { return $uds2norm["$grade"]; }
				break;
				
	 		case "LTU":
				if (isValidGrade($uni, $grade)) { return $ltu2norm["$grade"]; }
				break;	 		
						
	 		case "UPC":
				if (isValidGrade($uni, $grade)) { return $upc2norm["$grade"]; }
				break;	
				
			default:
				die('gradeConvert: university unknown - ' . $uni . '('. gettype($uni) . ')');
				break;	
		}
		die('gradeConvert: invalid grade - ' . $grade . '('. gettype($grade) . ')') ;
 	}
?>
