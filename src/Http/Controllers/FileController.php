<?php

namespace Jakovic\StorageExplorer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Jakovic\StorageExplorer\Services\StorageService;

class FileController extends Controller
{
    public function __construct(
        protected StorageService $storageService,
    ) {}

    public function download(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired download link.');
        }

        $path = $request->query('path', '');

        try {
            $fullPath = $this->storageService->getFullPath($path);
            $disk = $this->storageService->getDisk();

            if (! $disk->exists($fullPath)) {
                abort(404, 'File not found.');
            }

            $name = basename($path);

            return $disk->download($fullPath, $name);
        } catch (\InvalidArgumentException $e) {
            abort(403, $e->getMessage());
        }
    }

    public function downloadUrl(Request $request): JsonResponse
    {
        $path = $request->query('path', '');

        if ($path === '') {
            return response()->json([
                'success' => false,
                'message' => 'Path is required.',
            ], 400);
        }

        try {
            $this->storageService->validatePath($path);

            $url = URL::temporarySignedRoute(
                'storage-explorer.download',
                now()->addMinutes(5),
                ['path' => $path],
            );

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function upload(Request $request): JsonResponse
    {
        if (! config('storage-explorer.upload.enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload is disabled.',
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|max:'.config('storage-explorer.upload.max_size', 10240),
            'directory' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $directory = $request->input('directory') ?? '';
        $blockedExtensions = config('storage-explorer.upload.blocked_extensions', []);

        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, $blockedExtensions, true)) {
            return response()->json([
                'success' => false,
                'message' => "File type .{$extension} is not allowed.",
            ], 422);
        }

        try {
            $this->storageService->validatePath($directory);

            $disk = $this->storageService->getDisk();
            $targetDir = $this->storageService->getFullPath($directory);
            $fileName = $file->getClientOriginalName();

            $disk->putFileAs($targetDir, $file, $fileName);

            return response()->json([
                'success' => true,
                'message' => "File '{$fileName}' uploaded successfully.",
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function createDirectory(Request $request): JsonResponse
    {
        if (! config('storage-explorer.upload.enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload is disabled.',
            ], 403);
        }

        $path = $request->input('path') ?? '';

        if ($path === '') {
            return response()->json([
                'success' => false,
                'message' => 'Path is required.',
            ], 400);
        }

        try {
            $this->storageService->createDirectory($path);

            return response()->json([
                'success' => true,
                'message' => 'Folder created successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        if (! config('storage-explorer.delete.enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion is disabled.',
            ], 403);
        }

        $path = $request->input('path') ?? '';
        $isDirectory = $request->boolean('is_directory', false);

        if ($path === '') {
            return response()->json([
                'success' => false,
                'message' => 'Path is required.',
            ], 400);
        }

        try {
            if ($isDirectory) {
                $this->storageService->deleteDirectory($path);
            } else {
                $this->storageService->deleteFile($path);
            }

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
