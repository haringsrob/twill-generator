<?php

namespace Haringsrob\TwillGenerator\Enums;

use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;

enum SupportedFieldType: string
{
    case TEXT = 'text';
    case WYSIWYG = 'wysiwyg';
    case NUMBER = 'number';
    case IMAGE = 'image';
    case HREF = 'href';

    public function getStringForBuilder(string $name): array
    {
        return match ($this) {
            self::TEXT, self::HREF => [
                'class' => Input::class,
                'markup' => "Input::make()->name('$name')",
            ],
            self::WYSIWYG => [
                'class' => Wysiwyg::class,
                'markup' => "Wysiwyg::make()->name('$name')",
            ],
            self::NUMBER => [
                'class' => Input::class,
                'markup' => "Input::make()->name('$name')->type('number')",
            ],
            self::IMAGE => [
                'class' => Medias::class,
                'markup' => "Medias::make()->name('$name')->max(1)",
            ],
        };
    }
}
