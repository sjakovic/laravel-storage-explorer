<?php

namespace Jakovic\StorageExplorer\Tests;

use Jakovic\StorageExplorer\StorageExplorerServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            StorageExplorerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => $this->getTempDirectory(),
        ]);

        $app['config']->set('storage-explorer.disk', 'local');
        $app['config']->set('storage-explorer.root_path', '');
    }

    protected function getTempDirectory(): string
    {
        return sys_get_temp_dir().'/storage-explorer-tests';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $dir = $this->getTempDirectory();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $this->cleanDirectory($this->getTempDirectory());
        parent::tearDown();
    }

    protected function cleanDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($dir);
    }
}
