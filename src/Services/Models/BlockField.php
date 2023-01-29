<?php

namespace Haringsrob\TwillGenerator\Services\Models;

use Haringsrob\TwillGenerator\Enums\SupportedFieldType;
use Illuminate\Support\Str;
use PHPHtmlParser\Dom\Node\HtmlNode;
use PHPHtmlParser\Dom\Node\TextNode;

class BlockField
{
    protected string $name;
    protected ?string $imageRatio = null;

    public function __construct(protected SupportedFieldType $type, protected HtmlNode $node)
    {
        $this->name = $this->node->getAttribute('twill-' . $this->type->value);
    }

    public function getFieldName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return Str::title(Str::replace(['_', '-'], ' ', $this->name));
    }

    public function getType(): SupportedFieldType
    {
        return $this->type;
    }

    public function replaceDom(): void
    {
        $this->node->removeAttribute('twill-' . $this->type->value);

        match ($this->type) {
            SupportedFieldType::HREF => $this->handleHref(),
            SupportedFieldType::IMAGE => $this->handleImage(),
            SupportedFieldType::WYSIWYG => $this->handleWysiwyg(),
            default => $this->node->firstChild()->setText('{{ $input(\'' . $this->getFieldName() . '\') }}'),
        };
    }

    public function getImageRatio(): string
    {
        if (! $this->imageRatio) {
            if ($ratio = $this->node->getAttribute('twill-image-ratio')) {
                $this->node->removeAttribute('twill-image-ratio');
                $this->imageRatio = $ratio;
            } else {
                $this->imageRatio = '16 / 9';
            }
        }

        return $this->imageRatio;
    }

    private function handleImage(): void
    {
        $this->node->setAttribute('src', '{{ $image(\'' . $this->getFieldName() . '\', \'default\') }}', false);
    }

    private function handleHref(): void
    {
        $this->node->setAttribute('href', '{{ $input(\'' . $this->getFieldName() . '\') }}', false);
    }

    private function handleWysiwyg()
    {
        /** @var HtmlNode $child */
        foreach ($this->node->getChildren() as $child) {
            $child->delete();
        }
        $this->node->addChild(new TextNode('{{ $input(\'' . $this->getFieldName() . '\') }}'));
    }
}
