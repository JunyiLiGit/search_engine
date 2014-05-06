<?php
include_once("dbconnect.inc.php");

$mysqli = new mysqli($host, $user, $password, $database);
if (mysqli_connect_errno()) 
{
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}



$ui = $_GET['ui'];
$article = array();

$query = "SELECT * FROM TREC9 WHERE ID=".$ui;
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$row = $result->fetch_row();

echo "<h2>".$row[3]."</h2>";
echo "<b>MEDLINE identifier:</b><br>".$row[0]."<br><br>";
echo "<b>Author:</b><br>".$row[6]."<br><br>";
echo "<b>Source:</b><br>".$row[1]."<br><br>";
echo "<b>Publication type:</b><br>".$row[4]."<br><br>";
echo "<b>MeSH terms:</b><br>".$row[2]."<br><br>";
echo "<b>Abstract:</b><br>".$row[5];
?>
