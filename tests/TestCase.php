<?php

namespace Haringsrob\TwillGenerator\Tests;

use Haringsrob\TwillGenerator\TwillGeneratorServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            TwillGeneratorServiceProvider::class,
        ];
    }
}
