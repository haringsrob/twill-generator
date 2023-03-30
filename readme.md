# TwillGenerator

## What is it

This package can generate blocks based on html markup for rapidly building sites in Twill 3.

### Input
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

### Output



## Supported fields

It currently supports:
- text
- wysiwyg
- number
- image
- href

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

## Installation

Via Composer

Add repo:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/haringsrob/twill-generator"
    }
  ]
}
```

Require:

``` bash
$ composer require haringsrob/twill-generator --dev
```
