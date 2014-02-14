<?php

namespace Jlog;

interface JLogInterface
{
    public static function log(mixed $item, $level = JLog::LEVEL_WARNING);
    public static function fatal(mixed $item);
    public static function error(mixed $item);
    public static function warning(mixed $item);
    public static function notice(mixed $item);
    public static function debug(mixed $item);
    public static function flush();
}