
<?php

error_reporting(E_PARSE);
require 'stemmer.php';
include_once("dbconnect.inc.php");

$mysqli = new mysqli($host, $user, $password, $database);
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}



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


function array_sum_combine(/* $arr1, $arr2, ... */)
{
  $return = array();
  $args = func_get_args();
  foreach ($args as $arr)
  {
    foreach ($arr as $k => $v)
    {
      if (!array_key_exists($k, $return))
      {
        $return[$k] = 0;
      }
      $return[$k] += $v;
    }
  }
  return $return;
}





$a= $_POST['Query'];
	
$tempSearch = strtolower($_POST['Query']);
$tempSearch = str_replace(" (", "(", $tempSearch);
$tempSearch = str_replace(") ", ")", $tempSearch);
$orSearch = array();
$orSearch[] = @getParenthesesLeft($tempSearch);
$orSearch[] = @getParenthesesRight($tempSearch);

$a = explode("(",$tempSearch);
$b = explode(")",$tempSearch);
$tempSearch = @strtolower($a[0].$b[1]);
$tempSearch = str_replace("and", "", $tempSearch);
$tempSearch = str_replace("  ", " ", $tempSearch);
$andSearch = explode(" ",$tempSearch);
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


//print_r($andSearch);

for ($i = 0 ; $i < count($orSearch); $i++){

  if($orSearch[$i] == "U.S"){
  $orSearch[$i] = "u.s.";
  }else{
  $orSearch[$i] = $porter->stem($orSearch[$i]);
  }
} 
$orSearch = array_filter($orSearch);
$orSearch = array_values($orSearch);

$andCount=count($andSearch);
$orCount= count($orSearch);

$andQuery=array();

if($andCount > 0){
for($i=0; $i<$andCount; ++$i){
	
	$andQuery[$i] = "select ID, count from TREC9_WORD_COUNT where word = ". "'$andSearch[$i]'".";";
	
	//echo $andQuery[$i];
	//echo "<br>";
	$andResult = $mysqli->query($andQuery[$i]) or die($mysqli->error.__LINE__);
	$andResult_rows = $andResult->num_rows;	
	$ID_Count_andArray = array(); 
	while($and_RowDataArray = $andResult->fetch_array(MYSQLI_NUM))

       {  
		 
		 $ID_Count_andArray[$and_RowDataArray[0]] = $and_RowDataArray[1];
		
       }
       	
       	$data_andArray[$i]=$ID_Count_andArray;
             	
}

}

$copy=$data_andArray;


for($i=0; $i<$andCount; ++$i){

 $copy[$i+1]=array_intersect_key($copy[$i], $copy[$i+1]);

 }
  
 $andIntersectNumber= count($copy[$andCount-1]);
 
 //echo "and intersect result"."<br>";
 //print_r($copy[$andCount-1]);
 //echo "and result number"."<br>";
 //echo $andIntersectNumber;
  
$data_orArray=array();


if($orCount > 0){
for($i=0; $i<$orCount; ++$i){
	
	$orQuery[$i] = "select ID, count from TREC9_WORD_COUNT where word = ". "'$orSearch[$i]'".";";
	
	//echo $orQuery[$i];
	//echo "<br>";
	$orResult = $mysqli->query($orQuery[$i]) or die($mysqli->error.__LINE__);
	$orResult_rows = $orResult->num_rows;	
	$ID_Count_orArray = array(); 
	while($or_RowDataArray = $orResult->fetch_array(MYSQLI_NUM))

       {  
		 
		 $ID_Count_orArray[$or_RowDataArray[0]] = $or_RowDataArray[1];
		
       }
       	
       	$data_orArray[$i]=$ID_Count_orArray;
       	
       
        
       	
             	
}



}


  

	//print_r($data_orArray);
	//echo "<br>";
$combine_orArray= array_sum_combine($data_orArray[0],$data_orArray[1]);

 //print_r($combine_orArray);
 



