<?php
/*------------------------------------------------------------
|
|	PHP Mutation Engine
|	
|	By Dominika Vladimirovskaya
|	dominika.vladimirov(At)comentalo.net
|	October // 2009 // Soviet Union
|	
--------------------------------------------------------------*/

class Mutation{
	
	var $code;
	var $cursor;
	var $status;
	var $php;
	var $mutation_flags;

	/* Constructor */	
	function Mutation(){
   
		// Php parser 
		$this->status['in_php'] 			= false;
		$this->status['in_var'] 			= false;
		$this->status['in_comment_line']	= false;
		$this->status['in_comment']			= false;
		$this->status['in_quotes']			= false;
		$this->status['in_double_quotes']	= false;
		$this->status['structs']			= array();
		$this->status['slashed']			= false;
		$this->status['in_comment']			= false;
		$this->status['in_number']			= false;
		$this->status['character']			= '';

		// Php related
		$this->php['reserved_vars']	= array('this','_get','vars','_post','_request','cookies','server','globals');
		$this->php['word_separators']	= array("\n","\t",'"',"'",'(',')',' ','*','/','-','+','%','&','=','{','}','-','\\','<','>',';','.');

	}

	/*-----------------------------------------------------
	| function setCode($code)
	|
	| Set the code to be mutated
	------------------------------------------------------*/
	function setCode( $code ){
		$this->code=$code;
	}
	
	/*-----------------------------------------------------
	| function getCode($code)
	|
	| Returns the mutated code
	------------------------------------------------------*/
	function getCode(){
		return $this->code;	
	}

	/*-----------------------------------------------------
	| function setCursor(cursor position)
	|
	| Set's the parser internal cursor position
	------------------------------------------------------*/
	function cursorSetPosition($pos){
		$this->cursor=$pos;
	}
	
	
	/*-----------------------------------------------------
	| Function setFrecuency($flag,$frecuency)
	|
	| Set flags frecuency: -1=never, 1001=always
	| Ex:
	| $mutation->setFrecuency('MODIFY_QUOTED',500)
	| Result:
	| 50% of CHARACTERS in quoted strings will be changed
	------------------------------------------------------*/
	
	function setFrecuency($flag, $value){
		$accepted_flags=array('MODIFY_VARS','MODIFY_QUOTED_STRINGS',
		'MODIFY_DOUBLE_QUOTED_STRINGS','MODIFY_NUMBERS');
		
		// Valid flag check
		if( !in_array($flag,$accepted_flags)){
			echo "<pre>Function setFrecuency, Fatal error: Unknown flag: '$flag'\n";
			echo "Allowed flags: ".implode(',',$accepted_flags)."\n";
			echo "Exitting...\n</pre>";
			exit();			
		}
		
		// Valid value check
		if( $value != ceil($value) || $value<(-1) || $value>1001 || !is_numeric($value) ){
			echo "<pre>Function setFrecuency, Fatal error: '$value' is not a valid value for flag '$flag'\n";
			echo "Exitting...\n</pre>";
			exit();			
		}
		
		// Set it
		$this->flags[$flag] = $value;
		
	}
	
	/*-----------------------------------------------------
	| Function FrecuencyCheck($flag)
	|
	| Returns true or false based on a random dice
	| and frecuencys set by function SetFrecuency
	------------------------------------------------------*/	
	function frecuencyCheck( $flag ){

		$accepted_flags=array('MODIFY_VARS','MODIFY_QUOTED_STRINGS',
		'MODIFY_DOUBLE_QUOTED_STRINGS','MODIFY_NUMBERS');
		
		// Valid flag check
		if( !in_array($flag,$accepted_flags)){
			echo "<pre>Function FrecuencyCheck, Fatal error: Unknown flag: '$flag'\n";
			echo "Allowed flags: ".implode(',',$accepted_flags)."\n";
			echo "Exitting...\n</pre>";
			exit();			
		}
		
		// Random decision
		$dice=rand(0,1000);
		if( $dice < $this->flags[$flag] ){
			return true;
		}else{
			return false;
		}
		
	}
	
	
	/*-----------------------------------------------------
	| Function readBackToCharacter($stop_character,[$position])
	| 
	| Reads back from (current cursor positon-1) or $position
	| Stops when reading $stop_character
	------------------------------------------------------*/	
	function readBackToCharacter($stop_character,$position=false){
		
		// If no start position, use latest cursor position
		if( !$position ){ $position=$this->cursor-1; }

		// Read back loop
		while( $this->code[$position] != $stop_character ){
			$string=$this->code[$position].$string;
			$position--;
		}
		
		return $string;
	}
	
	
	/*-----------------------------------------------------
	| Function numberModify($number)
	| 
	| Modify a number with an equivalent calc
	| Example: numberModify(100)
	| Returns: (35+40+90-10)
	------------------------------------------------------*/	
	
