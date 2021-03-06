<?php
/******************************************************************

    PHP Metamorphic Mutation Engine Web Test
    ----------------------------------------------------
    
    This is a test file, shows a mutation of
    './mutation_chamber/test_file.php'
    in HTML format, run it from a web browser.
    
    By: Dominika Vladimirovskaya // 2009 // Soviet Union
        dominika.vladimirov@comentalo.net
        
******************************************************************/
	
	// Load Mutation engine
	require_once './inc/mutation.class.php';   
	
	// Create a random new mutation.
	$mutation = new Mutation();
	$mutation->setCode( file_get_contents('./mutation_chamber/test_file.php') );


	// Flags
	$mutation->setFrecuency('MODIFY_NUMBERS',500);				// 50% number modifications
	$mutation->setFrecuency('MODIFY_QUOTED_STRINGS',100);		// 10% quoted string modifications
	$mutation->setFrecuency('MODIFY_DOUBLE_QUOTED_STRINGS',100);// 10% quoted string modifications	
	$mutation->setFrecuency('MODIFY_VARS',500);					// 10% vars modification
	$mutation->setFrecuency('MODIFY_FUNCTIONS',1000);			// 100% functions modification 

	// Count time spend in mutation	
	$time_start = microtime(true);
	
	$mutation->mutate();
	//$mutation->test();
	
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	
	
	// Highlight and Show code
	highlight_string($mutation->getCode());
	echo "Mutation in $time seconds\n";
	exit();

?>