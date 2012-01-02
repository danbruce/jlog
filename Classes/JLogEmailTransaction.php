<?php

class JLogEmailTransaction extends JLogTransaction
{
    private $to;
    private $from;
    private $subject;

    public function __construct($details)
    {
        try {
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