<?php

class JLogFileTransaction extends JLogTransaction
{
    private $_rootFolder = null;

    public function __construct($details)
    {
        try {
            parent::__construct(
                $this->_generateNewID(
                    $details
                )
            );
        } catch (JSONException $e) {
            throw $e;
        }
    }

    private function _generateNewID($details)
    {
        if (is_array($details) && isset($details['rootFolder'])) {
                $this->_rootFolder = $details['rootFolder'];
        } else {
            throw new JLogException(
                'Root folder not specified for file storage method.'
            );
        }

        do {
            $trans_id = hash('sha256', uniqid('', true));
        } while (file_exists($this->_rootFolder.PATH_SEPARATOR.$trans_id));

        return $trans_id;
    }

    public function write()
    {
        throw new JLogException(
            'Write function not implemented in JLogFileTransaction class.'
        );
    }
}

?>