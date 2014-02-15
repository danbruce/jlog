<?php
/**
 * @file Classes/JLogMySQLTransaction.php
 * @brief Implemention of the JLogMySQLTransaction class.
 */

/**
 * @class JLogMySQLTransaction
 * @brief A transaction for writing the log to a MySQL database.
 */
class JLogMySQLTransaction extends JLogTransaction
{
    // the pdo class object pointing to the database
    private $_pdo = null;
    // the transacation database ID
    private $_transactionDatabaseID = null;
    // the pointer to where in the log we last wrote
    private $_writePtr = 0;
    // the prefix on the table names
    private $_tablePrefix = '';

    // a few prepared PDO statements for efficient queries
    private $_lookupUniqueIDStatement = null;
    private $_insertTransactionStatement = null;
    private $_insertMessageStatement = null;
    private $_updateTransactionModifyDateStatement = null;

    /**
     * Constructor for the class.
     * @param array $details The details for connecting to the database.
     * @throws JLogException Throws an exception if we cannot connect to the
     * database.
     */
    public function __construct($details)
    {
        // setup the PDO object
        $this->_constructPDO($details);
        // generate a new transaction ID and pass it to the parent class
        parent::__construct($this->_generateNewID());
        $this->_writePtr = 0;
    }

    /**
     * Inserts the logged objects into the database tables.
     * @param bool $final If true, then we are writing to the log for the last
     * time so we close the connection to the PDO.
     * @return void
     * @throws JLogException Throws an exception if anything goes wrong.
     */
    public function write($final = false)
    {
        // if we don't yet have a transaction database ID, we need to
        // insert the transaction into the database
        if (!isset($this->_transactionDatabaseID)) {
            $this->_transactionDatabaseID = $this->_insertTransaction();
        }

        // write the unlogged messages to the database
        $this->_insertMessages();

        // if this is the final write, close the connection to the database
        if ($final) {
            $this->_destroyPDO();
        }
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
        $this->_pdo = new PDO($pdoString, $username, $password);
        if (!$this->_pdo) {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to initialize PDO object.'
            );
        }
// @codeCoverageIgnoreEnd
    }

    // clears the current pdo object
    private function _destroyPDO()
    {
        $this->_pdo = null;
    }

    // generates a unique transaction ID
    private function _generateNewID()
    {
        // we need to prepare the query that checks for a unique ID
        if ($this->_prepareLookupStatement()) {
            $rowCount = 1;
            // loop until we find a unique id
            do {
                // generate a hashed ID
                $trans_id = hash('sha256', uniqid('', true));
                // bind the parameters to the query
                $success = $this->_lookupUniqueIDStatement->bindParam(
                    ':transID',
                    $trans_id,
                    PDO::PARAM_STR,
                    strlen($trans_id)
                );
                // if binding was successful
                if ($success) {
                    // execute the query
                    if (!$this->_lookupUniqueIDStatement->execute()) {
// @codeCoverageIgnoreStart
                        throw new JLogException(
                            'Unable to execute lookup statement.'
                        );
                    }
// @codeCoverageIgnoreEnd
                    // count how many rows matched this unique ID
                    $rowCount = $this->_lookupUniqueIDStatement->rowCount();
                }
            } while ($rowCount > 0);
            return $trans_id;
        } else {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to prepare unique ID lookup statement.'
            );
        }
