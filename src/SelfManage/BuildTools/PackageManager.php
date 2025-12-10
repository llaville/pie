<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Php\Pie\File\Sudo;
use Php\Pie\Platform;
use Php\Pie\Util\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;

use function array_unshift;
use function implode;
use function str_contains;
use function strtolower;

/** @internal This is not public API for PIE, so should not be depended upon unless you accept the risk of BC breaks */
enum PackageManager: string
{
    case Test = 'test';
    case Apt  = 'apt-get';
    case Apk  = 'apk';
    case Dnf  = 'dnf';
    case Yum  = 'yum';
    // @todo enable: case Brew = 'brew';

    public static function detect(): self|null
    {
        $executableFinder = new ExecutableFinder();

        foreach (self::cases() as $packageManager) {
            if ($packageManager === self::Test) {
                continue;
            }

            if ($executableFinder->find($packageManager->value) !== null) {
                return $packageManager;
            }
        }

        return null;
    }

    /**
     * @param list<string> $packages
     *
     * @return list<string>
     */
    public function installCommand(array $packages): array
    {
        return match ($this) {
            self::Test => ['echo', '"fake installing ' . implode(', ', $packages) . '"'],
            self::Apt => ['apt-get', 'install', '-y', '--no-install-recommends', '--no-install-suggests', ...$packages],
            self::Apk => ['apk', 'add', '--no-cache', ...$packages],
            self::Dnf => ['dnf', 'install', '-y', ...$packages],
            self::Yum => ['yum', 'install', '-y', ...$packages],
        };
    }

    /** @param list<string> $packages */
    public function install(array $packages): void
    {
        $cmd = self::installCommand($packages);

        try {
            Process::run($cmd);

            return;
        } catch (ProcessFailedException $e) {
            if (Platform::isInteractive() && self::isProbablyPermissionDenied($e)) {
                array_unshift($cmd, Sudo::find());

                Process::run($cmd);

                return;
            }

            throw $e;
        }
    }

    private static function isProbablyPermissionDenied(ProcessFailedException $e): bool
    {
        $mergedProcessOutput = strtolower($e->getProcess()->getErrorOutput() . $e->getProcess()->getOutput());

        $needles = [
            'permission denied',
            'you must be root',
            'operation not permitted',
            'are you root',
        ];

        foreach ($needles as $needle) {
            if (str_contains($mergedProcessOutput, $needle)) {
                return true;
            }
        }

        return false;
    }
}
