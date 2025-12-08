<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Php\Pie\File\Sudo;
use Php\Pie\Util\Process;
use Symfony\Component\Process\ExecutableFinder;

use function array_unshift;

/** @internal This is not public API for PIE, so should not be depended upon unless you accept the risk of BC breaks */
enum PackageManager: string
{
    case Apt = 'apt-get';
    case Apk = 'apk';
    // @todo dnf
    // @todo yum
    // @todo brew

    public static function detect(): self|null
    {
        $executableFinder = new ExecutableFinder();

        foreach (self::cases() as $packageManager) {
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
            self::Apt => ['apt-get', 'install', '-y', '--no-install-recommends', '--no-install-suggests', ...$packages],
            self::Apk => ['apk', 'add', '--no-cache', ...$packages],
        };
    }

    /** @param list<string> $packages */
    public function install(array $packages): void
    {
        $cmd = self::installCommand($packages);

        // @todo ideally only add sudo if it's needed
        if (Sudo::exists()) {
            array_unshift($cmd, Sudo::find());
        }

        Process::run($cmd);
    }
}
