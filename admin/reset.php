<?php
require_once "../classes/Database.php";

$database = new Database();
$database->connect();

$precinctSql = array();
for ($a = 1; $a < 7; $a++) {
	$precinctSql[] = "precinct_" . $a . " = 0";	
}
$precinctSql = implode(', ', $precinctSql);

$sql = "TRUNCATE TABLE vote CASCADE;";
$database->query($sql);
$database->getQueryResult();
if ($database->getSqlState() == 0) {
	echo "Vote records cleared.<br/>";
}

$sql = "UPDATE vote_summary SET $precinctSql";
$database->query($sql);
$database->getQueryResult();
if ($database->getSqlState() == 0) {
	echo "Vote summary cleared<br/>";
} else {
	echo $database->getSqlState();
}


