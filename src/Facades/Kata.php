<?php

namespace Kata\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string greet(?string $name = null)
 * @method static string version()
 *
 * @see \Kata\Kata
 */
class Kata extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'kata';
    }
}
