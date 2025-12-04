<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\BuildTools;

use Php\Pie\File\Sudo;
use Php\Pie\Util\Process;
use Symfony\Component\Process\ExecutableFinder;

use function array_unshift;

enum PackageManager: string
{
    case Apt = 'apt-get';
    case Apk = 'apk';
    // @todo dnf
    // @todo yum

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
        $cmd = match ($this) {
            self::Apt => ['apt-get', 'install', '-y', '--no-install-recommends', '--no-install-suggests', ...$packages],
            self::Apk => ['apk', 'add', '--no-cache', ...$packages],
        };

        if (Sudo::exists()) {
            array_unshift($cmd, Sudo::find());
        }

        return $cmd;
    }

    /** @param list<string> $packages */
    public function install(array $packages): void
    {
        Process::run(self::installCommand($packages));
    }
}
