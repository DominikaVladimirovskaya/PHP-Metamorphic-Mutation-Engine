<?php
/*

    PHP Metamorphic Mutation Engine Tester
    ----------------------------------------------------------
    
    This scripts does an infinite loop generating different
    mutations of 'example_file.php'
    
    Runs each mutation to check if it runs fine and checks
    the ouput of every mutation against the outuput of the
    original 'example_file.php'
    
    Run it from a command line
    
    Dominika Vladimirovskaya // 2009 // Soviet Union
    dominika.vladimirov@comentalo.net
    
*/

	// Engine load
    require './inc/mutation.class.php';
    
    // Run the script and get the ouput
    system("php ./mutation_chamber/test_file.php > ./mutation_chamber/original.txt");
    $original=implode('',file('./mutation_chamber/original.txt'));

	// Infinite loop    
    while(true){
        
        $aux++;
        echo "Mutation: $aux ...................................";
        
        // Mutation init
        $mutation=new Mutation();
        $mutation->setCode( file_get_contents('./mutation_chamber/test_file.php') );
        $mutation->mutate();
        
        // Run the mutation
        file_put_contents("./mutation_chamber/mutation.php",$mutation->getCode());
        system("php ./mutation_chamber/mutation.php > ./mutation_chamber/mutation.txt");
        $output=implode('',file('./mutation_chamber/mutation.txt'));
        
        // If original ouput is not equal to new output then show an error message
        // and exit.
        if( $original != $output ){
            echo "[EPIC FAIL!] !!\n";
            exit;
        }
        
        // if equal then continue
        echo "[ok]\n";
        
    }

?>