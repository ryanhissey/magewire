<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireCompiling\View;

use Magewirephp\Magewire\Support\DataArray;

class DirectiveHandoverData extends DataArray
{
    public function __construct(
        private string|null $id = null
    ) {
        $this->id ??= uniqid();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function print(): string
    {
        return base64_encode(serialize($this->all()));
    }
}
