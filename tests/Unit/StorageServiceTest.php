<?php

namespace Jakovic\StorageExplorer\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Jakovic\StorageExplorer\Services\StorageService;
use Jakovic\StorageExplorer\Tests\TestCase;

class StorageServiceTest extends TestCase
{
    protected StorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StorageService::class);
    }

    public function test_validate_path_allows_valid_paths(): void
    {
        $this->assertSame('test.txt', $this->service->validatePath('test.txt'));
        $this->assertSame('subdir/file.txt', $this->service->validatePath('subdir/file.txt'));
        $this->assertSame('', $this->service->validatePath(''));
    }

    public function test_validate_path_rejects_traversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->validatePath('../etc/passwd');
    }

    public function test_validate_path_rejects_encoded_traversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->validatePath('..%2Fetc%2Fpasswd');
    }

    public function test_validate_path_rejects_null_bytes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->validatePath("test\0.txt");
    }

    public function test_validate_path_rejects_double_dots_in_middle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->validatePath('subdir/../../etc/passwd');
    }

    public function test_get_directory_contents_returns_sorted(): void
    {
        Storage::disk('local')->put('b_file.txt', 'content');
        Storage::disk('local')->put('a_file.txt', 'content');
        Storage::disk('local')->makeDirectory('z_dir');
        Storage::disk('local')->makeDirectory('a_dir');

        $contents = $this->service->getDirectoryContents('');

        // Directories first, then files, both sorted alphabetically
        $names = array_column($contents, 'name');
        $this->assertSame('a_dir', $names[0]);
        $this->assertSame('z_dir', $names[1]);
        $this->assertSame('a_file.txt', $names[2]);
        $this->assertSame('b_file.txt', $names[3]);
    }

    public function test_get_directory_contents_returns_correct_structure(): void
    {
        Storage::disk('local')->put('test.txt', 'hello world');

        $contents = $this->service->getDirectoryContents('');
        $file = collect($contents)->firstWhere('name', 'test.txt');

        $this->assertNotNull($file);
        $this->assertSame('test.txt', $file['name']);
        $this->assertSame('test.txt', $file['path']);
        $this->assertFalse($file['is_directory']);
        $this->assertSame('txt', $file['extension']);
        $this->assertSame('text', $file['file_type']);
        $this->assertSame(11, $file['size']);
        $this->assertFalse($file['has_children']);
    }

    public function test_get_directory_contents_identifies_directories(): void
    {
        Storage::disk('local')->makeDirectory('subdir');
        Storage::disk('local')->put('subdir/file.txt', 'content');

        $contents = $this->service->getDirectoryContents('');
        $dir = collect($contents)->firstWhere('name', 'subdir');

        $this->assertNotNull($dir);
        $this->assertTrue($dir['is_directory']);
        $this->assertTrue($dir['has_children']);
    }

    public function test_hidden_files_are_excluded(): void
    {
        Storage::disk('local')->put('.gitignore', 'content');
        Storage::disk('local')->put('.DS_Store', 'content');
        Storage::disk('local')->put('visible.txt', 'content');

        $contents = $this->service->getDirectoryContents('');
        $names = array_column($contents, 'name');

        $this->assertNotContains('.gitignore', $names);
        $this->assertNotContains('.DS_Store', $names);
        $this->assertContains('visible.txt', $names);
    }

    public function test_read_file_contents(): void
    {
        Storage::disk('local')->put('test.txt', 'line 1\nline 2\nline 3');

        $content = $this->service->readFileContents('test.txt');
        $this->assertNotNull($content);
        $this->assertStringContainsString('line 1', $content);
    }

    public function test_read_file_contents_returns_null_for_missing_file(): void
    {
        $content = $this->service->readFileContents('nonexistent.txt');
        $this->assertNull($content);
    }

    public function test_read_file_contents_truncates_at_max_lines(): void
    {
        $lines = implode("\n", array_fill(0, 100, 'test line'));
        Storage::disk('local')->put('long.txt', $lines);

        $content = $this->service->readFileContents('long.txt', 10);
        $lineCount = substr_count($content, "\n");
        $this->assertLessThanOrEqual(12, $lineCount); // 10 lines + truncation message
    }

    public function test_highlight_log_content(): void
    {
        $log = "[2024-01-01] ERROR: Something failed\n[2024-01-01] INFO: All good\n[2024-01-01] WARNING: Careful";

        $highlighted = $this->service->highlightLogContent($log);

        $this->assertStringContainsString('text-red-600', $highlighted);
        $this->assertStringContainsString('text-blue-600', $highlighted);
        $this->assertStringContainsString('text-amber-600', $highlighted);
    }

    public function test_highlight_log_content_escapes_html(): void
    {
        $log = '<script>alert("xss")</script> ERROR: test';
        $highlighted = $this->service->highlightLogContent($log);

        $this->assertStringNotContainsString('<script>', $highlighted);
        $this->assertStringContainsString('&lt;script&gt;', $highlighted);
    }

    public function test_search_finds_files(): void
    {
        Storage::disk('local')->put('report.txt', 'content');
        Storage::disk('local')->put('data.csv', 'content');
        Storage::disk('local')->put('subdir/report2.txt', 'content');

        $results = $this->service->search('report');

        $this->assertCount(2, $results);
        $names = array_column($results, 'name');
        $this->assertContains('report.txt', $names);
        $this->assertContains('report2.txt', $names);
    }

    public function test_search_is_case_insensitive(): void
    {
        Storage::disk('local')->put('Report.TXT', 'content');

        $results = $this->service->search('report');
        $this->assertCount(1, $results);
    }

    public function test_search_respects_max_results(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Storage::disk('local')->put("file{$i}.txt", 'content');
        }

        $results = $this->service->search('file', 3);
        $this->assertCount(3, $results);
    }

    public function test_search_returns_empty_for_short_query(): void
    {
        Storage::disk('local')->put('test.txt', 'content');

        $results = $this->service->search('');
        $this->assertCount(0, $results);
    }

    public function test_delete_file(): void
    {
        Storage::disk('local')->put('deleteme.txt', 'content');
        $this->assertTrue(Storage::disk('local')->exists('deleteme.txt'));

        $result = $this->service->deleteFile('deleteme.txt');

        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('local')->exists('deleteme.txt'));
    }

    public function test_delete_file_throws_when_disabled(): void
    {
        config(['storage-explorer.delete.enabled' => false]);

        $this->expectException(\RuntimeException::class);
        $this->service->deleteFile('test.txt');
    }

    public function test_delete_directory_throws_when_disabled(): void
    {
        config(['storage-explorer.delete.allow_directory_delete' => false]);

        $this->expectException(\RuntimeException::class);
        $this->service->deleteDirectory('subdir');
    }

    public function test_format_bytes(): void
    {
        $this->assertSame('0 B', $this->service->formatBytes(0));
        $this->assertSame('500 B', $this->service->formatBytes(500));
        $this->assertSame('1 KB', $this->service->formatBytes(1024));
        $this->assertSame('1.5 KB', $this->service->formatBytes(1536));
        $this->assertSame('1 MB', $this->service->formatBytes(1048576));
    }

    public function test_is_hidden(): void
    {
        $patterns = ['.gitignore', '.DS_Store'];

        $this->assertTrue($this->service->isHidden('.gitignore', $patterns));
        $this->assertTrue($this->service->isHidden('.DS_Store', $patterns));
        $this->assertFalse($this->service->isHidden('readme.txt', $patterns));
    }

    public function test_get_image_base64(): void
    {
        // Create a tiny 1x1 PNG
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        Storage::disk('local')->put('test.png', $pixel);

        $base64 = $this->service->getImageBase64('test.png');
        $this->assertNotNull($base64);
        $this->assertStringStartsWith('data:image/png;base64,', $base64);
    }

    public function test_get_image_base64_returns_null_for_missing_file(): void
    {
        $result = $this->service->getImageBase64('nonexistent.png');
        $this->assertNull($result);
    }

    public function test_subdirectory_contents(): void
    {
        Storage::disk('local')->put('subdir/file1.txt', 'content');
        Storage::disk('local')->put('subdir/file2.txt', 'content');

        $contents = $this->service->getDirectoryContents('subdir');

        $this->assertCount(2, $contents);
        $names = array_column($contents, 'name');
        $this->assertContains('file1.txt', $names);
        $this->assertContains('file2.txt', $names);
    }
}
