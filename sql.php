<?php

require 'stemmer.php';

if(file_get_contents('./ohsumed87.txt')){
	$file = file_get_contents('./ohsumed87.txt');
	$file = str_replace("'", "\'", $file);	
	}
	

function getTextArray($string, $tagname)
 {
    $pattern = "/<$tagname>([\s\S]*?)<\/$tagname>/";
    preg_match_all($pattern, $string, $matches);
    return $matches[1];
 }
 
 
function getOneText($string, $tagname)
 {
    $pattern = "/<$tagname>([\s\S]*?)<\/$tagname>/";
    preg_match($pattern, $string, $matches);
    return $matches[1];
 } 
 
 
 $TextArray = getTextArray($file, "I");
 $stem = new Stemmer();

 
 //echo count($TextArray);
 
 for($i=0; $i<count($TextArray); ++$i){
	 	 
   $Value[$i][0]=@getOneText($TextArray[$i], "U");
   $Value[$i][1]=@getOneText($TextArray[$i], "S");
   $Value[$i][2]=@getOneText($TextArray[$i], "M");
   $Value[$i][3]=@getOneText($TextArray[$i], "T");
   $Value[$i][4]=@getOneText($TextArray[$i], "P");
   $Value[$i][5]=@getOneText($TextArray[$i], "W");
   $Value[$i][6]=@getOneText($TextArray[$i], "A");
   

   //print_r("\n");
   
   
   $valuetring[$i] = $Value[$i][3]." ".$Value[$i][5]." ".$Value[$i][2]." ".$Value[$i][6]; 
   //echo $valuetring[$i];
   

   
   $bad=array(".\n","\n",".");
   $good=array(" "," "," ");
   $valuetring[$i] = str_replace($bad, $good, $valuetring[$i]);
   //echo $valuetring[$i];
   //2d array which store the all words;
   $words[$i] = explode(" ",$valuetring[$i]);	
   
   for ($j = 0 ; $j< count($words[$i]);$j++){
	  	  $words[$i][$j] = $stem->stem($words[$i][$j]);
	  	 // echo $words[$i][$j];
	  	  //print_r("\n");
	  } 
	  
	$words[$i]=array_filter($words[$i]);  
	$KeyValueArray[$i] = array_count_values($words[$i]);
	 
	//print_r($KeyValueArray[$i]);
	
	
	

}






//create table1
$output_file = 'init.sql';

if (file_exists($output_file))
      unlink($output_file);

//create records table;

$createT1 = "drop table  if exists TREC9;";
$createT1 .= "create table TREC9 (ID int, source varchar(255), mesh varchar(1000), title varchar(255), 
pulication varchar(255), abstract varchar(2000), author varchar(255));\n";


file_put_contents($output_file, $createT1, FILE_APPEND | LOCK_EX);

for($i=0 ; $i<count($TextArray) ; $i++)
{
	$insert[$i] = "insert into TREC9 values (".$Value[$i][0].",'".$Value[$i][1]."','".$Value[$i][2]."','".$Value[$i][3]."','".$Value[$i][4]."','".$Value[$i][5]."','".$Value[$i][6]."');\n";
    file_put_contents($output_file, $insert[$i], FILE_APPEND | LOCK_EX);
    //echo $insert[$i];
    //echo"<br>";
	
}


//create keywords table

$createT2 = "drop table  if exists TREC9_WORD_COUNT;";
$createT2 .= "create table TREC9_WORD_COUNT (ID varchar(255), word varchar(255), count int);\n";
file_put_contents($output_file, $createT2, FILE_APPEND | LOCK_EX);


for($i=0,$j=0 ; $i<count($TextArray) ; $i++){
		
		foreach ($KeyValueArray[$i] as $key=>$value)
		{   
			$key = str_replace("'", "\'", $key);	
			$insert[$j] = "insert into TREC9_WORD_COUNT values ('".$Value[$i][0]."','";
			$insert[$j].= $key."','".$value."');\n";
			//echo $insert[$h]."\n";
			file_put_contents($output_file, $insert[$j], FILE_APPEND | LOCK_EX);
			$j++;
		}
	
}

?>


<?php
include_once("dbconnect.inc.php");

function getParenthesesLeft($string)
{
    $pattern = "/\(([\s\S]*?) or/";
    preg_match($pattern, $string, $matches);
    $leftOr = str_replace(" ", "", $matches[1]);
    return $leftOr;
}

function getParenthesesRight($string)
{
    $pattern = "/or ([\s\S]*?)\)/";
    preg_match($pattern, $string, $matches);
    $rightOr = str_replace(" ", "", $matches[1]);
    return $rightOr;
}

function cmp ($lhs, $rhs){
return $lhs[1] < $rhs[1];
}


$mysqli = new mysqli($host, $user, $password, $database);

if (mysqli_connect_errno()) 
{
printf("Connect failed: %s\n", mysqli_connect_error());
exit();
}


if(isset($_POST['submit'])){

$tempSearch = strtolower($_POST['Query']);
$tempSearch = str_replace(" (", "(", $tempSearch);
$tempSearch = str_replace(") ", ")", $tempSearch);
$orSearch = array();
$orSearch[] = getParenthesesLeft($tempSearch);
$orSearch[] = getParenthesesRight($tempSearch);

$a = explode("(",$tempSearch);
$b = explode(")",$tempSearch);
$tempSearch = strtolower($a[0].$b[1]);
//echo $tempSearch;
$tempSearch = str_replace("and", "", $tempSearch);
$tempSearch = str_replace("  ", " ", $tempSearch);
$andSearch = explode(" ",$tempSearch);
//print_r($andSearch);

$porter = new Stemmer();

for ($i = 0 ; $i < count($andSearch); $i++){

  if($andSearch[$i] == "U.S"){
  $andSearch[$i] = "u.s.";
  }else{
  $andSearch[$i] = $porter->stem($andSearch[$i]);
  }
} 
$andSearch = array_filter($andSearch);
$andSearch = array_values($andSearch);
print_r($andSearch);

for ($i = 0 ; $i < count($orSearch); $i++){

  if($orSearch[$i] == "U.S"){
  $orSearch[$i] = "u.s.";
  }else{
  $orSearch[$i] = $porter->stem($orSearch[$i]);
  }
} 
$orSearch = array_filter($orSearch);
$orSearch = array_values($orSearch);

print_r($orSearch);

}

?>




