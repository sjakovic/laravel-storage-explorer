<?php

namespace Jakovic\StorageExplorer\Tests\Unit;

use Jakovic\StorageExplorer\Enums\FileType;
use PHPUnit\Framework\TestCase;

class FileTypeTest extends TestCase
{
    public function test_text_extensions(): void
    {
        $this->assertSame(FileType::Text, FileType::fromExtension('txt'));
        $this->assertSame(FileType::Text, FileType::fromExtension('md'));
        $this->assertSame(FileType::Text, FileType::fromExtension('csv'));
        $this->assertSame(FileType::Text, FileType::fromExtension('json'));
        $this->assertSame(FileType::Text, FileType::fromExtension('xml'));
        $this->assertSame(FileType::Text, FileType::fromExtension('yml'));
        $this->assertSame(FileType::Text, FileType::fromExtension('yaml'));
    }

    public function test_log_extension(): void
    {
        $this->assertSame(FileType::Log, FileType::fromExtension('log'));
    }

    public function test_image_extensions(): void
    {
        $this->assertSame(FileType::Image, FileType::fromExtension('jpg'));
        $this->assertSame(FileType::Image, FileType::fromExtension('jpeg'));
        $this->assertSame(FileType::Image, FileType::fromExtension('png'));
        $this->assertSame(FileType::Image, FileType::fromExtension('gif'));
        $this->assertSame(FileType::Image, FileType::fromExtension('svg'));
        $this->assertSame(FileType::Image, FileType::fromExtension('webp'));
    }

    public function test_code_extensions(): void
    {
        $this->assertSame(FileType::Code, FileType::fromExtension('php'));
        $this->assertSame(FileType::Code, FileType::fromExtension('js'));
        $this->assertSame(FileType::Code, FileType::fromExtension('ts'));
        $this->assertSame(FileType::Code, FileType::fromExtension('css'));
        $this->assertSame(FileType::Code, FileType::fromExtension('py'));
    }

    public function test_archive_extensions(): void
    {
        $this->assertSame(FileType::Archive, FileType::fromExtension('zip'));
        $this->assertSame(FileType::Archive, FileType::fromExtension('tar'));
        $this->assertSame(FileType::Archive, FileType::fromExtension('gz'));
    }

    public function test_pdf_extension(): void
    {
        $this->assertSame(FileType::Pdf, FileType::fromExtension('pdf'));
    }

    public function test_video_extensions(): void
    {
        $this->assertSame(FileType::Video, FileType::fromExtension('mp4'));
        $this->assertSame(FileType::Video, FileType::fromExtension('avi'));
        $this->assertSame(FileType::Video, FileType::fromExtension('mkv'));
    }

    public function test_audio_extensions(): void
    {
        $this->assertSame(FileType::Audio, FileType::fromExtension('mp3'));
        $this->assertSame(FileType::Audio, FileType::fromExtension('wav'));
        $this->assertSame(FileType::Audio, FileType::fromExtension('ogg'));
    }

    public function test_unknown_extension(): void
    {
        $this->assertSame(FileType::Unknown, FileType::fromExtension('xyz'));
        $this->assertSame(FileType::Unknown, FileType::fromExtension(''));
        $this->assertSame(FileType::Unknown, FileType::fromExtension('asdf'));
    }

    public function test_case_insensitive(): void
    {
        $this->assertSame(FileType::Image, FileType::fromExtension('JPG'));
        $this->assertSame(FileType::Image, FileType::fromExtension('Png'));
        $this->assertSame(FileType::Code, FileType::fromExtension('PHP'));
    }

    public function test_enum_values(): void
    {
        $this->assertSame('text', FileType::Text->value);
        $this->assertSame('log', FileType::Log->value);
        $this->assertSame('image', FileType::Image->value);
        $this->assertSame('pdf', FileType::Pdf->value);
        $this->assertSame('video', FileType::Video->value);
        $this->assertSame('audio', FileType::Audio->value);
        $this->assertSame('archive', FileType::Archive->value);
        $this->assertSame('code', FileType::Code->value);
        $this->assertSame('unknown', FileType::Unknown->value);
    }
}
