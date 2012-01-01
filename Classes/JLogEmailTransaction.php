<?php

class JLogEmailTransaction extends JLogTransaction
{
    public function __construct($details)
    {
        try {
            parent::__construct($this->_generateNewID($details));
        } catch (JSONException $e) {
            throw $e;
        }
    }

    private function _generateNewID($details)
    {
        return 'asdf';
    }

    public function write()
    {
        throw new JLogException(
            'Write function not implemented in JLogEmailTransaction class.'
        );
    }
}

?>