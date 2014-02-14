<?php

namespace Jlog;

interface JLogInterface
{
    public static function log($item, $level = JLog::LEVEL_WARNING);
    public static function fatal($item);
    public static function error($item);
    public static function warning($item);
    public static function notice($item);
    public static function debug($item);
    public static function flush();
}