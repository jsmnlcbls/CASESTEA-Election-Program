<?php
require_once "Database.php";

class Position
{
	private $_database;
	
	public function __construct()
	{
		$this->_database = new Database();
		$this->_database->connect();
	}
	
	public function getAll()
	{
		$sql = "SELECT id, name, composition FROM positions ORDER BY id";
		$this->_database->query($sql);
		return $this->_database->getQueryResultAsArray();
	}
}