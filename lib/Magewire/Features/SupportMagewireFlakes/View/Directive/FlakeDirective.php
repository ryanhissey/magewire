<?php
/**
 * Copyright © Willem Poortman 2021-present. All rights reserved.
 *
 * Please read the README and LICENSE files for more
 * details on copyrights and license information.
 */

declare(strict_types=1);

namespace Magewirephp\Magewire\Features\SupportMagewireFlakes\View\Directive;

use Magewirephp\Magewire\Features\SupportMagewireCompiling\View\Directive;
use Magewirephp\Magewire\Features\SupportMagewireCompiling\View\Directive\Parser\ExpressionParserType;
use Magewirephp\Magewire\Features\SupportMagewireCompiling\View\ScopeDirectiveParser;

class FlakeDirective extends Directive
{
    #[ScopeDirectiveParser(expressionParserType: ExpressionParserType::FUNCTION_ARGUMENTS)]
    public function flake(array $arguments): string
    {
        $arguments = json_encode($arguments);

        return <<<MAGEWIRE_DIRECTIVE
<?php
\$variables = get_defined_vars();
\$arguments = json_decode('$arguments', true);

\$html = \$__magewire->action('magewire.flake')->execute(
    'create',
    flake: \$arguments['flake'],
    data: \$arguments['data'] ?? [],
    metadata: \$arguments['metadata'] ?? [],
    variables: \$arguments
);

echo \$__magewire->utils()
    ->fragment()
    ->make()
    ->custom(\Magewirephp\Magewire\Features\SupportMagewireFlakes\View\Fragment\FlakeFragment::class)
    ->withTags(['magewire', 'flake'])
    ->wrap(\$html);

unset(\$variables, \$arguments, \$fragment, \$html);
?>
MAGEWIRE_DIRECTIVE;
    }
}
