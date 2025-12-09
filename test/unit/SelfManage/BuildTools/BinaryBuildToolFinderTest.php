<?php

declare(strict_types=1);

namespace Php\PieUnitTest\SelfManage\BuildTools;

use Php\Pie\Platform\TargetPhp\PhpBinaryPath;
use Php\Pie\Platform\TargetPlatform;
use Php\Pie\SelfManage\BuildTools\BinaryBuildToolFinder;
use Php\Pie\SelfManage\BuildTools\PackageManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BinaryBuildToolFinder::class)]
final class BinaryBuildToolFinderTest extends TestCase
{
    public function testCheckFailsToFindTool(): void
    {
        self::assertFalse((new BinaryBuildToolFinder('this-should-not-be-anything-in-path', []))->check());
    }

    public function testCheckFindsTool(): void
    {
        self::assertTrue((new BinaryBuildToolFinder('echo', []))->check());
    }

    public function testPackageNameIsNullWhenNoPackageConfiguredForPackageManager(): void
    {
        self::assertNull(
            (new BinaryBuildToolFinder('a', []))
                ->packageNameFor(
                    PackageManager::Test,
                    TargetPlatform::fromPhpBinaryPath(PhpBinaryPath::fromCurrentProcess(), null),
                ),
        );
    }

    public function testPackageNameIsNullWhenPackageConfiguredForPackageManagerIsNull(): void
    {
        self::assertNull(
            (new BinaryBuildToolFinder('a', [PackageManager::Test->value => null]))
                ->packageNameFor(
                    PackageManager::Test,
                    TargetPlatform::fromPhpBinaryPath(PhpBinaryPath::fromCurrentProcess(), null),
                ),
        );
    }

    public function testPackageNameIsReturnedWhenPackageConfiguredForPackageManager(): void
    {
        self::assertSame(
            'the-package',
            (new BinaryBuildToolFinder('a', [PackageManager::Test->value => 'the-package']))
                ->packageNameFor(
                    PackageManager::Test,
                    TargetPlatform::fromPhpBinaryPath(PhpBinaryPath::fromCurrentProcess(), null),
                ),
        );
    }

    public function testPackageNameIsReturnedWithFormattingWhenPackageConfiguredForPackageManager(): void
    {
        $phpBinary = PhpBinaryPath::fromCurrentProcess();

        self::assertSame(
            'php' . $phpBinary->majorVersion() . $phpBinary->minorVersion() . '-dev',
            (new BinaryBuildToolFinder('a', [PackageManager::Test->value => 'php{major}{minor}-dev']))
                ->packageNameFor(
                    PackageManager::Test,
                    TargetPlatform::fromPhpBinaryPath($phpBinary, null),
                ),
        );
    }
}
