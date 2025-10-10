<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Mechanisms\ResolveComponents\Management;

use Magento\Framework\View\LayoutInterface;
use Magewirephp\Magento\View\LayoutBuilder;

class LayoutManager
{
    public function __construct(
        private LayoutInterface $layout,
        protected readonly LayoutBuilder $builder
    ) {
        //
    }

    /**
     * Set or get the global layout instance to use when trying to retrieve blocks.
     */
    public function layout(LayoutInterface|null $layout = null): LayoutInterface
    {
        if ($layout) {
            $this->layout = $layout;
        }

        return $this->layout;
    }

    /**
     * Returns the Magewire layout builder.
     */
    public function builder(): LayoutBuilder
    {
        return $this->builder;
    }
}
