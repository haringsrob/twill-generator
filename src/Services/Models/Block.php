<?php

namespace Haringsrob\TwillGenerator\Services\Models;

use A17\Twill\Services\Forms\Form;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Haringsrob\TwillGenerator\Enums\SupportedFieldType;
use Haringsrob\TwillGenerator\Facades\TwillGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use PHPHtmlParser\Dom\Node\HtmlNode;

class Block
{
    /**
     * @var Collection<BlockField>|null
     */
    private ?Collection $fields = null;

    /**
     * @var Collection<BlockRepeater>|null
     */
    private ?Collection $repeaters = null;

    public function __construct(private string $name, private HtmlNode $node)
    {
        $this->node->removeAttribute('twill-block');
    }

    public function getBlockName(): string
    {
        return $this->name;
    }

    public function getViewName(): string
    {
        return 'components.twill.blocks.' . Str::snake($this->getBlockName());
    }

    public function getResourcePath(): string
    {
        return 'views' . DIRECTORY_SEPARATOR . Str::replace('.',
                DIRECTORY_SEPARATOR,
                $this->getViewName()) . '.blade.php';
    }

    public function getClassName(): string
    {
        return Str::studly($this->name);
    }

    public function getClassPath(): string
    {
        $name = 'View\\Components\\Twill\\Blocks\\' . $this->getClassName();

        return app_path(str_replace('\\', '/', $name) . '.php');
    }

    /**
     * @return Collection<BlockRepeater>
     */
    public function getBlockRepeaters(): Collection
    {
        if (! $this->repeaters) {

            $this->repeaters = collect();

            /** @var HtmlNode $repeaterNode */
            foreach ($this->node->find('*[twill-repeater]') as $repeaterNode) {
                $this->repeaters->push(new BlockRepeater(
                    $repeaterNode->getAttribute('twill-repeater'),
                    $repeaterNode
                ));
            }
        }

        return $this->repeaters;
    }

    public function getAllCropNames(): array
    {
        $names = [];

        foreach ($this->getBlockFields() as $field) {
            if ($field->getType() === SupportedFieldType::IMAGE) {
                $names[$field->getFieldName()] = $field->getImageRatio();
            }
        }

        foreach ($this->getBlockRepeaters() as $repeater) {
            foreach ($repeater->getBlockFields() as $field) {
                if ($field->getType() === SupportedFieldType::IMAGE) {
                    $names[$field->getFieldName()] = $field->getImageRatio();
                }
            }
        }

        return $names;
    }

    /**
     * @return Collection<BlockField>
     */
    public function getBlockFields(): Collection
    {
        if (! $this->fields) {

            $this->fields = collect();

            foreach (SupportedFieldType::cases() as $type) {
                /** @var HtmlNode $repeaterNode */
                foreach ($this->node->find('*[twill-' . $type->value . ']') as $fieldNode) {
                    $this->fields->push(new BlockField($type, $fieldNode));
                }
            }
        }

        return $this->fields;
    }

    public function replaceBlockFields(): void
    {
        $this->getBlockFields()->each(function (BlockField $blockField) {
            $blockField->replaceDom();
        });

        $this->getBlockRepeaters()->each(function (BlockRepeater $blockRepeater) {
            $blockRepeater->replaceDom();
        });
    }

    public function getBlockBladeContent(): string
    {
        $this->replaceBlockFields();
        return TwillGenerator::tidyHtml((string)$this->node);
    }

    public function getClass(): string
    {
        $namespace = new PhpNamespace('App\\View\\Components\\Twill\\Blocks');

        $class = new ClassType($this->getClassName());

        $namespace->add($class);

        $namespace->addUse(TwillBlockComponent::class);
        $namespace->addUse(Form::class);
        $namespace->addUse(View::class);

        $class->setExtends(TwillBlockComponent::class);

        // Render.
        $class->addMethod('render')
            ->setReturnType(View::class)
            ->setBody('return view(\'' . $this->getViewName() . '\');');

        // Form.
        $getForm = $class->addMethod('getForm')
            ->setReturnType(Form::class);

        $formCalls = collect();
        foreach ($this->getBlockFields() as $field) {
            $data = $field->getType()->getStringForBuilder($field->getFieldName());
            $namespace->addUse($data['class']);
            $formCalls->push($data['markup']);
        }

        foreach ($this->getBlockRepeaters() as $repeater) {
            $formCalls->push($repeater->getStringForBuilder($namespace));
        }

        $inner = $formCalls->join(',' . PHP_EOL . '    ');

        $getForm->setBody(<<<PHP
return Form::make([
    $inner 
]);
PHP
        );

        $replacements = [];

        // Crops.
        if ($this->getAllCropNames() !== []) {
            $cropMethod = $class->addMethod('getCrops')
                ->setStatic()
                ->setReturnType('array');

            $array = [];

            foreach ($this->getAllCropNames() as $cropName => $ratio) {
                $array[$cropName] = [
                    'default' => [
                        [
                            'name' => 'default',
                            'ratio' => new Literal($ratio),
                        ],
                    ]
                ];
            }

            $dumper = new Dumper();
            $encoded = $dumper->format($dumper->dump($array));

            $cropMethod->setBody(<<<PHP
return $encoded;
PHP
            );
        }

        $printer = new PsrPrinter();

        $file = new PhpFile();
        $file->addNamespace($namespace);

        return $printer->printFile($file);
    }
}