if($orCount>0 && $andCount>0){

//print_r($copy[$andCount-1]);

//echo "<br>";

$result_Array=array_intersect_key($copy[$andCount-1], $combine_orArray);

//echo "and or intersect"."<br>";
//print_r($result_Array);
//echo "and or intersect result nummber"."<br>";

//echo count($result_Array);
//echo "<br>";



}

else if ($andCount>0 && $orCount==0) {$result_Array=$copy[$andCount-1];}

else if ($andCount==0 && $orCount>0) {$result_Array=$combine_orArray;}


//$resoultNumber= count($result_Array);

//echo $resoultNumber;

$ID_Array=array_keys($result_Array);

//echo "ID_Array"."<br>";
//print_r($ID_Array);

$product=0;
$countAnd=array();
$countOr=array();

$result_Rank=array();
//echo $andCount;

//echo "dataorarray"."<br>";
//print_r($combine_orArray);


$countAnd=array();
if($andCount>0 && $orCount>0){
	
for($i=0; $i<count($ID_Array); ++$i){
		 
		  
	  for($j=0; $j<$andCount; ++$j){
		  
	    $countAnd[$j] = $data_andArray[$j][$ID_Array[$i]];
		
		    }
		    
		//echo "<br>";    
		//print_r($countAnd);
		
		 
		  
	    $countOr[$i] = $combine_orArray[$ID_Array[$i]];
		
		    
		 //echo "<br>";
		 //echo "countOr"; 
		 //echo "<br>";    
		//  print_r($countOr); 
		  
		  	  
		  $rank_Array[$i]= array_product($countAnd) * $countOr[$i];
		  
		  
		  //print_r($rank_Array[$i]);
		  
		  $result_Rank[$ID_Array[$i]]  = $rank_Array[$i];
		  
		  }
		
		
		  
		  //print_r($rank_Array[$i]);
		  
		 // $result_Rank[$ID_Array[$i]]  = $rank_Array[$i];
		  
		 // print_r($result_Rank);
		 
		   arsort($result_Rank);
		   
		   //print_r($result_Rank);
		   
	  
  }

  
if ($andCount>0 && $orCount==0)  {
	
	
	for($i=0; $i<count($ID_Array); ++$i){
		 
		  
	  for($j=0; $j<$andCount; ++$j){
		  
	    $countAnd[$j] = $data_andArray[$j][$ID_Array[$i]];
		
		    }
		  	  
		 $rank_Array[$i]= array_product($countAnd);	  
		  $result_Rank[$ID_Array[$i]]  = $rank_Array[$i];
		  		 
		   arsort($result_Rank);

}
  
}

//print_r($combine_orArray);
//echo count($combine_orArray);

if (empty($andCount) && $orCount==2){
	
 $result_Rank=$combine_orArray;
 arsort($result_Rank);

}
	 
//print_r($combine_orArray);
  

//print_r($result_Rank);
 
 
 
 $result_Count= count($result_Rank);
 
 
 
 

echo "<table border=\"1\">
	 <tr>
	 <td bgcolor=#0000FF style=\"color:white\" >Title</td> 
	 <td bgcolor=#0000FF style=\"color:white\" >Author</td> 
	 <td bgcolor=#0000FF style=\"color:white\" >Source</td> 
	 <td bgcolor=#0000FF style=\"color:white\" >Rank</td> 
	 </tr>";
foreach ($result_Rank  as $key => $value) {
	if($value > 0){
		$sql = "SELECT title, author, source FROM TREC9 WHERE ID = ".$key.";";
		$result = $mysqli->query($sql);
		$row = $result->fetch_row();
		echo "<tr>
			<td><a href = \"detail.php?ui=".$key."\">".$row[0]."</a></td>
			<td>".$row[1]."</td>
			<td>".$row[2]."</td>
			<td>".$value."</td>
			</tr>";	
	}	  
}
 

 
 
 





?>
