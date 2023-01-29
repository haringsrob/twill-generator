<?php

namespace Haringsrob\TwillGenerator\Tests\Unit;

use Haringsrob\TwillGenerator\Services\Parser;
use Haringsrob\TwillGenerator\Tests\TestCase;

class FullGeneratorTest extends TestCase
{
    public function testBlockWithSingleField(): void
    {
        $html = <<<HTML
<div twill-block="demo">
    <h1 twill-text="title">
        This will be the title
    </h1>
</div>
HTML;

        $expectedHtml = <<<HTML
<div> <h1>{{ \$input('title') }}</h1> </div>
HTML;

        $expectedClass = <<<PHP
namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Illuminate\Contracts\View\View;

class Demo extends TwillBlockComponent
{
    public function render(): View
    {
        return view('components.twill.blocks.demo');
    }

    public function getForm(): Form
    {
        return Form::make([
            Input::make()->name('title'),
        ]);
    }
}

PHP;

        $parser = new Parser($html);

        $this->assertEquals(
            $expectedHtml,
            $parser->getBlockSegments()[0]->getBlockBladeContent()
        );

        $this->assertEquals(
            $expectedClass,
            $parser->getBlockSegments()[0]->getClass()
        );
    }

    public function testBlockWithAllFields(): void
    {
        $html = <<<HTML
<div twill-block="demo">
    <h1 twill-text="title">
        This will be the title
    </h1>
    <div class="image">
        <img twill-image="cover" src="http://some-url-placeholder" /> 
    </div>
    <div class="prose" twill-wysiwyg="body">
        <p>
          Lorem ipsum
        </p>
    </div>
    <div class="count">
        So far we had <span twill-number="visitors">5</span> visitors
    </div>
</div>
HTML;

        $expectedHtml = <<<HTML
<div> <h1>{{ \$input('title') }}</h1> <div class="image"> <img src='{{ \$image('cover') }}' /> </div> <div class="prose">{{ \$wysiwyg('body') }}</div> <div class="count"> So far we had <span>{{ \$input('visitors') }}</span> visitors </div> </div>
HTML;

        $expectedClass = <<<PHP
namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Illuminate\Contracts\View\View;

class Demo extends TwillBlockComponent
{
    public function render(): View
    {
        return view('components.twill.blocks.demo');
    }

    public function getForm(): Form
    {
        return Form::make([
            Input::make()->name('title'),
            Wysiwyg::make()->name('body'),
            Input::make()->name('visitors')->type('number'),
            Medias::make()->name('cover')->max(1)
        ]);
    }
}

PHP;

        $parser = new Parser($html);

        $this->assertEquals(
            $expectedHtml,
            $parser->getBlockSegments()[0]->getBlockBladeContent()
        );

        $this->assertEquals(
            $expectedClass,
            $parser->getBlockSegments()[0]->getClass()
        );
    }
}
