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
	$mutation->setFlag('MODIFY_VARS', true);

	// Count time spend in mutation	
	$time_start = microtime(true);
	$mutation->mutate();
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	
	
	// Highlight and Show code
	highlight_string($mutation->getCode());
	
	echo "Mutation in $time seconds\n";
	
	exit();

?>