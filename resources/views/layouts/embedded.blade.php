@extends($layout)

@push('styles')
    <style>
        /* Storage Explorer — self-contained styles (no Tailwind CDN needed) */
        #storage-explorer { max-width: 1536px; margin-left: auto; margin-right: auto; padding: 1rem; box-sizing: border-box; }
        #storage-explorer [x-cloak] { display: none !important; }
        #storage-explorer *, #storage-explorer *::before, #storage-explorer *::after { box-sizing: border-box; }

        /* --- Display / Flex --- */
        /* No !important on display properties — Alpine x-show uses inline display:none */
        #storage-explorer .flex { display: flex; }
        #storage-explorer .inline-flex { display: inline-flex; }
        #storage-explorer .block { display: block; }
        #storage-explorer .hidden { display: none; }
        #storage-explorer .flex-col { flex-direction: column !important; }
        #storage-explorer .flex-1 { flex: 1 1 0% !important; }
        #storage-explorer .flex-shrink-0 { flex-shrink: 0 !important; }
        #storage-explorer .items-center { align-items: center !important; }
        #storage-explorer .justify-between { justify-content: space-between !important; }
        #storage-explorer .justify-center { justify-content: center !important; }
        #storage-explorer .justify-end { justify-content: flex-end !important; }
        #storage-explorer .gap-1 { gap: 0.25rem !important; }
        #storage-explorer .gap-1\.5 { gap: 0.375rem !important; }
        #storage-explorer .gap-2 { gap: 0.5rem !important; }
        #storage-explorer .gap-3 { gap: 0.75rem !important; }
        #storage-explorer .gap-4 { gap: 1rem !important; }
        #storage-explorer .space-y-2 > * + * { margin-top: 0.5rem !important; }

        /* --- Width / Height --- */
        #storage-explorer .w-3 { width: 0.75rem !important; }
        #storage-explorer .w-3\.5 { width: 0.875rem !important; }
        #storage-explorer .w-4 { width: 1rem !important; }
        #storage-explorer .w-5 { width: 1.25rem !important; }
        #storage-explorer .w-10 { width: 2.5rem !important; }
        #storage-explorer .w-16 { width: 4rem !important; }
        #storage-explorer .w-full { width: 100% !important; }
        #storage-explorer .h-2 { height: 0.5rem !important; }
        #storage-explorer .h-4 { height: 1rem !important; }
        #storage-explorer .h-5 { height: 1.25rem !important; }
        #storage-explorer .h-full { height: 100% !important; }
        #storage-explorer .min-w-0 { min-width: 0 !important; }
        #storage-explorer .max-w-md { max-width: 28rem !important; }
        #storage-explorer .max-w-full { max-width: 100% !important; }

        /* --- Padding --- */
        #storage-explorer .p-2 { padding: 0.5rem !important; }
        #storage-explorer .p-3 { padding: 0.75rem !important; }
        #storage-explorer .p-4 { padding: 1rem !important; }
        #storage-explorer .p-6 { padding: 1.5rem !important; }
        #storage-explorer .p-8 { padding: 2rem !important; }
        #storage-explorer .px-1\.5 { padding-left: 0.375rem !important; padding-right: 0.375rem !important; }
        #storage-explorer .px-2 { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
        #storage-explorer .px-3 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
        #storage-explorer .px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
        #storage-explorer .py-0\.5 { padding-top: 0.125rem !important; padding-bottom: 0.125rem !important; }
        #storage-explorer .py-1 { padding-top: 0.25rem !important; padding-bottom: 0.25rem !important; }
        #storage-explorer .py-1\.5 { padding-top: 0.375rem !important; padding-bottom: 0.375rem !important; }
        #storage-explorer .py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
        #storage-explorer .py-3 { padding-top: 0.75rem !important; padding-bottom: 0.75rem !important; }
        #storage-explorer .py-4 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
        #storage-explorer .py-8 { padding-top: 2rem !important; padding-bottom: 2rem !important; }
        #storage-explorer .pl-10 { padding-left: 2.5rem !important; }
        #storage-explorer .pr-8 { padding-right: 2rem !important; }

        /* --- Margin --- */
        #storage-explorer .mx-auto { margin-left: auto !important; margin-right: auto !important; }
        #storage-explorer .mx-4 { margin-left: 1rem !important; margin-right: 1rem !important; }
        #storage-explorer .mb-1 { margin-bottom: 0.25rem !important; }
        #storage-explorer .mb-2 { margin-bottom: 0.5rem !important; }
        #storage-explorer .mb-3 { margin-bottom: 0.75rem !important; }
        #storage-explorer .mb-4 { margin-bottom: 1rem !important; }
        #storage-explorer .mt-0\.5 { margin-top: 0.125rem !important; }
        #storage-explorer .mt-1 { margin-top: 0.25rem !important; }
        #storage-explorer .ml-2 { margin-left: 0.5rem !important; }
        #storage-explorer .mr-2 { margin-right: 0.5rem !important; }

        /* --- Typography --- */
        #storage-explorer .text-xs { font-size: 0.75rem !important; line-height: 1rem !important; }
        #storage-explorer .text-sm { font-size: 0.875rem !important; line-height: 1.25rem !important; }
        #storage-explorer .text-lg { font-size: 1.125rem !important; line-height: 1.75rem !important; }
        #storage-explorer .text-xl { font-size: 1.25rem !important; line-height: 1.75rem !important; }
        #storage-explorer .font-medium { font-weight: 500 !important; }
        #storage-explorer .font-semibold { font-weight: 600 !important; }
        #storage-explorer .font-bold { font-weight: 700 !important; }
        #storage-explorer .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important; }
        #storage-explorer .leading-relaxed { line-height: 1.625 !important; }
        #storage-explorer .truncate { overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; }
        #storage-explorer .whitespace-nowrap { white-space: nowrap !important; }
        #storage-explorer .whitespace-pre-wrap { white-space: pre-wrap !important; }
        #storage-explorer .break-words { overflow-wrap: break-word !important; }
        #storage-explorer .text-center { text-align: center !important; }
        #storage-explorer .text-left { text-align: left !important; }

        /* --- Text Colors --- */
        #storage-explorer .text-white { color: #fff !important; }
        #storage-explorer .text-gray-300 { color: #d1d5db !important; }
        #storage-explorer .text-gray-400 { color: #9ca3af !important; }
        #storage-explorer .text-gray-500 { color: #6b7280 !important; }
        #storage-explorer .text-gray-600 { color: #4b5563 !important; }
        #storage-explorer .text-gray-700 { color: #374151 !important; }
        #storage-explorer .text-gray-900 { color: #111827 !important; }
        #storage-explorer .text-blue-600 { color: #2563eb !important; }
        #storage-explorer .text-blue-700 { color: #1d4ed8 !important; }
        #storage-explorer .text-blue-800 { color: #1e40af !important; }
        #storage-explorer .text-green-800 { color: #166534 !important; }
        #storage-explorer .text-red-600 { color: #dc2626 !important; }
        #storage-explorer .text-red-800 { color: #991b1b !important; }
        #storage-explorer .text-amber-500 { color: #f59e0b !important; }
        #storage-explorer .text-purple-500 { color: #a855f7 !important; }
        #storage-explorer .text-orange-500 { color: #f97316 !important; }
        #storage-explorer .text-green-500 { color: #22c55e !important; }
        #storage-explorer .text-blue-500 { color: #3b82f6 !important; }
        #storage-explorer .text-red-500 { color: #ef4444 !important; }
        #storage-explorer .text-yellow-600 { color: #ca8a04 !important; }
        #storage-explorer .text-pink-500 { color: #ec4899 !important; }
        #storage-explorer .text-indigo-500 { color: #6366f1 !important; }

        /* --- Background Colors --- */
        #storage-explorer .bg-white { background-color: #fff !important; }
        #storage-explorer .bg-gray-50 { background-color: #f9fafb !important; }
        #storage-explorer .bg-gray-100 { background-color: #f3f4f6 !important; }
        #storage-explorer .bg-gray-200 { background-color: #e5e7eb !important; }
        #storage-explorer .bg-blue-50 { background-color: #eff6ff !important; }
        #storage-explorer .bg-blue-600 { background-color: #2563eb !important; }
        #storage-explorer .bg-green-50 { background-color: #f0fdf4 !important; }
        #storage-explorer .bg-red-50 { background-color: #fef2f2 !important; }
        #storage-explorer .bg-black\/50 { background-color: rgb(0 0 0 / 0.5) !important; }

        /* --- Border --- */
        #storage-explorer .border { border: 1px solid #e5e7eb !important; }
        #storage-explorer .border-b { border-bottom: 1px solid #e5e7eb !important; border-top: 0 !important; border-left: 0 !important; border-right: 0 !important; }
        #storage-explorer .border-2 { border-width: 2px !important; border-style: solid !important; }
        #storage-explorer .border-dashed { border-style: dashed !important; }
        #storage-explorer .border-gray-200 { border-color: #e5e7eb !important; }
        #storage-explorer .border-gray-300 { border-color: #d1d5db !important; }
        #storage-explorer .border-blue-200 { border-color: #bfdbfe !important; }
        #storage-explorer .border-blue-400 { border-color: #60a5fa !important; }
        #storage-explorer .border-red-300 { border-color: #fca5a5 !important; }
        #storage-explorer .border-green-200 { border-color: #bbf7d0 !important; }
        #storage-explorer .rounded { border-radius: 0.25rem !important; }
        #storage-explorer .rounded-lg { border-radius: 0.5rem !important; }
        #storage-explorer .rounded-xl { border-radius: 0.75rem !important; }
        #storage-explorer .rounded-full { border-radius: 9999px !important; }

        /* --- Shadows --- */
        #storage-explorer .shadow { box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1) !important; }
        #storage-explorer .shadow-lg { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1) !important; }
        #storage-explorer .shadow-xl { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important; }

        /* --- Positioning --- */
        #storage-explorer .fixed { position: fixed !important; }
        #storage-explorer .relative { position: relative !important; }
        #storage-explorer .absolute { position: absolute !important; }
        #storage-explorer .inset-0 { top: 0 !important; right: 0 !important; bottom: 0 !important; left: 0 !important; }
        #storage-explorer .top-4 { top: 1rem !important; }
        #storage-explorer .right-4 { right: 1rem !important; }
        #storage-explorer .top-1\/2 { top: 50% !important; }
        #storage-explorer .left-3 { left: 0.75rem !important; }
        #storage-explorer .right-3 { right: 0.75rem !important; }
        #storage-explorer .-translate-y-1\/2 { transform: translateY(-50%) !important; }
        #storage-explorer .z-40 { z-index: 40 !important; }
        #storage-explorer .z-50 { z-index: 50 !important; }

        /* --- Overflow --- */
        #storage-explorer .overflow-auto { overflow: auto !important; }
        #storage-explorer .overflow-hidden { overflow: hidden !important; }
        #storage-explorer .overflow-y-auto { overflow-y: auto !important; }

        /* --- Opacity --- */
        #storage-explorer .opacity-0 { opacity: 0 !important; }
        #storage-explorer .opacity-25 { opacity: 0.25 !important; }
        #storage-explorer .opacity-60 { opacity: 0.6 !important; }
        #storage-explorer .opacity-75 { opacity: 0.75 !important; }

        /* --- Transitions --- */
        #storage-explorer .transition-colors { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        #storage-explorer .transition-transform { transition-property: transform; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        #storage-explorer .transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        #storage-explorer .transition-opacity { transition-property: opacity; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }

        /* --- Transform --- */
        #storage-explorer .rotate-90 { transform: rotate(90deg) !important; }

        /* --- Misc --- */
        #storage-explorer .outline-none { outline: 2px solid transparent !important; outline-offset: 2px !important; }
        #storage-explorer .cursor-pointer { cursor: pointer !important; }
        #storage-explorer button { cursor: pointer; background: transparent; border: none; padding: 0; font: inherit; color: inherit; }

        /* --- Animation --- */
        @keyframes se-spin { to { transform: rotate(360deg); } }
        #storage-explorer .animate-spin { animation: se-spin 1s linear infinite !important; }

        /* --- Hover States --- */
        #storage-explorer .hover\:bg-gray-50:hover { background-color: #f9fafb !important; }
        #storage-explorer .hover\:bg-gray-100:hover { background-color: #f3f4f6 !important; }
        #storage-explorer .hover\:bg-blue-700:hover { background-color: #1d4ed8 !important; }
        #storage-explorer .hover\:bg-red-50:hover { background-color: #fef2f2 !important; }
        #storage-explorer .hover\:text-blue-600:hover { color: #2563eb !important; }
        #storage-explorer .hover\:text-gray-600:hover { color: #4b5563 !important; }
        #storage-explorer .hover\:opacity-100:hover { opacity: 1 !important; }
        #storage-explorer .group:hover .group-hover\:opacity-100 { opacity: 1 !important; }

        /* --- Focus States --- */
        #storage-explorer .focus\:ring-2:focus { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #3b82f6 !important; }
        #storage-explorer .focus\:ring-blue-500:focus { --tw-ring-color: #3b82f6; }
        #storage-explorer .focus\:border-blue-500:focus { border-color: #3b82f6 !important; }

        /* --- Responsive --- */
        @media (min-width: 640px) {
            #storage-explorer.sm\:p-6, #storage-explorer .sm\:p-6 { padding: 1.5rem !important; }
        }
        @media (min-width: 1024px) {
            #storage-explorer .lg\:flex-row { flex-direction: row !important; }
            #storage-explorer .lg\:w-1\/3 { width: 33.333333% !important; }
            #storage-explorer .lg\:w-2\/3 { width: 66.666667% !important; }
        }

        /* --- Notification animations --- */
        #storage-explorer .notification-enter { animation: se-slideIn 0.3s ease-out; }
        @keyframes se-slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* --- Log content --- */
        #storage-explorer .tree-line { border-left: 1px solid #e5e7eb; }
        #storage-explorer pre.log-content span { display: inline; }

        /* --- Tail preview banner --- */
        #storage-explorer .se-tail-banner {
            background-color: #fef3c7;
            color: #92400e;
            border-bottom: 1px solid #fde68a;
            font-size: 0.875rem;
        }

        /* --- Directory delete button --- */
        #storage-explorer .se-dir-delete-btn {
            padding: 2px;
            border-radius: 4px;
        }
        #storage-explorer .se-dir-delete-btn:hover {
            background-color: #fef2f2;
        }

        /* --- Input reset (Bootstrap overrides) --- */
        #storage-explorer input[type="text"] {
            background-color: #fff !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            width: 100% !important;
            padding: 0.5rem 2rem 0.5rem 2.5rem !important;
        }
        #storage-explorer input[type="text"]:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #3b82f6 !important;
            outline: none !important;
        }
    </style>
@endpush

@section($contentSection)
    @include('storage-explorer::explorer', [
        'deleteEnabled' => $deleteEnabled ?? true,
        'uploadEnabled' => $uploadEnabled ?? true,
        'apiBase' => $apiBase,
    ])
@endsection
