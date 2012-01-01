<?php

class JLogMySQLTransaction extends JLogTransaction
{
    private $_pdo = null;
    private $_transactionDatabaseID = null;
    private $_writePtr = 0;
    private $_tablePrefix = '';

    private $_lookupUniqueIDStatement = null;
    private $_insertTransactionStatement = null;
    private $_insertMessageStatement = null;
    private $_updateTransactionModifyDateStatement = null;

    public function __construct($details)
    {
        try {
            $this->_constructPDO($details);
            parent::__construct($this->_generateNewID());
            $this->_writePtr = 0;
        } catch (JSONException $e) {
            throw $e;
        }
    }

    public function write()
    {
        try {
            if (!isset($this->_transactionDatabaseID)) {
                $this->_transactionDatabaseID = $this->_insertTransaction();
            }

            $this->_insertMessages();
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function _constructPDO($details)
    {
        try {
            extract($details, EXTR_OVERWRITE);
            if (isset($tablePrefix)) {
                $this->_tablePrefix = $tablePrefix;
            }
            $pdoString  = 'mysql:dbname='.$database.';';
            $pdoString .= 'host='.$host;
            $this->_pdo = new PDO($pdoString, $username, $password);
            if (!$this->_pdo) {
                throw new JSONException(
                    'Unable to initialize PDO object.'
                );
            }
        } catch (Exception $e) {
            throw new JLogException($e->getMessage());  
        }
    }

    private function _generateNewID()
    {
        try {
            if ($this->_prepareLookupStatement()) {
                $rowCount = 1;
                do {
                    $trans_id = hash('sha256', uniqid('', true));
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
                } while ($rowCount > 0);
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
        $prefix = $this->_tablePrefix;
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
        $prefix = $this->_tablePrefix;
        $queryString  = 'INSERT INTO `'.$prefix.'Transactions` ';
        $queryString .= '(`transactionID`, `createDate`, `modifyDate`)';
        $queryString .= 'VALUES';
        $queryString .= '(:transID, NOW(), NOW())';
        return $this->_prepareGenericStatement(
            '_insertTransactionStatement',
            $queryString
        );
    }

    private function _prepareMessageInsertStatement()
    {
        $prefix = $this->_tablePrefix;
        $queryString  = 'INSERT INTO `'.$prefix.'Messages` ';
        $queryString .= '(`transaction`, `message`, `createDate`)';
        $queryString .= 'VALUES';
        $queryString .= '(:transID, :message, NOW())';
        return $this->_prepareGenericStatement(
            '_insertMessageStatement',
            $queryString
        );
    }

    private function _prepareUpdateTransactionModifyDateStatement()
    {
        $prefix = $this->_tablePrefix;
        $queryString  = 'UPDATE `'.$prefix.'Transactions` ';
        $queryString .= 'SET `'.$prefix.'Transactions`.`modifyDate` = NOW() ';
        $queryString .= 'WHERE `'.$prefix.'Transactions`.`id` = :transID';
        return $this->_prepareGenericStatement(
            '_updateTransactionModifyDateStatement',
            $queryString
        );
    }

    private function _insertTransaction()
    {
        if (!$this->_prepareTransactionInsertStatement()) {
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

        if (!$success) {
            throw new JLogException(
                'Unable to bind params to insert transaction query.'
                );
        }
        if (!$this->_insertTransactionStatement->execute()) {
            throw new JLogException(
                'Unable to execute insert transaction query.'
            );
        }

        return $this->_pdo->lastInsertId();
    }

    private function _insertMessages()
    {
        if (!$this->_prepareMessageInsertStatement()) {
            throw new JLogException(
                'Unable to prepare message insert statement.'
            );
        }

        $success = $this->_insertMessageStatement->bindParam(
            ':transID',
            $this->_transactionDatabaseID,
            PDO::PARAM_INT
        );
        if (!$success) {
            throw new JLogException(
                'Unable to bind :transID param to message insert statement'
            );
        }

        $logSize = count($this->log);

        while($this->_writePtr < $logSize) {
            $message = $this->log[$this->_writePtr];

            $messageString = $message->__toString();
            $success = $this->_insertMessageStatement->bindParam(
                ':message',
                $messageString,
                PDO::PARAM_STR,
                strlen($messageString)
            );
            if (!$success) {
                throw new JLogException(
                    'Unable to bind :message param to message insert statement'
                );
            }
            if (!$this->_insertMessageStatement->execute()) {
                throw new JLogException(
                    'Unable to execute insert message statement'
                );
            }
            $this->_updateTransactionModifyDate();
            $this->_writePtr++;
        }
    }

    private function _updateTransactionModifyDate()
    {
        if (!isset($this->_updateTransactionModifyDateStatement)) {
            $this->_prepareUpdateTransactionModifyDateStatement();
        }

        $success = $this->_updateTransactionModifyDateStatement->bindParam(
            ':transID',
            $this->_transactionDatabaseID,
            PDO::PARAM_INT
        );
        if (!$success) {
            throw new JLogException(
                'Unable to bind :transID param to transaction modify date update statement'
            );
        }

        if (!$this->_updateTransactionModifyDateStatement->execute()) {
            throw new JLogException(
                'Unable to execute transaction modify date update statement'
            );
        }
    }
}

?>