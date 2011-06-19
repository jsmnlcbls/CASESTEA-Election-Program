<?php
require_once "Database.php";

class Election
{
	private $_database;
	
	private $_errorMessage;
	
	public function __construct()
	{
		$this->_database = new Database();
		$this->_database->connect();
	}
	
	public function getTotalResults()
	{
		$sql = "SELECT * FROM vote_summary ORDER BY candidate_id";
		$this->_database->query($sql);
		$resultArray = $this->_database->getQueryResultAsArray();
		$result = array();
		
		foreach ($resultArray as $vote) {
			$candidateId = $vote['candidate_id'];
			$candidateTotal = 0;
			foreach ($vote as $index => $value) {
				if ($index != "candidate_id") {
					$precinctNumber = str_replace("precinct_", "", $index);
					$candidateTotal += $value;
					$result[$precinctNumber][$candidateId] = $value;
				}
			}
			$result['total'][$candidateId] = $candidateTotal;
		}

		return $result;
	}
	
	public function getResultsByPrecint($precinct)
	{
		$sql = "SELECT candidate_id, precinct_$precinct FROM vote_summary ORDER BY candidate_id";
		$this->_database->query($sql);
		$resultArray = $this->_database->getQueryResultAsArray();
		$result = array();
		foreach ($resultArray as $vote) {
			$result[$vote['candidate_id']] = $vote['precinct_' . $precinct];
		}
		return $result;
	}
	
	public function castVote($vote, $precinct)
	{
		
		$columns = implode(', ', array_keys($vote));
		$values = implode(', ', array_values($vote));
		
		$updateStatements = array();
		foreach ($vote as $candidateId) {
			$precinctColumn = "precinct_" . $precinct;
			$updateStatements[] = "UPDATE vote_summary SET $precinctColumn = $precinctColumn + 1 WHERE candidate_id = $candidateId";
		}
		
		$sql = "BEGIN;";
		$sql .= implode(';', $updateStatements) . ";";
		$sql .= "COMMIT;";
		
		$this->_database->query($sql);
		while (false !== ($result = $this->_database->getQueryResult())) {
			$error = $this->_database->getSqlState($result);
			if (0 != $error) {
				return false;
			}
		}
		return true;
	}
}