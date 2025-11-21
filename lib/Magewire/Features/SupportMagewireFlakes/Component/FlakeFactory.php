<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireFlakes\Component;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Mechanisms\ResolveComponents\Management\LayoutManager;

class FlakeFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly LayoutManager $layoutManager,
        private readonly string $type = Flake::class
    ) {

    }

    public function create(array $arguments = []): Component
    {
        return $this->objectManager->create($this->type, $arguments);
    }

    public function createByName(string $name, array $data = []): AbstractBlock|false
    {
        $layout = $this->layoutManager->decorator()->decorateForPagelessBlockFetching(
            $this->layoutManager->factory()->create()
        );

        $layout->getUpdate()->addHandle('magewire_flakes');
        $block = $layout->getBlock($name);

        if ($block instanceof AbstractBlock) {
            $data['magewire'] ??= $this->create();
            $data['magewire:resolver'] ??= 'test';

            $block->addData($data);
        }

        return $block;
    }
}
