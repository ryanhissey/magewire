<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire;

enum Mode: int
{
    case UNKNOWN = 0;
    case PRECEDING = 1;
    case SUBSEQUENT = 2;

    public function isUnknown(): bool
    {
        return $this === Mode::UNKNOWN;
    }

    public function isSubsequent(): bool
    {
        return $this === Mode::SUBSEQUENT;
    }

    public function isPreceding(): bool
    {
        return $this === Mode::PRECEDING;
    }
}
