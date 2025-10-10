<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magento\View;

use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Layout\BuilderInterface;
use Magento\Framework\View\LayoutFactory as MagentoLayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magewirephp\Magento\View\Layout\GeneratorPool;
use Magewirephp\Magewire\Support\Concerns\WithFactory;

class LayoutBuilder implements BuilderInterface
{
    use WithFactory;

    private array $builds = [];
    private array $handles = [];

    public function __construct(
        private readonly GeneratorPool $generatorPool,
        private readonly MagentoLayoutFactory $magentoLayoutFactory,
        private readonly LayoutInterface $layout
    ) {
        //
    }

    public function withHandles(array $handles, bool $merge = true): static
    {
        $handles = array_values($handles);
        $this->handles = $merge ? array_merge($this->handles, $handles) : $handles;

        return $this;
    }

    public function withHandle(string $handle): static
    {
        return $this->withHandles([$handle]);
    }

    public function removeHandles(array|null $handles = null): static
    {
        if ($handles) {
            foreach ($handles as $handle) {
                $this->removeHandle($handle);
            }

            return $this;
        }

        $this->handles = [];

        return $this;
    }

    public function removeHandle(string $handle): static
    {
        unset($this->handles[$handle]);

        return $this;
    }

    /**
     * @return LayoutInterface
     * @throws LocalizedException
     */
    final public function build(bool $force = false): LayoutInterface
    {
        if (empty($this->handles)) {
            throw new InvalidArgumentException('Handles array cannot be empty');
        }

        // Copy the handles for sorting without removing the current ordering.
        $handles = $this->handles;

        sort($handles);
        $hash = hash('xxh3', json_encode(($handles)));

        // Early return when a version with the identical handles already exists.
        if (! $force && in_array($hash, $this->builds, true)) {
            return $this->builds[$hash];
        }

        $layout = $this->magentoLayoutFactory->create([
            // Custom generator pool to have a virtual root element to bind elements upon.
            'generatorPool' => $this->generatorPool
        ]);

        $layout->getUpdate()->load($this->handles);

        $layout->generateXml();
        $layout->generateElements();

        // Store build for option reuse and return the final layout.
        return $this->builds[$hash] = $layout;
    }

    public function reset(): static
    {
        $this->builds = [];
        $this->handles = [];

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    final public function rebuild(): LayoutInterface
    {
        return $this->build(true);
    }
}
