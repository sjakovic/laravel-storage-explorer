<?php

namespace Jakovic\StorageExplorer\Tests\Feature;

use Jakovic\StorageExplorer\Tests\TestCase;

class SecurityTest extends TestCase
{
    public function test_path_traversal_with_double_dots(): void
    {
        $response = $this->getJson(route('storage-explorer.tree', ['path' => '../etc']));

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
    }

    public function test_path_traversal_with_encoded_dots(): void
    {
        $response = $this->getJson(route('storage-explorer.tree', ['path' => '..%2Fetc']));

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
    }

    public function test_path_traversal_in_preview(): void
    {
        $response = $this->getJson(route('storage-explorer.preview', ['path' => '../../etc/passwd']));

        $response->assertStatus(403);
    }

    public function test_path_traversal_in_search(): void
    {
        // Search uses query parameter, not path - should still work safely
        $response = $this->getJson(route('storage-explorer.search', ['q' => '../etc']));

        // Search should just return no results, not expose traversal
        $response->assertStatus(200);
    }

    public function test_path_traversal_in_delete(): void
    {
        $response = $this->deleteJson(route('storage-explorer.delete'), [
            'path' => '../../../etc/passwd',
            'is_directory' => false,
        ]);

        $response->assertStatus(403);
    }

    public function test_path_traversal_in_upload_directory(): void
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('test.txt', 100);

        $response = $this->postJson(route('storage-explorer.upload'), [
            'file' => $file,
            'directory' => '../../etc',
        ]);

        $response->assertStatus(403);
    }

    public function test_null_byte_in_path(): void
    {
        $response = $this->getJson(route('storage-explorer.tree', ['path' => "test\0.txt"]));

        $response->assertStatus(403);
    }

    public function test_path_traversal_with_nested_dots(): void
    {
        $response = $this->getJson(route('storage-explorer.tree', ['path' => 'subdir/../../etc']));

        $response->assertStatus(403);
    }

    public function test_download_requires_signed_url(): void
    {
        $response = $this->get(route('storage-explorer.download', ['path' => 'test.txt']));

        $response->assertStatus(403);
    }

    public function test_blocked_upload_extensions(): void
    {
        $extensions = ['php', 'phar', 'sh', 'exe', 'bat', 'cmd'];

        foreach ($extensions as $ext) {
            $file = \Illuminate\Http\UploadedFile::fake()->create("malicious.{$ext}", 100);

            $response = $this->postJson(route('storage-explorer.upload'), [
                'file' => $file,
                'directory' => '',
            ]);

            $response->assertStatus(422, "Extension .{$ext} should be blocked");
        }
    }
}
