<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Php\Pie\Platform\TargetPhp\PhpizePath;
use Symfony\Component\Process\ExecutableFinder;

/** @internal This is not public API for PIE, so should not be depended upon unless you accept the risk of BC breaks */
class PhpizeBuildToolFinder extends BinaryBuildToolFinder
{
    public function check(): bool
    {
        $foundTool = (new ExecutableFinder())->find($this->tool);

        return $foundTool !== null && PhpizePath::looksLikeValidPhpize($foundTool);
    }
}
