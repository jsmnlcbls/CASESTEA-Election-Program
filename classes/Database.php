<?php

class Database
{
	private $_connectionString;
	
	private $_connection;
	
	private $_lastQueryResult;
	
	public function __construct()
	{	
		$this->_connectionString = "host=" . Settings::$host . " "
								 . "port=" . Settings::$port . " "
								 . "dbname=" . Settings::$database . " "
								 . "user=" . Settings::$user;	
	}
	
	public function connect()
	{
		$this->_connection = pg_connect($this->_connectionString);
		if ($this->_connection !== FALSE) {
			return true;
		}
		return false;
	}
	
	public function disconnect()
	{
		pg_close($this->_connection);
	}
	
	public function query($sql)
	{
		if(pg_connection_busy($this->_connection)) {
			return false;
		}
		return pg_send_query($this->_connection, $sql);	
	}
	
	public function getQueryResult()
	{
		$this->_lastQueryResult = pg_get_result($this->_connection);
		return $this->_lastQueryResult;
	}
	
	public function getQueryResultAsArray()
	{
		$this->_lastQueryResult = pg_get_result($this->_connection);
		return pg_fetch_all($this->_lastQueryResult);
	}
	
	public function getAffectedRows($result = null)
	{
		if (null == $result) {
			if (null != $this->_lastQueryResult) {
				return pg_affected_rows($this->_lastQueryResult);
			}
		} else {
			if (is_resource($result)) {
				return pg_affected_rows($result);
			}
		}
		return 0;
	}
	
	public function getSqlState($result = null)
	{
		if (null == $result) {
			if (null != $this->_lastQueryResult) {
				return pg_result_error_field($this->_lastQueryResult, PGSQL_DIAG_SQLSTATE);
			}
		} else {
			if (is_resource($result)) {
				return pg_result_error_field($result, PGSQL_DIAG_SQLSTATE);
			}
		}
		
	}
	
	public function getErrorMessage()
	{
		return pg_last_error($this->_connection);
	}
}