// @codeCoverageIgnoreEnd
    }

    // a generic function for preparing all of the above statements
    private function _prepareGenericStatement($statementName, $query)
    {
        // prepare the statement
        $this->$statementName = $this->_pdo->prepare($query);
        return ($this->$statementName) ? true : false;
    }
    
    // prepares the lookup statement for finding unique transaction IDs
    private function _prepareLookupStatement()
    {
        $stmtName = '_lookupUniqueIDStatement';

        $prefix = $this->_tablePrefix;
        $queryString  = 'SELECT `'.$prefix.'Transactions`.`id` ';
        $queryString .= 'FROM `'.$prefix.'Transactions` ';
        $queryString .= 'WHERE `'.$prefix.
                        'Transactions`.`transactionID` = :transID ';
        $queryString .= 'LIMIT 1';
        return $this->_prepareGenericStatement(
            $stmtName,
            $queryString
        );
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

    // prepare the statement which updates the transacations last modifyDate
    // field
    private function _prepareUpdateTransactionModifyDateStatement()
    {
        $stmtName = '_updateTransactionModifyDateStatement';
        if ($this->$stmtName) {
            return true;
        }

        $prefix = $this->_tablePrefix;
        $queryString  = 'UPDATE `'.$prefix.'Transactions` ';
        $queryString .= 'SET `'.$prefix.'Transactions`.`modifyDate` = NOW() ';
        $queryString .= 'WHERE `'.$prefix.'Transactions`.`id` = :transID ';
        $queryString .= 'LIMIT 1';
        return $this->_prepareGenericStatement(
            $stmtName,
            $queryString
        );
    }

    // inserts a new transaction in the database
    private function _insertTransaction()
    {
        // check if the statement for inserting transactions has been prepared
        if (!$this->_prepareTransactionInsertStatement()) {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to prepare transaction insert statement.'
            );
        }
// @codeCoverageIgnoreEnd

        // bind the params
        $success = $this->_insertTransactionStatement->bindParam(
            ':transID',
            $this->id,
            PDO::PARAM_STR,
            strlen($this->id)
        );

        // if we failed to bind the params throw an exception
        if (!$success) {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to bind params to insert transaction query.'
                );
        }
// @codeCoverageIgnoreEnd
        // execute the actual insertion
        if (!$this->_insertTransactionStatement->execute()) {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to execute insert transaction query.'
            );
        }
// @codeCoverageIgnoreEnd

        // return the PDO classes last insert ID as the new transaction
        // database ID
        return $this->_pdo->lastInsertId();
    }

    // inserts a message into the database
    private function _insertMessages()
    {
        // check if the statement for inserting messages has been prepared
        if (!$this->_prepareMessageInsertStatement()) {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to prepare message insert statement.'
            );
        }
// @codeCoverageIgnoreEnd

        // bind the params
        $success = $this->_insertMessageStatement->bindParam(
            ':transID',
            $this->_transactionDatabaseID,
            PDO::PARAM_INT
        );

        // if we failed to bind the params throw an exception
        if (!$success) {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to bind :transID param to message insert statement'
            );
        }
// @codeCoverageIgnoreEnd

        // count how many elements are in this log
        $logCount = count($this->log);
        // and we'll loop while the write pointer is less than the number
        // of elements in the log (ie we have objects remaining to be written)
        while($this->_writePtr < $logCount) {
            // fetch the message from the log
            $message = $this->log[$this->_writePtr];
            // turn the message into a JSON string
            $messageString = $message->__toString();
            // bind the message string to the parameters of the query
            $success = $this->_insertMessageStatement->bindParam(
                ':message',
                $messageString,
                PDO::PARAM_STR,
                strlen($messageString)
            );
            // if we failed to bind the params throw an exception
            if (!$success) {
// @codeCoverageIgnoreStart
                throw new JLogException(
                    'Unable to bind :message param to message insert statement'
                );
            }
// @codeCoverageIgnoreEnd
            // perform the actual insertion
            if (!$this->_insertMessageStatement->execute()) {
// @codeCoverageIgnoreStart
                throw new JLogException(
                    'Unable to execute insert message statement'
                );
            }
// @codeCoverageIgnoreEnd
            // increment the write pointer
            $this->_writePtr++;
        }
        // update the last time the transaction was modified
        $this->_updateTransactionModifyDate();
    }

    // executes the update query to set the last modification timestamp of the
    // transaction to NOW()
    private function _updateTransactionModifyDate()
    {
        // prepare the statement
        $this->_prepareUpdateTransactionModifyDateStatement();

        // bind the params
        $success = $this->_updateTransactionModifyDateStatement->bindParam(
            ':transID',
            $this->_transactionDatabaseID,
            PDO::PARAM_INT
        );
        // if the params failed to bind throw an exception
        if (!$success) {
// @codeCoverageIgnoreStart
            throw new JLogException(
                'Unable to bind :transID param to transaction modify date update statement'
            );
        }
// @codeCoverageIgnoreEnd

        // execute the update query
        if (!$this->_updateTransactionModifyDateStatement->execute()) {
// @codeCoverageIgnoreStart            
            throw new JLogException(
                'Unable to execute transaction modify date update statement'
            );
        }
// @codeCoverageIgnoreEnd
    }
}

?>