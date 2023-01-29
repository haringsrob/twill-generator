<?php

namespace Haringsrob\TwillGenerator\Tests\Unit;

use Haringsrob\TwillGenerator\Services\Parser;
use Haringsrob\TwillGenerator\Tests\TestCase;

class ParserReplaceTest extends TestCase
{
    public function testSingleBlockEmpty(): void
    {
        $html = <<<HTML
<div twill-block="demo"></div>
HTML;

        $parser = new Parser($html);

        $this->assertEquals('<div></div>', $parser->getBlockSegments()[0]->getBlockBladeContent());
    }

    public function testSingleBlockWithMarkup(): void
    {
        $html = <<<HTML
<div twill-block="demo">
<div class="some-class"><span>Hello world!</span></div>
</div>
HTML;

        $parser = new Parser($html);

        $this->assertEquals(
            '<div> <div class="some-class"><span>Hello world!</span></div> </div>',
            $parser->getBlockSegments()[0]->getBlockBladeContent()
        );
    }

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

        $parser = new Parser($html);

        $this->assertEquals(
            $expectedHtml,
            $parser->getBlockSegments()[0]->getBlockBladeContent()
        );
    }
}
