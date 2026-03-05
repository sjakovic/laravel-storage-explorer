<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk to browse. Must be defined in config/filesystems.php.
    |
    */

    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Root Path
    |--------------------------------------------------------------------------
    |
    | Subdirectory within the disk to use as root. Leave empty for disk root.
    |
    */

    'root_path' => '',

    /*
    |--------------------------------------------------------------------------
    | Standalone Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the package registers its own routes so you can visit
    | the explorer at /storage-explorer (or your custom prefix).
    |
    */

    'standalone' => [
        'enabled' => true,
        'prefix' => 'storage-explorer',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout (Embedded Mode)
    |--------------------------------------------------------------------------
    |
    | When set to a layout name (e.g. 'layout.base.subheader'), the explorer
    | renders inside the host application's layout instead of standalone HTML.
    | Set to null for standalone mode.
    |
    */

    'layout' => null,
    'content_section' => 'content-page',
    'page_title' => 'Storage Explorer',

    /*
    |--------------------------------------------------------------------------
    | Hidden Patterns
    |--------------------------------------------------------------------------
    |
    | Files matching these names will be hidden from the tree view.
    |
    */

    'hidden_patterns' => [
        '.gitignore',
        '.gitkeep',
        '.DS_Store',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Preview
    |--------------------------------------------------------------------------
    */

    'preview' => [
        'max_size' => 2 * 1024 * 1024, // 2 MB
        'tail_size' => 64 * 1024, // 64 KB — bytes to read from end for large files
        'max_lines' => 1000,
        'text_extensions' => [
            'txt', 'log', 'md', 'csv', 'json', 'xml', 'yml', 'yaml',
            'env', 'ini', 'conf', 'cfg', 'htaccess',
        ],
        'image_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico',
        ],
        'code_extensions' => [
            'php', 'js', 'ts', 'css', 'scss', 'html', 'vue', 'jsx', 'tsx',
            'py', 'rb', 'go', 'rs', 'java', 'c', 'cpp', 'h', 'sh', 'bash',
            'sql', 'graphql',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'enabled' => true,
        'max_size' => 10240, // KB
        'blocked_extensions' => [
            'php', 'phar', 'sh', 'exe', 'bat', 'cmd', 'com', 'msi',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Delete Settings
    |--------------------------------------------------------------------------
    */

    'delete' => [
        'enabled' => true,
        'allow_directory_delete' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tree Settings
    |--------------------------------------------------------------------------
    */

    'tree' => [
        'lazy_load' => true,
        'max_depth' => 20,
        'max_items_per_directory' => 500,
    ],

];
