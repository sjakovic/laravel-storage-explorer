<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Storage Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        .tree-line {
            border-left: 1px solid #e5e7eb;
        }

        pre.log-content span {
            display: inline;
        }

        .notification-enter {
            animation: slideIn 0.3s ease-out;
        }

        .notification-leave {
            animation: slideOut 0.3s ease-in;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        .se-tail-banner {
            background-color: #fef3c7;
            color: #92400e;
            border-bottom: 1px solid #fde68a;
        }

        .se-dir-delete-btn {
            padding: 2px;
            border-radius: 4px;
        }
        .se-dir-delete-btn:hover {
            background-color: #fef2f2;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    @include('storage-explorer::explorer', [
        'deleteEnabled' => $deleteEnabled ?? true,
        'uploadEnabled' => $uploadEnabled ?? true,
        'apiBase' => $apiBase,
    ])
</body>
</html>
