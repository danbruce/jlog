<?php

namespace JLog\Storage;

use JLog\Transaction;

interface StorageInterface
{
    public function isValidTransactionId($id);
    public function setup($settings);
    public function preWrite(Transaction $transaction);
    public function write($string);
    public function postWrite(Transaction $transaction);
    public function beforeBufferedWrite(Transaction $transaction);
    public function afterBufferedWrite(Transaction $transaction);
    public function close();
}