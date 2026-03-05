<?php

namespace Jakovic\StorageExplorer\Services;

use Illuminate\Support\Facades\Storage;
use Jakovic\StorageExplorer\Enums\FileType;

class StorageService
{
    protected \Illuminate\Filesystem\FilesystemAdapter $disk;

    protected string $rootPath;

    public function __construct(string $diskName, string $rootPath = '')
    {
        $this->disk = Storage::disk($diskName);
        $this->rootPath = $rootPath ? trim($rootPath, '/').'/' : '';
    }

    public function validatePath(string $path): string
    {
        // Reject null bytes
        if (str_contains($path, "\0")) {
            throw new \InvalidArgumentException('Invalid path.');
        }

        // Decode and reject traversal attempts
        $decoded = urldecode($path);
        if (str_contains($decoded, '..') || str_contains($decoded, "\0")) {
            throw new \InvalidArgumentException('Path traversal is not allowed.');
        }

        // Normalize the path
        $path = ltrim($path, '/');
        $fullPath = $this->rootPath.$path;

        // Ensure path stays within root
        $normalized = $this->normalizePath($fullPath);
        $normalizedRoot = $this->rootPath ? $this->normalizePath($this->rootPath) : '';

        if ($normalizedRoot && ! str_starts_with($normalized.'/', $normalizedRoot) && $normalized !== rtrim($normalizedRoot, '/')) {
            throw new \InvalidArgumentException('Path is outside allowed root.');
        }

        return $path;
    }

