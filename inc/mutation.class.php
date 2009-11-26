<?php
/*************************************************************

	PHP Mutation Engine
	
	By Dominika Vladimirovskaya
	dominika.vladimirov(At)comentalo.net
	October // 2009 // Soviet Union
	
**************************************************************/

class Mutation{
	
	var $code;
	var $cursor;
	var $status;
	var $php;
	var $mutation_flags;

	// Constructor	
	function Mutation(){
   
		// Php parser status init
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
	
		// Php parser configuration
		$this->php['word_separators']	= array("\n","\t",'"',"'",'(',')',' ','*','/','-','+','%','&','=','{','}','-','\\','<','>',';');
		
		// Php related stuff
		$this->php['reserved_vars']	= array('this','_get','vars','_post','_request','cookies','server','globals');
		

	}
	
	// Sets the code to mutate
	function setCode( $code ){
		$this->code=$code;
	}
	
	// Return the mutated code
	function getCode(){
		return $this->code;	
	}

	// Sets the internal cursor position
	function cursorSetPosition($pos){
		$this->cursor=$pos;
	}
	
	// FUNCTION setFrecuency
	// Set flags frecuency, -1=never, 1001=always
	// Ex:
	// $mutation->setFrecuency('MODIFY_QUOTED',500)
	// Result:
	// 50% of CHARACTERS in quoted strings will be changed
	
	function setFrecuency($flag, $value){
		$accepted_flags=array('MODIFY_VARS','MODIFY_QUOTED_STRINGS','MODIFY_DOUBLE_QUOTED_STRINGS','MODIFY_NUMBERS');
	}
	
	
	// Reads back from (current cursor positon-1) or $position
	// Stops when reading $stop_character
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
	
	// Modify a number with an equivalent calc
	// Example: numberModify(100)
	// Returns: (35+40+90-10)
	
	function numberModify($number){
		
		$num_calcs=rand(1,4);
		$total=rand(0,1366613);
		$out=$total;
		
		for( $aux=0; $aux<$num_calcs; $aux++ ){
			
			$random_number=rand(0,65535);
			
			switch(rand(1,3)){
				case 1:
					$out.='+'.$random_number;
					$total=$total+$random_number;
				break;
				case 2:
					$out.='-'.$random_number;
					$total=$total-$random_number;
				break;
				case 3:
					$random_number=rand(1,30);
					$out='('.$out.')*'.$random_number;
					$total=$total*$random_number;
				break;
			}
			
		}
		
		if( $total>$number ){
			return $out.'-'.($total-$number);
		}elseif( $total<$number ){
			return $out.'+'.($number-$total);
		}else{
			return $out;
		}
	
	}
	
	
	// Generate a Random String.
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
	
	// Generate a new random varname or return
	// a previously generated
	//
	// $this->getNewVarName('hello')  Returns "UmndsYhw"
	// $this->getNewVarName('bye')	Returns "wkHoPw"
	//
	// $this->getNewVarName('start')  Returns "ulasnqhwe"
	// $this->getNewVarName('start')  Returns "ulasnqhwe"
	//
	function getNewVarName($varname){
		if( $this->varnames[$varname] ){
			return $this->varnames[$varname];
		}else{
			$new_varname=$this->randomString(4,10);
			$this->varnames[$varname] = $new_varname;
			return $new_varname;
		}
	}
	
	// InsertString
	function insertString($str){
		$this->backReplace('',$str);
		/*$current_cursor=$this->cursor;
		$left=substr($this->code, 0 , $this->cursor-(strlen($search))); 
		$right=substr($this->code, $this->cursor);
		$this->code=$left.$replace.$right;
		$this->cursor=$this->cursor+(strlen($replace)-strlen($search));
		*/
	}	
	
	
	// Back word replace
	function backReplace( $search, $replace ){
		$current_cursor=$this->cursor;
		$left=substr($this->code, 0 , $this->cursor-(strlen($search))); 
		$right=substr($this->code, $this->cursor);
		$this->code=$left.$replace.$right;
		$this->cursor=$this->cursor+(strlen($replace)-strlen($search));
	}
	
	//**********************************************
	//  Read the character at current position
	//  ProcessIt an refresh the status
	//**********************************************
	
