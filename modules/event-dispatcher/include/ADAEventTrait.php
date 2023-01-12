<?php

namespace Lynxlab\ADA\Module\EventDispatcher;

trait ADAEventTrait
{
    public static function getConstants()
    {
        // "static::class" here does the magic
        $reflectionClass = new \ReflectionClass(static::class);
        return $reflectionClass->getConstants();
    }
}