<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireCompiling;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\ComponentHook;
use Magewirephp\Magewire\Features\SupportMagewireCompiling\View\Compiler\MagewireCompilerFactory;
use function Magewirephp\Magewire\on;

class SupportMagewireCompiling extends ComponentHook
{
    public function __construct(
        private readonly MagewireCompilerFactory $compilerFactory,
        private readonly MagewireUnderscoreViewModelFactory $underscoreViewModelFactory
    ) {
        //
    }

    public function provide(): void
    {
        on('magewire:view:precompile', function (AbstractBlock $block, string $filename, array $dictionary, Component $magewire) {
            $compiler = $magewire->compiler() ?? $magewire->compiler($this->compilerFactory->create());

            return function (array $result) use ($magewire, $compiler, $block) {
                // Although named "filename", this actually represents the full file path,
                // including the filename and its extension.
                $path = $result['filename'];

                if ($magewire->compiler()->canCompile()) {
                    // Compiles the final HTML, puts it into a resource, and returns its new file path.
                    $result['filename'] = $compiler->requiresCompilation($path) ? $compiler->compile($path) : $compiler->generateFilePath($path);
                }

                // Include the Magewire underscore object optionally required by compiled views.
                $result['dictionary']['__magewire'] = $this->underscoreViewModelFactory->create();

                return $result;
            };
        });
    }

    /**
     * WIP
     *
     * Generate a readable file structure for generated files.
     *
     * Instead of unreadable filenames, create organized directories that map to source files,
     * making it easier for developers to locate and debug generated output.
     *
     * TODO: Consider enabling this only in development mode.
     */
    private function transformToViewPath(Template $block): string
    {
        $template = $block->getTemplate();
        $parts = explode('::', $template);

        if (count($parts) === 2) {
            $parts[0] = str_replace('_', '/', $parts[0]);

            return implode('/', $parts);
        }

        return $block->getTemplateFile();
    }
}
