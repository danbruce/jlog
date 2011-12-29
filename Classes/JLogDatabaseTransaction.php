<?php

class JLogDatabaseTransaction extends JLogTransaction
{
	private $_pdo = null;
	private $_lookupUniqueIDStatement = null;
	private $_insertTransactionStatement = null;
	private $_insertMessageStatement = null;

	public function __construct()
	{
		try {
			$this->_constructPDO();
			$transaction_id = $this->_generateNewID();
			parent::__construct($transaction_id);
		} catch (JSONException $e) {
			throw $e;
		}
	}

	public function write()
	{
		if(!$this->_prepareTransactionInsertStatement()) {
			throw new JLogException(
				'Unable to prepare transaction insert statement.'
			);
		}

		$success = $this->_insertTransactionStatement->bindParam(
			':transID',
			$this->id,
			PDO::PARAM_STR,
			strlen($this->id)
		);
		if(!$success) {
			throw new JLogException(
				'Unable to bind params to insert transaction query.'
			);
		}
		if(!$this->_insertTransactionStatement->execute()) {
			throw new JLogException(
				'Unable to execute insert transaction query.'
			);
		}

		$lastRowID = $this->_pdo->lastInsertId();

		if(!$this->_prepareMessageInsertStatement()) {
			throw new JLogException(
				'Unable to prepare message insert statement.'
			);
		}

		$success = $this->_insertMessageStatement->bindParam(
			':transID',
			$lastRowID,
			PDO::PARAM_STR,
			strlen($this->id)
		);
		if(!$success) {
			throw new JLogException(
				'Unable to bind :transID param to message insert statement'
			);
		}

		reset($this->log);
		foreach($this->log as $message) {
			$messageString = $message->__toString();
			$success = $this->_insertMessageStatement->bindParam(
				':message',
				$messageString,
				PDO::PARAM_STR,
				strlen($messageString)
			);
			if(!$success) {
				throw new JLogException(
					'Unable to bind :message param to message insert statement'
				);
			}
			if(!$this->_insertMessageStatement->execute()) {
				throw new JLogException(
					'Unable to execute insert message statement'
				);
			}
		}
	}

	private function _constructPDO()
	{
		try {
			extract(JLogSettings::$DBInfo, EXTR_OVERWRITE);
			$pdoString  = $driver.':';
			$pdoString .= 'dbname='.$database.';';
			$pdoString .= 'host='.$host;
			$this->_pdo = new PDO($pdoString,$username,$password);
			if (!$this->_pdo) {
				throw new JSONException(
					'Unable to initialize PDO object.'
				);
			}
		} catch (Exception $e) {
			throw new JSONException($e->getMessage());	
		}
	}

	private function _generateNewID()
	{
		try {
			if($this->_prepareLookupStatement()) {
				$rowCount = 1;
				do {
					$trans_id = hash('sha256', JLogger::generateRandomString());
					$success = $this->_lookupUniqueIDStatement->bindParam(
						':transID',
						$trans_id,
						PDO::PARAM_STR,
						strlen($trans_id)
					);
					if ($success) {
						if (!$this->_lookupUniqueIDStatement->execute()) {
							throw new JLogException(
								'Unable to execute lookup statement.'
							);
						}
						$rowCount = $this->_lookupUniqueIDStatement->rowCount();
					}
				} while($rowCount > 0);
				return $trans_id;
			} else {
				throw new JLogException(
					'Unable to prepare unique ID lookup statement.'
				);
			}
		} catch (Exception $e) {
			throw new JLogException($e->getMessage());
		}
	}

	private function _prepareGenericStatement($statementName, $query)
	{
		if ($this->$statementName) {
			return true;
		}

		try {
			$this->$statementName = $this->_pdo->prepare($query);
			if ($this->$statementName) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}
	
	private function _prepareLookupStatement()
	{
		$prefix = JLogSettings::$DBInfo['tablePrefix'];
		$queryString  = 'SELECT `'.$prefix.'Transactions`.`id` ';
		$queryString .= 'FROM `'.$prefix.'Transactions` ';
		$queryString .= 'WHERE `'.$prefix.
						'Transactions`.`transactionID` = :transID ';
		$queryString .= 'LIMIT 1';
		return $this->_prepareGenericStatement(
			'_lookupUniqueIDStatement',
			$queryString
		);
	}

	private function _prepareTransactionInsertStatement()
	{
		$prefix = JLogSettings::$DBInfo['tablePrefix'];
		$queryString  = 'INSERT INTO `'.$prefix.'Transactions` ';
		$queryString .= '(`transactionID`)';
		$queryString .= 'VALUES';
		$queryString .= '(:transID)';
		return $this->_prepareGenericStatement(
			'_insertTransactionStatement',
			$queryString
		);
	}

	private function _prepareMessageInsertStatement()
	{
		$prefix = JLogSettings::$DBInfo['tablePrefix'];
		$queryString  = 'INSERT INTO `'.$prefix.'Messages` ';
		$queryString .= '(`transaction`, `message`)';
		$queryString .= 'VALUES';
		$queryString .= '(:transID, :message)';
		return $this->_prepareGenericStatement(
			'_insertMessageStatement',
			$queryString
		);
	}
}

?>