<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Mechanisms\ResolveComponents\ComponentResolver;

use Magento\Framework\ObjectManagerInterface;

class ComponentResolverFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    ) {

    }

    public function create(string $type, array $arguments = [])
    {
        return $this->objectManager->create($type, $arguments);
    }
}
