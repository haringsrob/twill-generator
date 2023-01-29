<?php

namespace Haringsrob\TwillGenerator\Commands;

use Haringsrob\TwillGenerator\Services\Parser;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateBlocksForFileCommand extends Command
{
    protected $signature = 'twill-generator:generate-blocks-for-file {file} {--force}';

    protected $description = 'Generate blocks for the given html file';

    public function handle(Filesystem $files): void
    {
        $force = $this->option('force', false);
        $parser = new Parser(file_get_contents($this->argument('file')));

        $blocks = $parser->getBlockSegments();

        $this->line('The following block components will be created:');

        $rows = [];
        foreach ($blocks as $block) {
            $rows[] = [$block->getBlockName(), '', '', '', $block->getResourcePath(), str_replace(
                base_path(),
                '',
                $block->getClassPath()
            )];

            foreach ($block->getBlockFields() as $field) {
                $rows[] = ['', $field->getLabel(), $field->getFieldName(), $field->getType()->value];
            }

            foreach ($block->getBlockRepeaters() as $repeater) {
                $rows[] = ['', 'REPEATER', $repeater->getName()];


                foreach ($repeater->getBlockFields() as $field) {
                    $rows[] = ['', $field->getLabel(), $repeater->getName() . '.' . $field->getFieldName(), $field->getType()->value];
                }
            }
        }

        $this->table(['block name', 'label', 'field name', 'field type', 'dir', 'class'], $rows);


        if ($this->confirm('Is this ok?')) {
            foreach ($blocks as $block) {
                $viewDir = Str::beforeLast(resource_path($block->getResourcePath()), DIRECTORY_SEPARATOR);
                if (! $files->isDirectory($viewDir)) {
                    $files->makeDirectory($viewDir, recursive: true);
                }

                if (
                    $force ||
                    !$files->exists(resource_path($block->getResourcePath())) ||
                    (
                        $files->exists(resource_path($block->getResourcePath())) &&
                        $this->confirm($block->getResourcePath() . ' exists, do  you want to overwrite?')
                    )
                ) {
                    $files->put(resource_path($block->getResourcePath()), $block->getBlockBladeContent());
                }

                $classDir = Str::beforeLast($block->getClassPath(), DIRECTORY_SEPARATOR);
                if (! $files->isDirectory($classDir)) {
                    $files->makeDirectory($classDir, recursive: true);
                }

                if (
                    $force ||
                    !$files->exists($block->getClassPath()) ||
                    (
                        $files->exists($block->getClassPath()) &&
                        $this->confirm(
                            str_replace(base_path(), '', $block->getClassPath()) . ' exists, do  you want to overwrite?'
                        )
                    )
                ) {
                    $files->put($block->getClassPath(), $block->getClass());
                }

            }
        }
    }
}
