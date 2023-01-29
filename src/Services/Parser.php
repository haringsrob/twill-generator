<?php

namespace Haringsrob\TwillGenerator\Services;

use Haringsrob\TwillGenerator\Services\Models\Block;
use Illuminate\Support\Collection;
use PHPHtmlParser\Dom;

class Parser
{
    private Dom $dom;
    private ?Collection $blocks = null;

    public function __construct(public string $html)
    {
        $this->dom = new Dom();
        $this->dom->loadStr($this->html);
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlockSegments(): Collection
    {
        if (! $this->blocks) {
            $this->blocks = collect();

            /** @var Dom\Node\HtmlNode $node */
            foreach ($this->dom->find('*[twill-block]') as $node) {
                $this->blocks->push(new Block($node->getAttribute('twill-block'), $node));
            }

        }
        return $this->blocks;
    }
}
