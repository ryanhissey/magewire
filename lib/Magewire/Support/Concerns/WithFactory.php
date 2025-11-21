<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Support\Concerns;

use InvalidArgumentException;
use Magento\Framework\App\ObjectManager;

trait WithFactory
{
    /**
     * Returns a new instance of the current object.
     */
    public function newInstance(array $arguments = [], string|null $type = null): static
    {
        if ($type && ! class_exists($type)) {
            throw new InvalidArgumentException(sprintf('Class %s does not exist', $type));
        }

        return ObjectManager::getInstance()->create($type ?? static::class, $arguments);
    }
}
