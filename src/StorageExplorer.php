<?php

namespace Jakovic\StorageExplorer;

use Illuminate\Support\Facades\Route;
use Jakovic\StorageExplorer\Http\Controllers\FileController;
use Jakovic\StorageExplorer\Http\Controllers\StorageExplorerController;

class StorageExplorer
{
    public static function routes(?string $prefix = null, ?array $middleware = null): void
    {
        $prefix = $prefix ?? config('storage-explorer.standalone.prefix', 'storage-explorer');

        $groupConfig = ['prefix' => $prefix];

        if ($middleware !== null) {
            $groupConfig['middleware'] = $middleware;
        }

        Route::group($groupConfig, function () {
            Route::get('/', [StorageExplorerController::class, 'index'])->name('storage-explorer.index');
            Route::get('/api/tree', [StorageExplorerController::class, 'tree'])->name('storage-explorer.tree');
            Route::get('/api/preview', [StorageExplorerController::class, 'preview'])->name('storage-explorer.preview');
            Route::get('/api/search', [StorageExplorerController::class, 'search'])->name('storage-explorer.search');
            Route::get('/download', [FileController::class, 'download'])->name('storage-explorer.download');
            Route::get('/api/download-url', [FileController::class, 'downloadUrl'])->name('storage-explorer.download-url');
            Route::post('/upload', [FileController::class, 'upload'])->name('storage-explorer.upload');
            Route::post('/directory', [FileController::class, 'createDirectory'])->name('storage-explorer.create-directory');
            Route::delete('/file', [FileController::class, 'delete'])->name('storage-explorer.delete');
        });
    }
}