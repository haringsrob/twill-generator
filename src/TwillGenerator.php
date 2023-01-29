<?php

namespace Haringsrob\TwillGenerator;

class TwillGenerator
{
    public function tidyHtml(string $html): string
    {
        // This is not ideal atm as it does no indentation whatsoever, might require blade formatter to fix this at
        // some point.
        return $html;
    }
}