	function numberModify($number){
		
		$num_calcs=rand(1,4);		// Random number of calculations
		$total=rand(0,1366613);		// Starting number
		$out=$total;
		
		// Loop start
		for( $aux=0; $aux<$num_calcs; $aux++ ){
			
			$random_number=rand(0,65535);	// get a random number
			
			// 3 posible calculations
			switch(rand(1,3)){
				
				// Add
				case 1:				
					$out.='+'.$random_number;
					$total=$total+$random_number;
				break;
				
				// Substract
				case 2:				
					$out.='-'.$random_number;
					$total=$total-$random_number;
				break;
				
				// Multiply
				case 3:
					$random_number=rand(1,30);
					$out='('.$out.')*'.$random_number;
					$total=$total*$random_number;
				break;
			}
			
		}
		
		// Adjust the resulting number
		if( $total>$number ){
			return $out.'-'.($total-$number);
		}elseif( $total<$number ){
			return $out.'+'.($number-$total);
		}else{
			return $out;
		}
	
	}
	
	/*-----------------------------------------------------
	| Function generateRandomString($min_length,$max_length)
	|
	| Generate a Random String with a random number of
	| characters between $min_length and $max_length
	------------------------------------------------------*/		
	function randomString($min_length,$max_length){
		
		$characters='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$length=rand($min_length,$max_length);
		for($aux=1;$aux<=$length;$aux++){
			$n=rand(0,strlen($characters)-1);
			$char=$characters[$n];
			$string.=$char;
		}
		
		return $string;
		
	}

	/*-----------------------------------------------------
	| function getNewVarName
	|
	| Generate a new random varname or return
	| a previously generated varname
	|
	| $this->getNewVarName('hello')  Returns "UmndsYhw"
	| $this->getNewVarName('bye')	Returns "wkHoPw"
	|
	| $this->getNewVarName('start')  Returns "ulasnqhwe"
	| $this->getNewVarName('start')  Returns "ulasnqhwe" too
	|
	------------------------------------------------------*/	
	
	function getNewVarName($varname){
		if( $this->varnames[$varname] ){
			return $this->varnames[$varname];
		}else{
			if( $this->frecuencyCheck('MODIFY_VARS') ){
				$new_varname=$this->randomString(4,10);
			}else{
				$new_varname=$varname;
			}
			$this->varnames[$varname] = $new_varname;
			return $new_varname;
		}
	}
	
	/*-----------------------------------------------------
	| function inserString($string)
	|
	| Inserts a string in current cursor position 
	------------------------------------------------------*/	
	function insertString($str){
		$this->backReplace('',$str);
	}
	
	
	/*-----------------------------------------------------
	| function backwordReplace($string,$new_string)
	|
	| Looks for the woerd '$string' inmediatly after current
	| cursor, changes it, and readjusts the cursor to the
	| new position.
	------------------------------------------------------*/	
	function backReplace( $search, $replace ){
		$current_cursor=$this->cursor;
		$left=substr($this->code, 0 , $this->cursor-(strlen($search))); 
		$right=substr($this->code, $this->cursor);
		$this->code=$left.$replace.$right;
		$this->cursor=$this->cursor+(strlen($replace)-strlen($search));
	}
	
