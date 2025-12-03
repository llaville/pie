<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Composer\IO\IOInterface;

use function implode;

final class CheckAllBuildTools
{
    public static function buildToolsFactory(): self
    {
        return new self([
            new CheckIndividualBuildToolInPath(
                'gcc',
                [
                    PackageManager::Apt->value => 'gcc',
                    PackageManager::Apk->value => 'build-base',
                ],
            ),
            new CheckIndividualBuildToolInPath(
                'make',
                [
                    PackageManager::Apt->value => 'make',
                    PackageManager::Apk->value => 'make',
                ],
            ),
        ]);
    }

    /** @param list<CheckIndividualBuildToolInPath> $buildTools */
    public function __construct(
        private readonly array $buildTools,
    ) {
    }

    public function check(IOInterface $io, bool $forceInstall): void
    {
        $io->write('<info>Checking if all build tools are installed.</info>', verbosity: IOInterface::VERBOSE);
        $packageManager    = PackageManager::detect();
        $missingTools      = [];
        $packagesToSuggest = [];
        $allFound          = true;
        foreach ($this->buildTools as $buildTool) {
            if ($buildTool->check() !== false) {
                $io->write('Build tool ' . $buildTool->tool . ' is installed.', verbosity: IOInterface::VERY_VERBOSE);
                continue;
            }

            $allFound            = false;
            $missingTools[]      = $buildTool->tool;
            $packagesToSuggest[] = $buildTool->packageNameFor($packageManager);
        }

        if ($allFound) {
            $io->write('<info>All build tools found.</info>', verbosity: IOInterface::VERBOSE);

            return;
        }

        $io->write('<comment>The following build tools are missing: ' . implode(', ', $missingTools) . '</comment>');

        if (! $io->isInteractive() && ! $forceInstall) {
            $io->writeError('<error>You are not running in interactive mode, and --force was not specified. You may need to install the following build tools: ' . implode(' ', $packagesToSuggest) . '</error>');

            return;
        }

        $io->write('The following command will be run: ' . implode(' ', $packageManager->installCommand($packagesToSuggest)), verbosity: IOInterface::VERBOSE);

        if ($io->isInteractive() && ! $forceInstall) {
            if (! $io->askConfirmation('<question>Would you like to install them now?</question>', false)) {
                $io->write('<comment>Ok, but things might not work. Just so you know.</comment>');

                return;
            }
        }

        $packageManager->install($packagesToSuggest);
        $io->write('<info>Build tools installed.</info>');
    }
}