    protected function normalizePath(string $path): string
    {
        $parts = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);
            } else {
                $parts[] = $segment;
            }
        }

        return implode('/', $parts);
    }

    public function getDirectoryContents(string $path): array
    {
        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;
        $maxItems = config('storage-explorer.tree.max_items_per_directory', 500);
        $hiddenPatterns = config('storage-explorer.hidden_patterns', []);

        $directories = [];
        $files = [];

        foreach ($this->disk->directories($fullPath) as $dir) {
            $name = basename($dir);

            if ($this->isHidden($name, $hiddenPatterns)) {
                continue;
            }

            $relativePath = $this->rootPath ? str_replace($this->rootPath, '', $dir) : $dir;

            $directories[] = [
                'name' => $name,
                'path' => $relativePath,
                'is_directory' => true,
                'size' => null,
                'size_formatted' => '-',
                'last_modified' => null,
                'extension' => null,
                'file_type' => null,
                'has_children' => $this->hasChildren($dir),
            ];
        }

        foreach ($this->disk->files($fullPath) as $file) {
            $name = basename($file);

            if ($this->isHidden($name, $hiddenPatterns)) {
                continue;
            }

            $relativePath = $this->rootPath ? str_replace($this->rootPath, '', $file) : $file;
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $size = $this->disk->size($file);

            $files[] = [
                'name' => $name,
                'path' => $relativePath,
                'is_directory' => false,
                'size' => $size,
                'size_formatted' => $this->formatBytes($size),
                'last_modified' => $this->disk->lastModified($file),
                'extension' => $extension,
                'file_type' => FileType::fromExtension($extension)->value,
                'has_children' => false,
            ];
        }

        // Sort alphabetically
        usort($directories, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        usort($files, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        $results = array_merge($directories, $files);

        return array_slice($results, 0, $maxItems);
    }

    protected function hasChildren(string $dirPath): bool
    {
        return count($this->disk->directories($dirPath)) > 0
            || count($this->disk->files($dirPath)) > 0;
    }

    public function readFileContents(string $path, ?int $maxLines = null): ?string
    {
        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;
        $maxLines = $maxLines ?? config('storage-explorer.preview.max_lines', 1000);
        $maxSize = config('storage-explorer.preview.max_size', 2 * 1024 * 1024);

        if (! $this->disk->exists($fullPath)) {
            return null;
        }

        $size = $this->disk->size($fullPath);
        if ($size > $maxSize) {
            return null;
        }

        $content = $this->disk->get($fullPath);
        if ($content === null) {
            return null;
        }

        $lines = explode("\n", $content);
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[] = "\n--- Truncated at {$maxLines} lines ---";
        }

        return implode("\n", $lines);
    }

    public function readFileTail(string $path, ?int $tailBytes = null): ?array
    {
        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;
        $tailBytes = $tailBytes ?? config('storage-explorer.preview.tail_size', 64 * 1024);

        if (! $this->disk->exists($fullPath)) {
            return null;
        }

        $totalSize = $this->disk->size($fullPath);
        $realPath = $this->disk->path($fullPath);

        $fh = fopen($realPath, 'rb');
        if ($fh === false) {
            return null;
        }

        try {
            $readBytes = min($tailBytes, $totalSize);

            if ($totalSize > $tailBytes) {
                fseek($fh, -$readBytes, SEEK_END);
            }

            $content = fread($fh, $readBytes);

            if ($content === false) {
                return null;
            }

            // Trim partial first line when truncated
            if ($totalSize > $tailBytes) {
                $newlinePos = strpos($content, "\n");
                if ($newlinePos !== false) {
                    $content = substr($content, $newlinePos + 1);
                }
            }

            return [
                'content' => $content,
                'truncated' => $totalSize > $tailBytes,
                'total_size' => $totalSize,
                'total_size_formatted' => $this->formatBytes($totalSize),
                'tail_size_formatted' => $this->formatBytes($tailBytes),
            ];
        } finally {
            fclose($fh);
        }
    }

    public function getImageBase64(string $path): ?string
    {
        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;
        $maxSize = config('storage-explorer.preview.max_size', 2 * 1024 * 1024);

        if (! $this->disk->exists($fullPath)) {
            return null;
        }

        if ($this->disk->size($fullPath) > $maxSize) {
            return null;
        }

        $content = $this->disk->get($fullPath);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
        ];

        $mime = $mimeTypes[$extension] ?? 'application/octet-stream';

        return "data:{$mime};base64,".base64_encode($content);
    }

    public function highlightLogContent(string $content): string
    {
        $escaped = e($content);

        $patterns = [
            '/^(.*?\b(EMERGENCY|ALERT|CRITICAL|ERROR)\b.*)$/m' => '<span class="text-red-600">$1</span>',
            '/^(.*?\b(WARNING)\b.*)$/m' => '<span class="text-amber-600">$1</span>',
            '/^(.*?\b(NOTICE|INFO)\b.*)$/m' => '<span class="text-blue-600">$1</span>',
            '/^(.*?\b(DEBUG)\b.*)$/m' => '<span class="text-gray-500">$1</span>',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $escaped = preg_replace($pattern, $replacement, $escaped);
        }

        return $escaped;
    }

    public function search(string $query, ?int $maxResults = null): array
    {
        $maxResults = $maxResults ?? 50;
        $query = strtolower(trim($query));
        $hiddenPatterns = config('storage-explorer.hidden_patterns', []);

        if ($query === '') {
            return [];
        }

        $results = [];
        $this->searchRecursive($this->rootPath ?: '', $query, $results, $maxResults, 0, $hiddenPatterns);

        return $results;
    }

    protected function searchRecursive(
        string $dir,
        string $query,
        array &$results,
        int $maxResults,
        int $depth,
        array $hiddenPatterns,
    ): void {
        $maxDepth = config('storage-explorer.tree.max_depth', 20);

        if ($depth > $maxDepth || count($results) >= $maxResults) {
            return;
        }

        foreach ($this->disk->files($dir) as $file) {
            if (count($results) >= $maxResults) {
                return;
            }

            $name = basename($file);
            if ($this->isHidden($name, $hiddenPatterns)) {
                continue;
            }

            if (str_contains(strtolower($name), $query)) {
                $relativePath = $this->rootPath ? str_replace($this->rootPath, '', $file) : $file;
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $size = $this->disk->size($file);

                $results[] = [
                    'name' => $name,
                    'path' => $relativePath,
                    'is_directory' => false,
                    'size' => $size,
                    'size_formatted' => $this->formatBytes($size),
                    'extension' => $extension,
                    'file_type' => FileType::fromExtension($extension)->value,
                ];
            }
        }

        foreach ($this->disk->directories($dir) as $subDir) {
            if (count($results) >= $maxResults) {
                return;
            }

            $name = basename($subDir);
            if ($this->isHidden($name, $hiddenPatterns)) {
                continue;
            }

            if (str_contains(strtolower($name), $query)) {
                $relativePath = $this->rootPath ? str_replace($this->rootPath, '', $subDir) : $subDir;

                $results[] = [
                    'name' => $name,
                    'path' => $relativePath,
                    'is_directory' => true,
                    'size' => null,
                    'size_formatted' => '-',
                    'extension' => null,
                    'file_type' => null,
                ];
            }

            $this->searchRecursive($subDir, $query, $results, $maxResults, $depth + 1, $hiddenPatterns);
        }
    }

    public function createDirectory(string $path): bool
    {
        if (! config('storage-explorer.upload.enabled', true)) {
            throw new \RuntimeException('Upload is disabled.');
        }

        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;

        return $this->disk->makeDirectory($fullPath);
    }

    public function deleteFile(string $path): bool
    {
        if (! config('storage-explorer.delete.enabled', true)) {
            throw new \RuntimeException('File deletion is disabled.');
        }

        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;

        return $this->disk->delete($fullPath);
    }

    public function deleteDirectory(string $path): bool
    {
        if (! config('storage-explorer.delete.enabled', true)) {
            throw new \RuntimeException('Deletion is disabled.');
        }

        if (! config('storage-explorer.delete.allow_directory_delete', false)) {
            throw new \RuntimeException('Directory deletion is disabled.');
        }

        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;

        return $this->disk->deleteDirectory($fullPath);
    }

    public function getFileInfo(string $path): array
    {
        $path = $this->validatePath($path);
        $fullPath = $this->rootPath.$path;

        $name = basename($path);
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $size = $this->disk->size($fullPath);

        return [
            'name' => $name,
            'path' => $path,
            'extension' => $extension,
            'file_type' => FileType::fromExtension($extension)->value,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'last_modified' => $this->disk->lastModified($fullPath),
        ];
    }

    public function getDisk(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return $this->disk;
    }

    public function getFullPath(string $path): string
    {
        return $this->rootPath.$this->validatePath($path);
    }

    public function isHidden(string $name, array $patterns): bool
    {
        return in_array($name, $patterns, true);
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
