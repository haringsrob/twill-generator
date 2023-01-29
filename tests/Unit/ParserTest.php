<?php

namespace Haringsrob\TwillGenerator\Tests\Unit;

use Haringsrob\TwillGenerator\Services\Parser;
use Haringsrob\TwillGenerator\Tests\TestCase;

class ParserTest extends TestCase
{
    public function testBasicParser(): void
    {
        $html = <<<HTML
<div>
</div>
HTML;

        $parser = new Parser($html);
        $this->assertCount(0, $parser->getBlockSegments());
    }

    public function testParseSingleBlock(): void
    {
        $html = <<<HTML
<div twill-block="demo">
</div>
HTML;

        $parser = new Parser($html);
        $this->assertCount(1, $parser->getBlockSegments());

        $this->assertEquals('demo', $parser->getBlockSegments()[0]->getBlockName());
        $this->assertCount(0, $parser->getBlockSegments()[0]->getBlockFields());
    }

    public function testParseMultipleBlocks(): void
    {
        $html = <<<HTML
<div twill-block="demo">
</div>
<div twill-block="demo2">
</div>
HTML;

        $parser = new Parser($html);
        $this->assertCount(2, $parser->getBlockSegments());

        $this->assertEquals('demo', $parser->getBlockSegments()[0]->getBlockName());
        $this->assertEquals('demo2', $parser->getBlockSegments()[1]->getBlockName());
    }

    /**
     * @dataProvider supportedFields
     */
    public function testParserSupportedFields($type): void
    {
        $html = <<<HTML
<div twill-block="demo">
    <div twill-$type="field">
    </div>
</div>
HTML;

        $parser = new Parser($html);
        $this->assertCount(1, $parser->getBlockSegments());
        $this->assertCount(1, $parser->getBlockSegments()[0]->getBlockFields());
    }

    public function supportedFields(): array
    {
        return [
            [
                'text',
            ],
            [
                'wysiwyg',
            ],
            [
                'number',
            ],
            [
                'image'
            ]
        ];
    }
}
