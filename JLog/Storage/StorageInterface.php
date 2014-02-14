<?php

namespace JLog\Storage;

interface StorageInterface
{
    public function setup($settings = null);
    public function write($string);
    public function beforeBufferedWrite();
    public function afterBufferedWrite();
    public function close();
}