	function cursorRead(){
		
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

		// Check if slashed
		if( $this->lastChars(1) == '\\' && $this->lastChars(2) != '\\'.'\\' ){
			$this->status['slashed'] = true;
		}else{
			$this->status['slashed'] = false;
		}
	
		// Check for PHP start
		if( $separation_character && $this->lastChars(5) == '<?php' ){
			$this->status['in_php'] = true;
		}

		// Check for PHP end
		if( $separation_character && $this->lastChars(2) == '?>' ){
			$this->status['in_php'] = false;
		}

		// IN PHP CODE CONDITIONS
		if( $this->status['in_php'] == true  ){
  
			// CONDITIONS IF NOT SLASHED CHAR
			if( !$this->status['slashed']  ){

				// Quotes check '
				if ( $this->status['character'] == "'" && $this->status['in_quotes'] == false ){
					$this->status['in_quotes'] = true;
				}elseif(  $this->status['character'] == "'" && $this->status['in_quotes'] == true  ){
					$this->status['in_quotes'] = false;
				}

				// Double quotes check "
				if ( $this->status['character'] == '"' && $this->status['in_double_quotes'] == false ){
					$this->status['in_double_quotes'] = true;
				}elseif(  $this->status['character'] == '"' && $this->status['in_double_quotes'] == true  ){
					$this->status['in_double_quotes'] = false;
				}

				// Detect one-line comments
				if ( $this->status['character'] == '/' && $this->lastChars(1) == '/' && !$this->status['in_comment'] ){
					$this->status['in_comment_line'] = true;
				}
				if ( $this->status['character'] == "\n" && $this->status['in_comment_line'] ){
					$this->status['in_comment_line'] = false;
				}
				
				// Detect multi-line comments
				if ( $this->status['character'] == '*' && $this->lastChars(1) == '/' ){
					$this->status['in_comment'] = true;
				}
				if ( $this->status['character'] == '/' && $this->lastChars(1) == '*' ){
					$this->status['in_comment'] = false;
				}
				
				// Detect vars start
				if( $this->status['character'] == '$' && !$this->status['in_quotes'] ){
					$this->status['in_var'] = true;
					
				// Detect vars ending
				}elseif( $this->status['in_var'] && $word_separator ){
					
					$this->status['in_var'] = false;
					
					if ( $this->mutation_flags['MODIFY_VARS'] == true ){
						// Variable detected, capture its name
						$varname=$this->readBackToCharacter('$');
						
						// Get a random new name for this variable
						$new_varname = $this->getNewVarName($varname);
						
						// Replace the variable
						$this->backReplace($varname,$new_varname);
						
					}
				}

				// Detect Numbers 
				if( !$this->status['in_quotes'] && !$this->status['in_double_quotes'] ){
					
					if( $is_number && !$this->status['in_number'] ){
						$this->status['in_number'] = true;
						$this->status['number'].=$this->status['character'];
					}elseif($is_number && $this->status['in_number'] ){
						// detect numbers continuation
						$this->status['number'].=$this->status['character'];
					}elseif(!$is_number && $this->status['in_number']){
						
						  //  echo "Number detected:".$this->status['number']."<br>";
						  //  echo "Transformed to: ".$this->numberModify($this->status['number'])."<br><br>";
							$this->backReplace($this->status['number'],$this->numberModify($this->status['number']));
							$this->status['number']='';
							$this->status['in_number'] = false;
					}
				}

				// Modify simple quoted strings '
				if( $this->status[in_quotes] &&  $this->status[character] != "'" ){
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
				
				

			}

		}

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
	
  // Debug parse
	function debugParse(){
		
		// Go to code start
		$this->cursorSetPosition(0);
		
		// Dump HTML
		echo "<html><style>
		body { background-color: #444444; color: #FFFFFF; }
		table{ font-size:11px; width:100% }
		.letter{ width: 15px; height:15px; font-size:11px; display:inline; color:black;
		float:left; border:1px solid #AAAAAA; text-align: center; cursor:hand;cursor:pointer;
		background-color: white;
		}
		.info{ border:2px solid #444444; background-color: yellow; color:black; position:absolute; }
		</style>
		<script>
		function showInfo(num){
			document.getElementById('info_'+num).style.display='';
			document.getElementById('info_'+num).style.top=mouseY+'px';
			document.getElementById('info_'+num).style.left=mouseX+'px';
		}
		function hideInfo(num){
			document.getElementById('info_'+num).style.display='none';
		}
		
		
	
		// Detect if the browser is IE or not.
		// If it is not IE, we assume that the browser is NS.
		var IE = document.all?true:false
		
		// If NS -- that is, !IE -- then set up for mouse capture
		if (!IE) document.captureEvents(Event.MOUSEMOVE)
		
		// Set-up to use getMouseXY function onMouseMove
		document.onmousemove = getMouseXY;
		
		// Temporary variables to hold mouse x-y pos.s
		var tempX = 0
		var tempY = 0
		var mouseX=0
		var mousey=0
		// Main function to retrieve mouse x-y pos.s
		
		function getMouseXY(e) {
		  if (IE) { // grab the x-y pos.s if browser is IE
			tempX = event.clientX + document.body.scrollLeft
			tempY = event.clientY + document.body.scrollTop
		  } else {  // grab the x-y pos.s if browser is NS
			tempX = e.pageX
			tempY = e.pageY
		  }  
		  // catch possible negative values in NS4
		  if (tempX < 0){tempX = 0}
		  if (tempY < 0){tempY = 0}  
		  // show the position values in the form named Show
		  // in the text fields named MouseX and MouseY
		  mouseX = tempX
		  mouseY = tempY
		  return true
		}
		
		</script><body>
		<div style='border:1px solid white;margin-bottom:20px;background-color:#660000;padding:5px;'><div style='margin:10px; text-align:center; font-weight:bold; color:white;'>Php Polymorphic Mutation Engine :: By Dominika Vladimirovskaya :: Debug Info</div></div>
		";

		$counter='0';

		// Parse code
		while ( $this->cursorRead() ){

			$counter++;

			if( $this->status['character'] == "\n" ){
			   $c="<b>N</b>";
				$color='green';
			}else if( $this->status['character'] == "\t" ){
				$c="<b>T</b>";
				$color='green';
			}else{
				$c=htmlspecialchars($this->status['character']);
				$color='white';
			}
		
			$left.="<span class='letter' style='background-color:$color' onmouseover='showInfo($counter)' onmouseout='hideInfo($counter)'>$c</span>";
			$right.="<div id='info_$counter' style='display:none' class='info'><pre>".print_r($this->status,true).'</pre></div>';
			$chars_status[$c] = $this->status;
		
			if( $this->status['character'] == "\n" ){ $left.="<br><br>"; }
		}
		
		echo "<table width='100%'><tr><td valign='top'>$left</td><td valign='top'>$right";
		echo "</td></tr></table>";
	
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