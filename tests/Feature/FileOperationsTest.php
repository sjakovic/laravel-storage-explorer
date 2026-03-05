<?php

namespace Jakovic\StorageExplorer\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Jakovic\StorageExplorer\Tests\TestCase;

class FileOperationsTest extends TestCase
{
    public function test_download_with_valid_signed_url(): void
    {
        Storage::disk('local')->put('download.txt', 'file content');

        // Get a signed download URL first
        $urlResponse = $this->getJson(route('storage-explorer.download-url', ['path' => 'download.txt']));
        $urlResponse->assertJsonPath('success', true);

        $downloadUrl = $urlResponse->json('url');
        $response = $this->get($downloadUrl);

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_download_rejects_invalid_signature(): void
    {
        Storage::disk('local')->put('secret.txt', 'content');

        $response = $this->get(route('storage-explorer.download', ['path' => 'secret.txt']));

        $response->assertStatus(403);
    }

    public function test_download_url_requires_path(): void
    {
        $response = $this->getJson(route('storage-explorer.download-url'));

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }

    public function test_upload_file(): void
    {
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->postJson(route('storage-explorer.upload'), [
            'file' => $file,
            'directory' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        Storage::disk('local')->assertExists('document.txt');
    }

    public function test_upload_to_subdirectory(): void
    {
        Storage::disk('local')->makeDirectory('uploads');
        $file = UploadedFile::fake()->create('photo.jpg', 200);

        $response = $this->postJson(route('storage-explorer.upload'), [
            'file' => $file,
            'directory' => 'uploads',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        Storage::disk('local')->assertExists('uploads/photo.jpg');
    }

    public function test_upload_blocks_php_files(): void
    {
        $file = UploadedFile::fake()->create('malicious.php', 100);

        $response = $this->postJson(route('storage-explorer.upload'), [
            'file' => $file,
            'directory' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_upload_blocks_exe_files(): void
    {
        $file = UploadedFile::fake()->create('virus.exe', 100);

        $response = $this->postJson(route('storage-explorer.upload'), [
            'file' => $file,
            'directory' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_upload_disabled_returns_403(): void
    {
        config(['storage-explorer.upload.enabled' => false]);

        $file = UploadedFile::fake()->create('doc.txt', 100);

        $response = $this->postJson(route('storage-explorer.upload'), [
            'file' => $file,
            'directory' => '',
        ]);

        $response->assertStatus(403);
    }

    public function test_upload_requires_file(): void
    {
        $response = $this->postJson(route('storage-explorer.upload'), [
            'directory' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_delete_file(): void
    {
        Storage::disk('local')->put('deleteme.txt', 'content');

        $response = $this->deleteJson(route('storage-explorer.delete'), [
            'path' => 'deleteme.txt',
            'is_directory' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        Storage::disk('local')->assertMissing('deleteme.txt');
    }

    public function test_delete_requires_path(): void
    {
        $response = $this->deleteJson(route('storage-explorer.delete'), [
            'path' => '',
        ]);

        $response->assertStatus(400);
    }

    public function test_delete_disabled_returns_403(): void
    {
        config(['storage-explorer.delete.enabled' => false]);

        $response = $this->deleteJson(route('storage-explorer.delete'), [
            'path' => 'test.txt',
        ]);

        $response->assertStatus(403);
    }

    public function test_directory_delete_disabled_by_default(): void
    {
        Storage::disk('local')->makeDirectory('testdir');

        $response = $this->deleteJson(route('storage-explorer.delete'), [
            'path' => 'testdir',
            'is_directory' => true,
        ]);

        $response->assertStatus(403);
    }
}
