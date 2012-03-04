<?php
/**
 * @file Classes/JLogTransaction.php
 * @brief Implementation of the JLogTransaction class.
 */

/**
 * @class JLogTransaction
 * @brief Abstract base class for all transactions.
 */
abstract class JLogTransaction
{
    /** A unique transaction id. */
    public $id;
    /** An array to hold all items to be logged. */
    protected $log;
    /** A bool indicating whether this transaction is functioning. */
    public $functioning;

    /**
     * Constructor for the class.
     * @param mixed $id The unique identifier for this transaction.
     */
    protected function __construct($id)
    {
        $this->id = $id;
        $this->log = array();
        $this->functioning = true;
    }

    /**
     * Logs the object $obj onto this transaction at the specified $level.
     * @param mixed $obj The object to be logged.
     * @param int $level The logging level.
     * @return void
     */
    public final function log($obj, $level)
    {
        // if this transaction is not functioning we return
        if (!$this->functioning) return;

        // add the object to the log
        array_push(
            $this->log,
            new JLogMessage($this, $obj, $level)
        );

        // if our settings say to write immediately, we write it
        if(JLogSettings::$WriteImmediately) {
            $this->write();
        }
    }
    
    /**
     * Removes all objects currently in this transaction's log.
     * @return void
     */
    public final function flush()
    {
        $this->log = array();
    }

    /**
     * All subclasses must implement a write function which should write all of
     * the not-yet-written objects from the log.
     * @param bool $final An optional parameter indicating this whether this
     * write is the final write. Some transaction types only make sense to write
     * on the final write.
     * @return void
     */
    public abstract function write($final = false);
}

?>