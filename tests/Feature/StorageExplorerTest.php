<?php

namespace Jakovic\StorageExplorer\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Jakovic\StorageExplorer\Tests\TestCase;

class StorageExplorerTest extends TestCase
{
    public function test_index_page_loads(): void
    {
        $response = $this->get(route('storage-explorer.index'));

        $response->assertStatus(200);
        $response->assertSee('Storage Explorer');
    }

    public function test_tree_api_returns_json(): void
    {
        Storage::disk('local')->put('test.txt', 'content');

        $response = $this->getJson(route('storage-explorer.tree', ['path' => '']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['name', 'path', 'is_directory', 'size', 'size_formatted', 'extension', 'file_type'],
            ],
            'path',
        ]);
        $response->assertJsonFragment(['name' => 'test.txt']);
    }

    public function test_tree_api_returns_subdirectory(): void
    {
        Storage::disk('local')->put('subdir/file.txt', 'content');

        $response = $this->getJson(route('storage-explorer.tree', ['path' => 'subdir']));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'file.txt']);
    }

    public function test_preview_api_returns_text_content(): void
    {
        Storage::disk('local')->put('hello.txt', 'Hello World');

        $response = $this->getJson(route('storage-explorer.preview', ['path' => 'hello.txt']));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.content', 'Hello World');
        $response->assertJsonPath('data.previewable', true);
        $response->assertJsonPath('data.info.name', 'hello.txt');
    }

    public function test_preview_api_highlights_log_files(): void
    {
        Storage::disk('local')->put('app.log', '[2024-01-01] ERROR: Something failed');

        $response = $this->getJson(route('storage-explorer.preview', ['path' => 'app.log']));

        $response->assertStatus(200);
        $response->assertJsonPath('data.highlighted', true);
        $this->assertStringContainsString('text-red-600', $response->json('data.content'));
    }

    public function test_preview_api_returns_image_base64(): void
    {
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        Storage::disk('local')->put('image.png', $pixel);

        $response = $this->getJson(route('storage-explorer.preview', ['path' => 'image.png']));

        $response->assertStatus(200);
        $response->assertJsonPath('data.previewable', true);
        $this->assertStringStartsWith('data:image/png;base64,', $response->json('data.content'));
    }

    public function test_preview_requires_path(): void
    {
        $response = $this->getJson(route('storage-explorer.preview'));

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }

    public function test_search_api_returns_results(): void
    {
        Storage::disk('local')->put('report.txt', 'content');
        Storage::disk('local')->put('data.csv', 'content');

        $response = $this->getJson(route('storage-explorer.search', ['q' => 'report']));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['name' => 'report.txt']);
    }

    public function test_search_api_returns_empty_for_short_query(): void
    {
        $response = $this->getJson(route('storage-explorer.search', ['q' => 'a']));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(0, 'data');
    }

    public function test_search_api_finds_nested_files(): void
    {
        Storage::disk('local')->put('deep/nested/report.txt', 'content');

        $response = $this->getJson(route('storage-explorer.search', ['q' => 'report']));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }
}
