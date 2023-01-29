<?php

namespace Haringsrob\TwillGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class TwillGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Haringsrob\TwillGenerator\TwillGenerator::class;
    }
}
