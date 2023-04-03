<?php declare(strict_types=1);
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

namespace Magewirephp\Magewire\Model\Component;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Exception\MissingComponentException;
use Magewirephp\Magewire\Model\RequestInterface;

interface ResolverInterface
{
    /**
     * Checks for very specific data elements to see if
     * this component complies the requirements.
     *
     * It's recommended to keep these checks a light as
     * possible e.g. without any database interactions.
     */
    public function complies(BlockInterface $block): bool;

    /**
     * Build component based on type.
     */
    public function construct(Template $block): Component;

    /**
     * Re-build component based on subsequent request data.
     *
     * @throws MissingComponentException
     */
    public function reconstruct(RequestInterface $request): Component;

    /**
     * Returns the unique (publicly visible) name of the resolver.
     */
    public function getPublicName(): string;
}