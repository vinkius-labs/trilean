<?php

namespace VinkiusLabs\Trilean\Facades;

use Illuminate\Support\Facades\Facade;

class Trilean extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'trilean.logic';
    }
}
