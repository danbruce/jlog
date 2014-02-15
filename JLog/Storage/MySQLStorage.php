<?php

namespace JLog\Storage;

use JLog\Exception;

/**
 * @class MySQLStorage
 * @brief A storage class for writing to a MySQL database
 */
class MySQLStorage
    extends AbstractStorage
    implements StorageInterface
{
    // the pdo class object pointing to the database
    private $_pdo = null;
    // the transacation database ID
    private $_transactionDatabaseID = null;
    // the prefix on the table names
    private $_tablePrefix = '';

    // a few prepared PDO statements for efficient queries
    private $_insertTransactionStatement = null;
    private $_insertMessageStatement = null;

    public function isValidTransactionId($id)
    {
        if (isset($this->_transactionDatabaseID)) {
            return true;
        }

        try {
            $this->_transactionDatabaseID = intval($this->_insertTransaction($id));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function setup($settings)
    {
        $this->_constructPDO($settings);
    }

    public function write($string)
    {
        $this->_prepareMessageInsertStatement();
        $stmt = $this->_insertMessageStatement;
        $stmt->bindParam(
            ':transID',
            $this->_transactionDatabaseID,
            \PDO::PARAM_INT
        );
        $stmt->bindParam(
            ':message',
            $string,
            \PDO::PARAM_STR,
            strlen($string)
        );
        $stmt->execute();
    }

    public function close()
    {
        $this->_pdo = null;
    }

    // creates the PDO object we will use to write to the database
    private function _constructPDO($details)
    {
        extract($details, EXTR_OVERWRITE);
        if (isset($tablePrefix)) {
            $this->_tablePrefix = $tablePrefix;
        }
        $pdoString  = 'mysql:dbname='.$database.';';
        $pdoString .= 'host='.$host;
        try {
            $this->_pdo = new \PDO($pdoString, $username, $password);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    // a generic function for preparing all of the above statements
    private function _prepareGenericStatement($statementName, $query)
    {
        // prepare the statement
        $this->$statementName = $this->_pdo->prepare($query);
        return ($this->$statementName) ? true : false;
    }

    // prepares the transaction insert statement for adding new transactions to
    // the database
    private function _prepareTransactionInsertStatement()
    {
        $stmtName = '_insertTransactionStatement';

        $prefix = $this->_tablePrefix;
        $queryString  = 'INSERT INTO `'.$prefix.'Transactions` ';
        $queryString .= '(`transactionID`, `createDate`, `modifyDate`)';
        $queryString .= 'VALUES';
        $queryString .= '(:transID, NOW(), NOW())';
        return $this->_prepareGenericStatement(
            $stmtName,
            $queryString
        );
    }

    // prepares the message insert statement for adding new messages to the
    // database
    private function _prepareMessageInsertStatement()
    {
        $stmtName = '_insertMessageStatement';
        if ($this->$stmtName) {
            return true;
        }

        $prefix = $this->_tablePrefix;
        $queryString  = 'INSERT INTO `'.$prefix.'Messages` ';
        $queryString .= '(`transaction`, `message`, `createDate`)';
        $queryString .= 'VALUES';
        $queryString .= '(:transID, :message, NOW())';
        return $this->_prepareGenericStatement(
            $stmtName,
            $queryString
        );
    }

    // inserts a new transaction in the database
    private function _insertTransaction($transactionId)
    {
        // ensure the statement is prepared
        $this->_prepareTransactionInsertStatement();
        // fetch the statement and bind the parameter
        $stmt = $this->_insertTransactionStatement;
        $stmt->bindParam(
            ':transID',
            $transactionId,
            \PDO::PARAM_STR,
            strlen($transactionId)
        );
        // execute the actual insertion
        if (!$stmt->execute()) {
            throw new Exception(
                'Unable to execute insert transaction query.'
            );
        }

        // return the PDO classes last insert ID as the new transaction database ID
        return $this->_pdo->lastInsertId();
    }
}

?>