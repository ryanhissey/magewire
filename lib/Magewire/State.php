<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire;

use RuntimeException;

class State
{
    private Mode $mode = Mode::UNKNOWN;

    private bool|null $active = null;

    /**
     * @return $this
     */
    public function start(): static
    {
        if ($this->active !== null) {
            throw new RuntimeException('Magewire state already started, this can only happen once.');
        }

        $this->active = true;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active ?? false;
    }

    public function isInActive(): bool
    {
        return $this->isActive() === false;
    }

    public function mode(Mode|null $mode = null): Mode
    {
        if ($mode && $this->mode->isUnknown()) {
            return $this->mode = $mode;
        }

        return $this->mode;
    }
}