	/*-----------------------------------------------------
	| function cursorRead()
	|
	| Read the character at current position
	| ProcessIt an refresh the status
	------------------------------------------------------*/	
	function cursorRead(){
		
		// If cursor at end of code, return false
		if ( $this->cursor >= strlen($this->code) ){ return false; }
		
		// Load next character
		$this->status['character'] = $this->code[$this->cursor];

		// Check if current char is a separation character
		if ( in_array($this->status['character'],array("\n","\t",' '))  ){
			$separation_character = true;
		}else{
			$separation_character = false;
		}
		
		// Check if current char is a word separator
		if ( in_array($this->status['character'],$this->php['word_separators'])  ){
			$word_separator = true;
		}else{
			$word_separator = false;
		}
		
		// Check if current char is a number
		$nums=array('0','1','2','3','4','5','6','7','8','9');
		if ( in_array($this->status['character'],$nums)  ){
			$is_number = true;
		}else{
			$is_number= false;
		}

		// Check if current char is slashed
		if( $this->lastChars(1) == '\\' && $this->lastChars(2) != '\\'.'\\' ){
			$this->status['slashed'] = true;
		}else{
			$this->status['slashed'] = false;
		}
	
		// Check for PHP start
		if( $separation_character && $this->lastChars(5) == '<?php' ){
			$this->status['in_php'] = true;
		}

		// And Check for PHP ending
		if( $separation_character && $this->lastChars(2) == '?>' ){
			$this->status['in_php'] = false;
		}

		// This block is executed only if cursor is
		// inside PHP code
		if( $this->status['in_php'] == true  ){
  
			// CONDITIONS IF NOT SLASHED CHAR
			// This block executed only if the character
			// at cursor position is NOT \slashed
			if( !$this->status['slashed']  ){

				// Check if cursor position is 'quoted'
				if ( $this->status['character'] == "'" && $this->status['in_quotes'] == false ){
					$this->status['in_quotes'] = true;
				}elseif(  $this->status['character'] == "'" && $this->status['in_quotes'] == true  ){
					$this->status['in_quotes'] = false;
				}

				// Check if cursor position is "double quoted"
				if ( $this->status['character'] == '"' && $this->status['in_double_quotes'] == false ){
					$this->status['in_double_quotes'] = true;
				}elseif(  $this->status['character'] == '"' && $this->status['in_double_quotes'] == true  ){
					$this->status['in_double_quotes'] = false;
				}

				// Detect one-line comments
				// like this one :)
				if ( $this->status['character'] == '/' && $this->lastChars(1) == '/' && !$this->status['in_comment'] ){
					$this->status['in_comment_line'] = true;
				}
				if ( $this->status['character'] == "\n" && $this->status['in_comment_line'] ){
					$this->status['in_comment_line'] = false;
				}
				
				// Detect multi-line comments, comments /* like this */
				if ( $this->status['character'] == '*' && $this->lastChars(1) == '/' ){
					$this->status['in_comment'] = true;
				}
				if ( $this->status['character'] == '/' && $this->lastChars(1) == '*' ){
					$this->status['in_comment'] = false;
				}
				
				// Detect vars start, variables starts with a dollar
				if( $this->status['character'] == '$' && !$this->status['in_quotes'] ){
					$this->status['in_var'] = true;
					
				// Detect vars ending, variables ends with a separation character
				}elseif( $this->status['in_var'] && $word_separator ){
					
					$this->status['in_var'] = false;
					
						
					// Variable detected, capture its name
					$varname=$this->readBackToCharacter('$');

					/*
					| Posible Mutation HERE !! !!! !!
					| Vars conversion!
					*/					
					// Get a random new name for this variable
					$new_varname = $this->getNewVarName($varname); // <- frecuency implemented here
					
					// Replace the variable
					$this->backReplace($varname,$new_varname);
				}

				// Detect if cursor is in a number, like 666
				if( !$this->status['in_quotes'] && !$this->status['in_double_quotes'] ){
					
					if( $is_number && !$this->status['in_number'] ){
						$this->status['in_number'] = true;
						$this->status['number'].=$this->status['character'];
					}elseif($is_number && $this->status['in_number'] ){
						// detect numbers continuation
						$this->status['number'].=$this->status['character'];
					}elseif(!$is_number && $this->status['in_number']){
						
						/*
						| 	Posible Mutation HERE !! !!! !!
						| 	Number conversion!
						*/
						   
						if( $this->frecuencyCheck('MODIFY_NUMBERS') ){
							$this->backReplace($this->status['number'],$this->numberModify($this->status['number']));
						}
						
						$this->status['number']='';
						$this->status['in_number'] = false;
						/* mutation end */
					}
				}

				// Detect simple quoted strings 'like this one'
				if( $this->status[in_quotes] &&  $this->status[character] != "'" ){
			
					/*
					| 	Posible Mutation HERE !! !!! !!!
				 	| 	'quoted' literals conversion!
				 	*/

					if( $this->frecuencyCheck('MODIFY_QUOTED_STRINGS') ){
						if( rand(0,6) == 0 ){ $nl="\n"; }else{ $nl=''; }
						switch(rand(0,1)){
							case 0:
								$this->insertString("'.$nl'");
							break;
							case 1:
								$this->insertString("'.$".$this->randomString(4,9).".".$nl."'");
							break;
						}
						
					}
					/* mutation end */
				}
				
				
				// Detect double quoted strings "like this"
				if( $this->status[in_double_quotes] &&  $this->status[character] != '"' && $this->status['in_var'] == false ){
				
					/*
					| 	Posible Mutation HERE !! !!! !!!
				 	| 	"double quoted" literals conversion!
				 	*/
				 	if( $this->frecuencyCheck('MODIFY_DOUBLE_QUOTED_STRINGS') ){
						if( rand(0,6) == 0 ){ $nl="\n"; }else{ $nl=''; }
						switch(rand(0,1)){
							case 0:
								$this->insertString('".'.$nl.'"');
							break;
							case 1:
								$this->insertString('".$'.$this->randomString(4,9).'.'.$nl.'"');
							break;
						}
						
					}				 	
					
					/* mutation end */
					
				}
				
				
			}
		}

		// refresh status, and move cursor
		$this->status['cursor'] = $this->cursor;
		$this->cursor=$this->cursor+1;
		return true;
		
	}

	// Return the 'num' last characters
	function lastChars( $num ){

		$start=$this->cursor-$num;
		$end=$num;
		if($start<0){ $start=0; }
		if( $end > strlen($this->code)-1 ){ $end=strlen($this->code); }

		return (substr($this->code,$start,$end));
	}
	
	function mutate(){
		while ( $this->cursorRead() ){
			$n++;
		}
		return $n;
	}
	
}

/*************************************************************
	PHP Mutation Engine
**************************************************************/

function d( $nast ){
	echo "<pre style='border:1px solid red;background-color: #EEEEEE;color:black;'>";
	$out = print_r($nast,true);
	echo htmlentities($out);
	echo "</pre>";
}

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}


?>