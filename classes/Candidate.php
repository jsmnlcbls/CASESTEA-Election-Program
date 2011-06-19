<?php
require_once "Database.php";

class Candidate
{
	private $_database;
	
	public function __construct()
	{
		$this->_database = new Database();
		if (FALSE === $this->_database->connect()) {
			die("Unable to connect to database.");
		}
		
	}
	
	public function getAll()
	{
		$sql = "SELECT * FROM candidates";
		$this->_database->query($sql);
		return $this->_database->getQueryResultAsArray();
	}
	
	public function getAllCandidatesByPosition()
	{
		$sql = "SELECT * FROM candidates ORDER BY position_id";
		$this->_database->query($sql);
		$data = $this->_database->getQueryResultAsArray();
		
		$result = array();
		foreach ($data as $value) {
			$result[$value['position_id']][] = $value;
		}
		return $result;
	}
	
}