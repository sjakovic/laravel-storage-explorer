<?php

namespace Jakovic\StorageExplorer\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jakovic\StorageExplorer\Services\StorageService;

class StorageExplorerController extends Controller
{
    public function __construct(
        protected StorageService $storageService,
    ) {}

    public function index()
    {
        $apiBase = rtrim(route('storage-explorer.index'), '/');
        $layout = config('storage-explorer.layout');

        $data = [
            'deleteEnabled' => config('storage-explorer.delete.enabled', true),
            'uploadEnabled' => config('storage-explorer.upload.enabled', true),
            'apiBase' => $apiBase,
        ];

        if ($layout) {
            $data['layout'] = $layout;
            $data['contentSection'] = config('storage-explorer.content_section', 'content-page');
            $data['pageTitle'] = config('storage-explorer.page_title', 'Storage Explorer');

            return view('storage-explorer::layouts.embedded', $data);
        }

        return view('storage-explorer::layouts.standalone', $data);
    }

    public function tree(Request $request): JsonResponse
    {
        $path = $request->query('path', '');

        try {
            $contents = $this->storageService->getDirectoryContents($path);

            return response()->json([
                'success' => true,
                'data' => $contents,
                'path' => $path,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        $path = $request->query('path', '');

        if ($path === '') {
            return response()->json([
                'success' => false,
                'message' => 'Path is required.',
            ], 400);
        }

        try {
            $fileInfo = $this->storageService->getFileInfo($path);
            $fileType = $fileInfo['file_type'];
            $content = null;
            $highlighted = false;
            $tailPreview = false;

            $textExtensions = array_merge(
                config('storage-explorer.preview.text_extensions', []),
                config('storage-explorer.preview.code_extensions', []),
            );
            $imageExtensions = config('storage-explorer.preview.image_extensions', []);
            $maxSize = config('storage-explorer.preview.max_size', 2 * 1024 * 1024);

            $isTextFile = $fileType === 'log' || in_array($fileInfo['extension'], $textExtensions, true);

            if ($isTextFile) {
                $rawContent = $this->storageService->readFileContents($path);

                if ($rawContent === null && $fileInfo['size'] > $maxSize) {
                    // File too large for full preview — use tail
                    $tailData = $this->storageService->readFileTail($path);
                    if ($tailData !== null) {
                        $rawContent = $tailData['content'];
                        $tailPreview = [
                            'truncated' => $tailData['truncated'],
                            'total_size' => $tailData['total_size'],
                            'total_size_formatted' => $tailData['total_size_formatted'],
                            'tail_size_formatted' => $tailData['tail_size_formatted'],
                        ];
                    }
                }

                if ($rawContent !== null && $fileType === 'log') {
                    $content = $this->storageService->highlightLogContent($rawContent);
                    $highlighted = true;
                } else {
                    $content = $rawContent;
                }
            } elseif (in_array($fileInfo['extension'], $imageExtensions, true)) {
                $content = $this->storageService->getImageBase64($path);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'info' => $fileInfo,
                    'content' => $content,
                    'highlighted' => $highlighted,
                    'previewable' => $content !== null,
                    'tailPreview' => $tailPreview,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        try {
            $results = $this->storageService->search($query);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
