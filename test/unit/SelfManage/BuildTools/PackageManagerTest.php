<?php

declare(strict_types=1);

namespace Php\PieUnitTest\SelfManage\BuildTools;

use Php\Pie\SelfManage\BuildTools\PackageManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PackageManager::class)]
final class PackageManagerTest extends TestCase
{
    public function testInstallCommand(): void
    {
        self::assertSame(
            ['echo', '"fake installing a, b"'],
            PackageManager::Test->installCommand(['a', 'b']),
        );
        self::assertSame(
            ['apt-get', 'install', '-y', '--no-install-recommends', '--no-install-suggests', 'a', 'b'],
            PackageManager::Apt->installCommand(['a', 'b']),
        );
        self::assertSame(
            ['apk', 'add', '--no-cache', 'a', 'b'],
            PackageManager::Apk->installCommand(['a', 'b']),
        );
    }
}
