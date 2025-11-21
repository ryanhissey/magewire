<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireFlakes\Component;

use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Features\SupportMagewireFlakes\Mechanisms\ResolveComponent\ComponentResolver\FlakeResolver;

class Flake extends Component
{
    public function __construct(
        private FlakeResolver $resolver
    ) {
        //
    }

    public function click()
    {
        $this->dispatchSuccessMessage('Succes from the default Flake! ' . $this->id());
    }
}
