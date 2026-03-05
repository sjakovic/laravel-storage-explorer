<?php

namespace Jakovic\StorageExplorer\Enums;

enum FileType: string
{
    case Text = 'text';
    case Log = 'log';
    case Image = 'image';
    case Pdf = 'pdf';
    case Video = 'video';
    case Audio = 'audio';
    case Archive = 'archive';
    case Code = 'code';
    case Unknown = 'unknown';

    public static function fromExtension(string $extension): self
    {
        $extension = strtolower($extension);

        $map = [
            // Text
            'txt' => self::Text,
            'md' => self::Text,
            'csv' => self::Text,
            'json' => self::Text,
            'xml' => self::Text,
            'yml' => self::Text,
            'yaml' => self::Text,
            'env' => self::Text,
            'ini' => self::Text,
            'conf' => self::Text,
            'cfg' => self::Text,
            'htaccess' => self::Text,

            // Log
            'log' => self::Log,

            // Image
            'jpg' => self::Image,
            'jpeg' => self::Image,
            'png' => self::Image,
            'gif' => self::Image,
            'svg' => self::Image,
            'webp' => self::Image,
            'bmp' => self::Image,
            'ico' => self::Image,

            // PDF
            'pdf' => self::Pdf,

            // Video
            'mp4' => self::Video,
            'avi' => self::Video,
            'mov' => self::Video,
            'wmv' => self::Video,
            'mkv' => self::Video,
            'webm' => self::Video,

            // Audio
            'mp3' => self::Audio,
            'wav' => self::Audio,
            'ogg' => self::Audio,
            'flac' => self::Audio,
            'aac' => self::Audio,

            // Archive
            'zip' => self::Archive,
            'tar' => self::Archive,
            'gz' => self::Archive,
            'rar' => self::Archive,
            '7z' => self::Archive,
            'bz2' => self::Archive,

            // Code
            'php' => self::Code,
            'js' => self::Code,
            'ts' => self::Code,
            'css' => self::Code,
            'scss' => self::Code,
            'html' => self::Code,
            'vue' => self::Code,
            'jsx' => self::Code,
            'tsx' => self::Code,
            'py' => self::Code,
            'rb' => self::Code,
            'go' => self::Code,
            'rs' => self::Code,
            'java' => self::Code,
            'c' => self::Code,
            'cpp' => self::Code,
            'h' => self::Code,
            'sh' => self::Code,
            'bash' => self::Code,
            'sql' => self::Code,
            'graphql' => self::Code,
        ];

        return $map[$extension] ?? self::Unknown;
    }
}
