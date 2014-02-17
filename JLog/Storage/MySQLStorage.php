<?php
/**
    @file JLog/Storage/MySQLStorage.php
    @brief Contains the class definition of JLog::Storage::MySQLStorage.
 */

/**
    @namespace JLog::Storage
    @brief A namespace containing all the storage mechanisms that JLog uses.
 */
namespace JLog\Storage;

use JLog\Exception,
    JLog\Transaction;

/**
    @class JLog::Storage::MySQLStorage
    @brief A storage class for writing to a MySQL database
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

    /**
        Returns whether or not a transaction ID is unique in the Transactions table.
        @param string $id A new transaction id.
        @retval boolean Returns true if the new transaction id is unique (false otherwise)
     */
    public function isValidTransactionId($id)
    {
        // if we've already got a transaction database ID, we're fine
        if (isset($this->_transactionDatabaseID)) {
            return true;
        }

        try {
            // inserts the new transaction and retrieves the database row ID
            $this->_transactionDatabaseID = intval($this->_insertTransaction($id));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
        Sets up the PDO object for the given settings.
        @param array $settings The settings to use for this storage.
        @throws Exception Throws an exception if the settings are invalid.
     */
    public function setup($settings)
    {
        parent::setup($settings);
        $this->_constructPDO($settings);
    }

    /**
        Called before writing to the storage mechanism. Begins a database transaction.
        @param Transaction $transaction The transaction of the write.
     */
    public function preWrite(Transaction $transaction)
    {
        parent::preWrite($transaction);

        // prepare the insert message statement
        $this->_prepareMessageInsertStatement();
        // bind the transaction ID (never changes within a transaction)
        $this->_insertMessageStatement->bindParam(
            ':transID',
            $this->_transactionDatabaseID,
            \PDO::PARAM_INT
        );
        $this->_pdo->beginTransaction();
    }

    /**
        Inserts the new message into the database.
        @param string $string The string to be logged.
     */
    public function write($string)
    {
        // bind the actual message
        $this->_insertMessageStatement->bindParam(
            ':message',
            $string,
            \PDO::PARAM_STR,
            strlen($string)
        );
        try {
            // execute the insertion
            $this->_insertMessageStatement->execute();
        } catch (\PDOException $e) {
            // if something went wrong, try to rollback
            if ($this->_pdo->inTransaction()) {
                $this->_pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
        Called after writing to the storage mechanism. Commits the database transaction.
        @param Transaction $transaction The transaction of the write.
     */
    public function postWrite(Transaction $transaction)
    {
        parent::postWrite($transaction);
        // if we're in a transaction, commit it
        if ($this->_pdo->inTransaction()) {
            $this->_pdo->commit();
        }
    }

    /**
        Called when the logging system is being flushed. Clears the internal PDO object.
     */
    public function close()
    {
        parent::close();
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