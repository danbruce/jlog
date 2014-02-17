<?php
/**
    @file JLog/Storage/EmailStorage.php
    @brief Contains the class definition of JLog::Storage::EmailStorage.
 */

/**
    @namespace JLog::Storage
    @brief A namespace containing all the storage mechanisms that JLog uses.
 */
namespace JLog\Storage;

use JLog\Exception,
    JLog\EmailWrapper;

/**
    @class JLog::Storage::EmailStorage
    @brief A "storage" type for emailing logs
 */
class EmailStorage
    extends AbstractStorage
    implements StorageInterface
{
    // who the email should go to
    private $_to;
    // who the email should come from
    private $_from;
    // the subject of the email
    private $_subject;
    // the body of the email
    private $_body;

    // a pointer to an email wrapper
    private static $_emailWrapper;

    /**
        Sets up the storage mechanism for the given settings.
        @param array $settings The settings to use for this storage.
        @throws Exception Throws an exception if the settings are invalid.
     */
    public function setup($settings)
    {
        parent::setup($settings);

        // set the body of the email empty
        $this->_body = '';

        // validate the "to" address
        if (!isset($settings['to']) || !filter_var($settings['to'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid "to" email address.');
        }
        $this->_to = $settings['to'];

        // validate the "from" address
        if (!isset($settings['from']) || !filter_var($settings['from'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid "from" email address.');
        }
        $this->_from = $settings['from'];
        
        // check if a subject is set (if not use a default)
        $this->_subject = isset($settings['subject']) && strlen($settings['subject']) ?
            $settings['subject'] : 'Sent by JLog';

        // instantiate the email wrapper
        self::$_emailWrapper = new EmailWrapper;
    }

    /**
        Called to perform the actual writing to the storage mechanism. Note that we only append
        the logged items to a buffer until EmailStorage::close is called.
        @param string $string The string to be logged.
     */
    public function write($string)
    {
        // for email storage we simply append to the body with a line break
        $this->_body .= $string.PHP_EOL;
    }

    /**
        Called when the logging system is being flushed. This method should send the actual email.
     */
    public function close()
    {
        parent::close();
        // only send the email if the body is non-empty
        if (strlen($this->_body)) {
            self::$_emailWrapper->sendEmail(
                $this->_to, $this->_from, $this->_subject, $this->_body
            );
        }
    }

    /**
        Sets the email wrapper object. This object is used to actually send the email and is an
        instance of JLog::EmailWrapper by default.
        @param EmailWrapper $wrapper The email wrapper to be used.
    */
    public static function setEmailWrapper($wrapper)
    {
        self::$_emailWrapper = $wrapper;
    }
}

?>