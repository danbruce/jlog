<?php

namespace JLog\Storage;

use JLog\Exception,
    JLog\EmailWrapper;

/**
    @class EmailStorage
    @brief A "storage" type for emailing logs
 */
class EmailStorage
    extends AbstractStorage
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

    public function setup($settings)
    {
        $this->_body = '';
        if (!isset($settings['to']) || !filter_var($settings['to'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid "to" email address.');
        }
        if (!isset($settings['from']) || !filter_var($settings['from'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid "from" email address.');
        }
        $this->_to = $settings['to'];
        $this->_from = $settings['from'];
        $this->_subject = isset($settings['subject']) && strlen($settings['subject']) ?
            $settings['subject'] : 'No Subject';
        self::$_emailWrapper = new EmailWrapper;
    }

    public function write($string)
    {
        $this->_body .= $string.PHP_EOL;
    }

    public function close()
    {
        if (strlen($this->_body)) {
            self::$_emailWrapper->sendEmail(
                $this->_to, $this->_from, $this->_subject, $this->_body
            );
        }
    }

    public static function setEmailWrapper($wrapper)
    {
        self::$_emailWrapper = $wrapper;
    }
}

?>