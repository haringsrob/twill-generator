<?php

namespace Haringsrob\TwillGenerator\Services\Models;

use A17\Twill\Services\Forms\InlineRepeater;
use Haringsrob\TwillGenerator\Enums\SupportedFieldType;
use Illuminate\Support\Collection;
use Nette\PhpGenerator\PhpNamespace;
use PHPHtmlParser\Dom\Node\HtmlNode;
use PHPHtmlParser\Dom\Node\TextNode;

class BlockRepeater
{
    /**
     * @var Collection<BlockRepeaterField>|null
     */
    private ?Collection $fields = null;

    public function __construct(private string $name, private HtmlNode $node)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function replaceDom(): void
    {
        /**
         * @var HtmlNode $child
         */
        foreach ($this->node->parent->find('*') as $index => $child) {
            if (! $child->hasAttribute('twill-repeater')) {
                $child->delete();
            }
        }
        /** @var HtmlNode $inner */
        $inner = $this->node->parent->find('*[twill-repeater]')[0];

        $inner->removeAttribute('twill-repeater');

        foreach ($this->fields as $field) {
            $field->replaceDom();
        }

        $firstChildId = $this->node->parent->firstChild()->id();

        $this->node->parent->addChild(
            new TextNode("\n@foreach(\$repeater('{$this->name}') as \$items)\n"),
            $firstChildId
        );
        $this->node->parent->addChild(new TextNode("\n@endforeach\n"));
    }

    /**
     * @return Collection<BlockRepeaterField>
     */
    public function getBlockFields(): Collection
    {
        if (! $this->fields) {

            $this->fields = collect();

            foreach (SupportedFieldType::cases() as $type) {
                /** @var HtmlNode $repeaterNode */
                foreach ($this->node->find('*[twill-' . $this->name . '-' . $type->value . ']') as $fieldNode) {
                    $this->fields->push(new BlockRepeaterField($type, $fieldNode, $this->name));
                }
            }
        }

        return $this->fields;
    }

    public function getStringForBuilder(PhpNamespace $namespace): string
    {
        $namespace->addUse(InlineRepeater::class);

        $formCalls = collect();
        foreach ($this->getBlockFields() as $field) {
            $data = $field->getType()->getStringForBuilder($field->getFieldName());
            $namespace->addUse($data['class']);
            $formCalls->push($data['markup']);
        }

        $inner = $formCalls->join(',' . PHP_EOL . '    ');

        return <<<PHP
InlineRepeater::make()
    ->name('$this->name')
    ->fields([
        $inner 
    ])
PHP;

    }
}
