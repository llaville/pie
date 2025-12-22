<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Php\Pie\Platform\TargetPlatform;
use Symfony\Component\Process\ExecutableFinder;

use function array_key_exists;
use function str_replace;

/** @internal This is not public API for PIE, so should not be depended upon unless you accept the risk of BC breaks */
class BinaryBuildToolFinder
{
    /** @param array<PackageManager::*, non-empty-string|null> $packageManagerPackages */
    public function __construct(
        public readonly string $tool,
        private readonly array $packageManagerPackages,
    ) {
    }

    public function check(): bool
    {
        return (new ExecutableFinder())->find($this->tool) !== null;
    }

    /** @return non-empty-string|null */
    public function packageNameFor(PackageManager $packageManager, TargetPlatform $targetPlatform): string|null
    {
        if (! array_key_exists($packageManager->value, $this->packageManagerPackages) || $this->packageManagerPackages[$packageManager->value] === null) {
            return null;
        }

        // If we need to customise specific package names depending on OS
        // specific parameters, this is likely the place to do it
        return str_replace(
            '{major}',
            (string) $targetPlatform->phpBinaryPath->majorVersion(),
            str_replace(
                '{minor}',
                (string) $targetPlatform->phpBinaryPath->minorVersion(),
                $this->packageManagerPackages[$packageManager->value],
            ),
        );
    }
}
