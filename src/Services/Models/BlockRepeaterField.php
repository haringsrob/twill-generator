<?php

namespace Haringsrob\TwillGenerator\Services\Models;

use Haringsrob\TwillGenerator\Enums\SupportedFieldType;
use PHPHtmlParser\Dom\Node\HtmlNode;

class BlockRepeaterField extends BlockField
{
    public function __construct(
        protected SupportedFieldType $type,
        protected HtmlNode $node,
        protected string $group
    ) {
        $this->name = $this->node->getAttribute('twill-' . $this->group . '-' . $this->type->value);
    }

    public function replaceDom(): void
    {
        $this->node->removeAttribute('twill-' . $this->group . '-' . $this->type->value);

        match ($this->type) {
            SupportedFieldType::HREF => $this->handleHref(),
            SupportedFieldType::IMAGE => $this->handleImage(),
            SupportedFieldType::WYSIWYG => $this->handleWysiwyg(),
            default => $this->handleInput(),
        };
    }

    private function handleInput(): void
    {
        $this->node->firstChild()->setText(
            '{{ $' . $this->group . '->block()->input(\'' . $this->getFieldName() . '\') }}',
        );
    }

    private function handleImage(): void
    {
        $this->node->setAttribute(
            'src',
            '{{ $' . $this->group . '->block()->image(\'' . $this->getFieldName() . '\', \'default\') }}',
            false
        );
    }

    private function handleHref(): void
    {
        $this->node->setAttribute(
            'href',
            '{{ $' . $this->group . '->block()->input(\'' . $this->getFieldName() . '\') }}',
            false
        );
    }

    private function handleWysiwyg(): void
    {
        /** @var HtmlNode $child */
        foreach ($this->node->getChildren() as $child) {
            $child->delete();
        }
        $this->node->addChild(
            new TextNode('{{ $' . $this->group . '->block()->input(\'' . $this->getFieldName() . '\') }}')
        );
    }
}
