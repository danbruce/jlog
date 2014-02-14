<?php
/**
 * @file Classes/JLogEmailTransaction.php
 * @brief Implementation of the JLogEmailTransaction class.
 */

/**
 * @class JLogEmailTransaction
 * @brief A transaction for logging by sending an email.
 */
class JLogEmailTransaction extends JLogTransaction
{
    // who the email should go to
    private $to;
    // who the email should come from
    private $from;
    // the subject of the email
    private $subject;

    /**
     * Constructor for the class. Takes the email transaction details as the
     * argument.
     * @param array $details An array of email transaction details.
     * @throws JLogException Throws an exception if something went wrong.
     */
    public function __construct($details)
    {
        try {
            // make sure the $details variable is a valid array
            if (!isset($details) || !is_array($details)) {
                throw new JLogException(
                    'Invalid details for email transaction'
                );
            }
            parent::__construct('EMAIL');
            $this->_fillEmailFields($details);
        } catch (JLogException $e) {
            throw $e;
        }
    }

    // populates the local email fields from the details
    private function _fillEmailFields($details)
    {
        $fields = array('to', 'from', 'subject');
        foreach ($fields as $field) {
            if (isset($details[$field])) {
                $this->$field = $details[$field];
            } else {
                throw new JLogException(
                    'Missing email field "'.$field.'"'
                );
            }
        }
    }

    /**
     * Writes the log by sending the email. This function does nothing unless
     * the $final argument is set to true. This method is overridden from the
     * abstract parent class JLogTransaction.
     * @param bool $final This function does nothing unless this argument is
     * true
     * @return void
     * @throws JLogException Throws an exception if the email failed to send
     */
    public function write($final = false)
    {
        if (!$final) return;

        $body = implode("\r\n", $this->log);
        if (false === mail($this->to, $this->subject, $body, $this->from)) {
            throw new JLogException(
                'Unable to send email transaction.'
            );
        }
    }
}

?>