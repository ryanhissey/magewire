<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireCompiling\View;

class DirectiveHandover
{
    private array $handovers = [];

    public function __construct(
        private readonly DirectiveHandoverDataFactory $directiveHandoverDataFactory
    ) {
        //
    }

    public function bind(string $key, mixed $data, string|null $id = null): DirectiveHandoverData
    {
        /** @var DirectiveHandoverData $handover */
        $handover = $this->directiveHandoverDataFactory->create(['id' => $id]);
        $handover->set($key, $data);

        $id = $handover->id();
        $this->handovers[$id] = $handover;

        return $handover;
    }

    public function retrieve(string $id): DirectiveHandoverData
    {
        if (isset($this->handovers[$id])) {
            return $this->handovers[$id];
        }

        // THROW SOMETHING HERE.
    }
}
