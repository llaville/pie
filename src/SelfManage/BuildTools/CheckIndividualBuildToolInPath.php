<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Symfony\Component\Process\ExecutableFinder;

class CheckIndividualBuildToolInPath
{
    public function __construct(
        public readonly string $tool,
        private readonly array $packageManagerPackages,
    ) {
    }

    public function check(): bool
    {
        return (new ExecutableFinder())->find($this->tool) !== null;
    }

    public function packageNameFor(PackageManager $packageManager): string
    {
        // @todo could we do a check the package exists?

        // If we need to customise specific package names depending on OS
        // specific parameters, this is likely the place to do it
        return $this->packageManagerPackages[$packageManager->value];
    }
}
