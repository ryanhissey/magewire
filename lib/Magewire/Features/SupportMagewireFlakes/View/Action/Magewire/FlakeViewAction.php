<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireFlakes\View\Action\Magewire;

use Magento\Framework\Exception\LocalizedException;
use Magewirephp\Magewire\Features\SupportMagewireCompiling\View\ViewAction as ViewAction;
use Magewirephp\Magewire\Features\SupportMagewireFlakes\Component\FlakeFactory;
use Magewirephp\Magewire\Features\SupportMagewireFlakes\Mechanisms\ResolveComponent\ComponentResolver\FlakeResolver;
use Magewirephp\Magewire\Support\DataArray;
use Magewirephp\Magewire\Support\DataArrayFactory;

class FlakeViewAction extends ViewAction
{
    public function __construct(
        private readonly FlakeFactory $flakeFactory,
        private readonly DataArrayFactory $attributesFactory
    ) {
        //
    }

    public function create(
        string $flake,
        array $data = [],
        array $metadata = [],
        array $variables = []
    ): string {
        $data = $this->attributesFactory->create()->fill($data);

        $data->each(function (DataArray $array, $value, $key) use ($variables) {
                if (str_starts_with($value, '$')) {
                    $value = trim($value, '$');

                    if (array_key_exists($value, $variables)) {
                        $array->put($key, $variables[$value]);
                    }
                }
            });

        $data  = $data->all();
        $block = $this->flakeFactory->createByName($flake, $data);

        if (! $block) {
            return '';
        }

        $block->setData('magewire:alias', $flake);

        $block->setNameInLayout($data['magewire:name']);
        return $block->toHtml();
    }
}
