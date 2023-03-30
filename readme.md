# TwillGenerator

## What is it

This package can generate blocks based on html markup for rapidly building sites in Twill 3.

You can take a template file, annotate it and feed it to the package where it will take care of converting it.

Currently the blade files are NOT formatted, so you need to reformat them using your editor of choice.

This is a dev/proof of concept package. Feel free to submit more features! This package is not my priority, so I most
likely will not actively work on your feature requests.

## Installation

``` bash
$ composer require haringsrob/twill-generator --dev
```

## Usage

### Input

While this example only contains "one block" you can use a full html file as well. It will strip out what is not needed.

```html
<div class="max-w-5xl mx-auto flex items-center mt-16 mb-16 md:mt-24 md:mb-24 px-8 md:px-0" twill-block="hero">
  <div class="max-w-4xl flex-col">
    <h1 class="text-5xl font-black" twill-wysiwyg="title">
      The next generation page builder
    </h1>
    <div class="prose prose-lg py-8 max-w-3xl" twill-wysiwyg="text">
      <p>The generator that simply takes your markup and converts it into building blocks</p>
    </div>
    <div>
      <!-- Links todo -->
      <a twill-href="link_url" href="https://webisolv.com/nl/contact-us"
         class="inline px-6 py-4 border border-brown-900 text-brown-900 transition-colors hover:bg-brown-900 hover:text-white font-bold">
        <span twill-text="link_text">Contact us</span>
      </a>
    </div>
  </div>
</div>
```

See (./tests/example.html)[this example] for a more complete file.

Then we can run `twill-generator:generate-blocks-for-file {file}` and it will extract the blocks into classes.

You will have to confirm what the parser found:

```
The following block components will be created:
+---------------+---------------+---------------+------------+-------------------------------------------------------+----------------------------------------------------+
| block name    | label         | field name    | field type | dir                                                   | class                                              |
+---------------+---------------+---------------+------------+-------------------------------------------------------+----------------------------------------------------+
| hero          |               |               |            | views/components/twill/blocks/hero.blade.php          | /app/View/Components/Twill/Blocks/Hero.php         |
|               | Link Text     | link_text     | text       |                                                       |                                                    |
|               | Title         | title         | wysiwyg    |                                                       |                                                    |
|               | Text          | text          | wysiwyg    |                                                       |                                                    |
|               | Link Url      | link_url      | href       |                                                       |                                                    |
+---------------+---------------+---------------+------------+-------------------------------------------------------+----------------------------------------------------+
```

### Output

`resources/components/twill/blocks/quote.blade.php`

```blade
<div class="w-full mt-12 md:mt-24 bg-darkblue-500 text-brown-500 p-8 md:p-8 max-w-5xl mx-auto">
  <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-12">
    <div><h3 class="text-3xl font-bold">{{ $input('title') }}</h3></div>
    <div class="text-xl">{{ $input('quote') }}</div>
  </div>
  <img twill-image-ratio="16/9" src='{{ $image('content_image', 'default') }}'/></div>
```

`app/View/Components/Twill/Blocks/Quote.php`

```php
<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Illuminate\Contracts\View\View;

class Quote extends TwillBlockComponent
{
    public function render(): View
    {
        return view('components.twill.blocks.quote');
    }

    public function getForm(): Form
    {
        return Form::make([
            Input::make()->name('title'),
            Wysiwyg::make()->name('quote'),
            Medias::make()->name('content_image')->max(1)
        ]);
    }

    public static function getCrops(): array
    {
        return ['content_image' => ['default' => [['name' => 'default', 'ratio' => 16/9]]]];
    }
}
```

As you can see, we just feed it some annotate html, and it will take care of extracting and replacing the placeholder
content to make the blocks.

## Supported fields

It currently supports:

- text
- wysiwyg
- number
- image
- href
- repeaters

## Repeaters

This package can also generate inline (json) repeaters:

```html

<div twill-block="with-repeater">
  <div twill-repeater="items" class="repeater-item">
    <div twill-items-text="title">
      Hello world
    </div>
  </div>
  <div class="repeater-item">
    <div>
      Hello world 1
    </div>
  </div>
</div>
```

```blade
<div> @foreach($repeater('items') as $items)
    <div class="repeater-item">
      <div>{{ $items->block()->input('title') }}</div>
    </div>
  @endforeach </div>
```

```php
<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Illuminate\Contracts\View\View;

class WithRepeater extends TwillBlockComponent
{
    public function render(): View
    {
        return view('components.twill.blocks.with-repeater');
    }

    public function getForm(): Form
    {
        return Form::make([
            InlineRepeater::make()
            ->name('items')
            ->fields([
                Input::make()->name('title')
            ])
        ]);
    }
}
```
