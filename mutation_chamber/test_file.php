<?php

// Test script


	function addition($a,$b){
	
		return $a+$b;
	
	}


	// Numbers
	$a=1;
	$b=2;
	$c=3;
	$d=4;
	$e=5;
	$f=6;
	$g=7;
  
	echo "Total: :".$a+$b+$c+$f+$g."\n";
  
	
	
	// Strings
	$person_a = "Baba Yaga";
	$person_b = 'Dominika Vladimirovskaya';
	
	$message="$person_a has been fragged by $person_b !!";
	
	echo "$message\n";

	// Simple function call
	
	$number= addition( 25, 45 );
	echo "25+45=$number\n";
	
?>
