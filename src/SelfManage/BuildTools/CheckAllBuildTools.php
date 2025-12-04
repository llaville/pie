<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Composer\IO\IOInterface;

use function array_unique;
use function array_values;
use function count;
use function implode;

/** @internal This is not public API for PIE, so should not be depended upon unless you accept the risk of BC breaks */
class CheckAllBuildTools
{
    public static function buildToolsFactory(): self
    {
        return new self([
            new BinaryBuildToolFinder(
                'gcc',
                [
                    PackageManager::Apt->value => 'gcc',
                    PackageManager::Apk->value => 'build-base',
                ],
            ),
            new BinaryBuildToolFinder(
                'make',
                [
                    PackageManager::Apt->value => 'make',
                    PackageManager::Apk->value => 'build-base',
                ],
            ),
            new BinaryBuildToolFinder(
                'autoconf',
                [
                    PackageManager::Apt->value => 'autoconf',
                    PackageManager::Apk->value => 'autoconf',
                ],
            ),
            new BinaryBuildToolFinder(
                'bison',
                [
                    PackageManager::Apt->value => 'bison',
                    PackageManager::Apk->value => 'bison',
                ],
            ),
            new BinaryBuildToolFinder(
                're2c',
                [
                    PackageManager::Apt->value => 're2c',
                    PackageManager::Apk->value => 're2c',
                ],
            ),
            new BinaryBuildToolFinder(
                'pkg-config',
                [
                    PackageManager::Apt->value => 'pkg-config',
                    PackageManager::Apk->value => 'pkgconfig',
                ],
            ),
            new BinaryBuildToolFinder(
                'libtoolize',
                [
                    PackageManager::Apt->value => 'libtool',
                    PackageManager::Apk->value => 'libtool',
                ],
            ),
        ]);
    }

    /** @param list<BinaryBuildToolFinder> $buildTools */
    public function __construct(
        private readonly array $buildTools,
    ) {
    }

    public function check(IOInterface $io, bool $forceInstall): void
    {
        $io->write('<info>Checking if all build tools are installed.</info>', verbosity: IOInterface::VERBOSE);
        /** @var list<string> $packagesToInstall */
        $packagesToInstall = [];
        $missingTools      = [];
        $packageManager    = PackageManager::detect();
        $allFound          = true;

        foreach ($this->buildTools as $buildTool) {
            if ($buildTool->check() !== false) {
                $io->write('Build tool ' . $buildTool->tool . ' is installed.', verbosity: IOInterface::VERY_VERBOSE);
                continue;
            }

            $allFound       = false;
            $missingTools[] = $buildTool->tool;

            if ($packageManager === null) {
                continue;
            }

            $packageName = $buildTool->packageNameFor($packageManager);

            if ($packageName === null) {
                $io->writeError('<warning>Could not find package name for build tool ' . $buildTool->tool . '.</warning>', verbosity: IOInterface::VERBOSE);
                continue;
            }

            $packagesToInstall[] = $packageName;
        }

        if ($allFound) {
            $io->write('<info>All build tools found.</info>', verbosity: IOInterface::VERBOSE);

            return;
        }

        $io->write('<comment>The following build tools are missing: ' . implode(', ', $missingTools) . '</comment>');

        if ($packageManager === null) {
            $io->write('<warning>Could not find a package manager to install the missing build tools.</warning>');

            return;
        }

        if (! count($packagesToInstall)) {
            $io->write('<warning>Could not determine packages to install.</warning>');

            return;
        }

        $proposedInstallCommand = implode(' ', $packageManager->installCommand(array_values(array_unique($packagesToInstall))));

        if (! $io->isInteractive() && ! $forceInstall) {
            $io->writeError('<warning>You are not running in interactive mode. You may need to run: ' . $proposedInstallCommand . '</warning>');

            return;
        }

        $io->write('The following command will be run: ' . $proposedInstallCommand, verbosity: IOInterface::VERY_VERBOSE);

        if ($io->isInteractive() && ! $forceInstall) {
            if (! $io->askConfirmation('<question>Would you like to install them now?</question>', false)) {
                $io->write('<comment>Ok, but things might not work. Just so you know.</comment>');

                return;
            }
        }

        $packageManager->install(array_values(array_unique($packagesToInstall)));
        $io->write('<info>Missing build tools have been installed.</info>');
    }
}
