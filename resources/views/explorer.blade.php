<div
    id="storage-explorer"
    x-data="storageExplorer()"
    x-init="init()"
    x-cloak
    class="max-w-screen-2xl mx-auto p-4 sm:p-6"
>
    {{-- Notifications --}}
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="(notification, index) in notifications" :key="notification.id">
            <div
                class="notification-enter flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
                :class="{
                    'bg-green-50 text-green-800 border border-green-200': notification.type === 'success',
                    'bg-red-50 text-red-800 border border-red-200': notification.type === 'error',
                    'bg-blue-50 text-blue-800 border border-blue-200': notification.type === 'info',
                }"
            >
                <span x-text="notification.message"></span>
                <button @click="notifications.splice(index, 1)" class="ml-2 opacity-60 hover:opacity-100">&times;</button>
            </div>
        </template>
    </div>

    {{-- Header --}}
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">Storage Explorer</h1>
            <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded font-mono" x-text="currentDisk"></span>
        </div>
        <div class="flex items-center gap-2">
            <button
                @click="refresh()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                :disabled="loading"
            >
                <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
            @if($uploadEnabled ?? true)
            <button
                @click="createFolder()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                New Folder
            </button>
            <button
                @click="showUploadModal = true"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Upload
            </button>
            @endif
        </div>
    </div>

    {{-- Breadcrumbs --}}
    <div class="mb-4 flex items-center gap-1 text-sm text-gray-500 bg-white px-3 py-2 rounded-lg border border-gray-200">
        <button @click="navigateTo('')" class="hover:text-blue-600 font-medium">Root</button>
        <template x-for="(crumb, i) in breadcrumbs" :key="crumb.path">
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <button @click="navigateTo(crumb.path)" class="hover:text-blue-600" x-text="crumb.name"></button>
            </span>
        </template>
    </div>

    {{-- Main layout --}}
    <div class="flex flex-col lg:flex-row gap-4">
        {{-- Left panel: tree --}}
        <div class="w-full lg:w-1/3 bg-white rounded-lg border border-gray-200 flex flex-col" style="max-height: 80vh;">
            {{-- Search --}}
            <div class="p-3 border-b border-gray-200">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="text"
                        x-model="searchQuery"
                        @input.debounce.300ms="search()"
                        placeholder="Search files..."
                        class="w-full pl-10 pr-8 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    >
                    <button
                        x-show="searchQuery.length > 0"
                        @click="clearSearch()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >&times;</button>
                </div>
            </div>

            {{-- Tree / Search results --}}
            <div class="flex-1 overflow-y-auto p-2">
                {{-- Search results --}}
                <template x-if="isSearching">
                    <div>
                        <div class="px-2 py-1 text-xs text-gray-500 font-medium mb-1">
                            Search results (<span x-text="searchResults.length"></span>)
                        </div>
                        <template x-for="result in searchResults" :key="result.path">
                            <button
                                @click="result.is_directory ? navigateTo(result.path) : selectFile(result.path)"
                                class="w-full flex items-center gap-2 px-2 py-1.5 text-sm rounded hover:bg-gray-100 text-left"
                                :class="{ 'bg-blue-50': selectedFile === result.path }"
                            >
                                <span x-html="getIcon(result)"></span>
                                <span class="truncate flex-1">
                                    <span class="font-medium" x-text="result.name"></span>
                                    <span class="text-xs text-gray-400 block truncate" x-text="result.path"></span>
                                </span>
                                <span class="text-xs text-gray-400 whitespace-nowrap" x-text="result.size_formatted"></span>
                            </button>
                        </template>
                        <div x-show="searchResults.length === 0" class="px-2 py-4 text-sm text-gray-400 text-center">
                            No results found.
                        </div>
                    </div>
                </template>

                {{-- Directory tree --}}
                <template x-if="!isSearching">
                    <div>
                        <template x-for="node in visibleNodes()" :key="node.path || '__root__' + node.name">
                            <div class="relative group">
                                <button
                                    @click="node.is_directory ? toggleDir(node.path) : selectFile(node.path)"
                                    class="w-full flex items-center gap-1.5 py-1 px-2 text-sm rounded hover:bg-gray-100 text-left"
                                    :class="{
                                        'bg-blue-50 text-blue-700': selectedFile === node.path,
                                    }"
                                    :style="{ paddingLeft: (node.depth * 16 + 8) + 'px' }"
                                >
                                    {{-- Expand/collapse chevron for directories --}}
                                    <template x-if="node.is_directory">
                                        <svg
                                            class="w-3.5 h-3.5 text-gray-400 transition-transform flex-shrink-0"
                                            :class="{ 'rotate-90': expandedDirs.has(node.path) }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </template>
                                    <template x-if="!node.is_directory">
                                        <span class="w-3.5 flex-shrink-0"></span>
                                    </template>

                                    <span x-html="getIcon(node)" class="flex-shrink-0"></span>
                                    <span class="truncate flex-1" x-text="node.name"></span>
                                    <span
                                        x-show="!node.is_directory"
                                        class="text-xs text-gray-400 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity"
                                        x-text="node.size_formatted"
                                    ></span>
                                </button>
                                @if($deleteEnabled ?? true)
                                <template x-if="node.is_directory">
                                    <span
                                        @click.stop="deleteDirectory(node.path, node.name)"
                                        class="se-dir-delete-btn opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                                        :style="{ position: 'absolute', right: '8px', top: '50%', transform: 'translateY(-50%)', cursor: 'pointer' }"
                                        title="Delete folder"
                                    >
                                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </span>
                                </template>
                                @endif
                            </div>
                        </template>

                        <div x-show="visibleNodes().length === 0 && !loading" class="px-2 py-8 text-sm text-gray-400 text-center">
                            This directory is empty.
                        </div>
                    </div>
                </template>

                {{-- Loading --}}
                <div x-show="loading" class="flex items-center justify-center py-8">
                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Right panel: preview --}}
        <div class="w-full lg:w-2/3 bg-white rounded-lg border border-gray-200 flex flex-col" style="max-height: 80vh;">
            {{-- No file selected --}}
            <template x-if="!previewData">
                <div class="flex-1 flex items-center justify-center text-gray-400">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm">Select a file to preview</p>
                    </div>
                </div>
            </template>

            {{-- File preview --}}
            <template x-if="previewData">
                <div class="flex flex-col h-full">
                    {{-- Preview header --}}
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 truncate" x-text="previewData.info.name"></h2>
                            <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5">
                                <span x-text="previewData.info.size_formatted"></span>
                                <span x-text="formatDate(previewData.info.last_modified)"></span>
                                <span class="px-1.5 py-0.5 bg-gray-100 rounded" x-text="previewData.info.file_type"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button
                                @click="downloadFile(selectedFile)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download
                            </button>
                            @if($deleteEnabled ?? true)
                            <button
                                @click="deleteFile(selectedFile)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Tail preview banner --}}
                    <template x-if="previewData.tailPreview">
                        <div class="se-tail-banner flex items-center gap-2 px-4 py-2 flex-shrink-0">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm">
                                Large file (<span x-text="previewData.tailPreview.total_size_formatted"></span>) — showing last <span x-text="previewData.tailPreview.tail_size_formatted"></span>. Use Download for full file.
                            </span>
                        </div>
                    </template>

                    {{-- Preview content --}}
                    <div class="flex-1 overflow-auto p-4">
                        {{-- Image preview --}}
                        <template x-if="previewData.info.file_type === 'image' && previewData.content">
                            <div class="flex items-center justify-center">
                                <img :src="previewData.content" class="max-w-full max-h-[60vh] rounded shadow" :alt="previewData.info.name">
                            </div>
                        </template>

                        {{-- Log preview (highlighted HTML) --}}
                        <template x-if="previewData.highlighted && previewData.content">
                            <pre class="log-content text-xs leading-relaxed font-mono bg-gray-50 p-4 rounded border overflow-auto whitespace-pre-wrap break-words" x-html="previewData.content"></pre>
                        </template>

                        {{-- Text / Code preview --}}
                        <template x-if="!previewData.highlighted && previewData.info.file_type !== 'image' && previewData.content && previewData.previewable">
                            <pre class="text-xs leading-relaxed font-mono bg-gray-50 p-4 rounded border overflow-auto whitespace-pre-wrap break-words" x-text="previewData.content"></pre>
                        </template>

                        {{-- Not previewable --}}
                        <template x-if="!previewData.previewable">
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <div class="text-center">
                                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <p class="text-sm mb-2">Preview not available for this file type</p>
                                    <p class="text-xs" x-text="previewData.info.size_formatted"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Upload Modal --}}
    <div
        x-show="showUploadModal"
        x-cloak
        class="fixed inset-0 z-40 flex items-center justify-center"
    >
        <div class="absolute inset-0 bg-black/50" @click="showUploadModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6" @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload File</h3>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Directory</label>
                <input
                    type="text"
                    x-model="uploadDir"
                    placeholder="/ (root)"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                >
            </div>

            <div
                class="mb-4 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center"
                :class="{ 'border-blue-400 bg-blue-50': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="isDragging = false; handleDrop($event)"
            >
                <svg class="w-10 h-10 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm text-gray-600 mb-2">Drag & drop a file here, or</p>
                <label class="inline-flex items-center px-4 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                    Browse files
                    <input type="file" class="hidden" @change="handleFileSelect($event)">
                </label>
            </div>

            <div x-show="uploadProgress !== null" class="mb-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all" :style="{ width: uploadProgress + '%' }"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1 text-center" x-text="uploadProgress + '%'"></p>
            </div>

            <div class="flex justify-end gap-2">
                <button
                    @click="showUploadModal = false"
                    class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function storageExplorer() {
    return {
        // State
        nodes: [],
        expandedDirs: new Set(),
        selectedFile: null,
        previewData: null,
        searchQuery: '',
        searchResults: [],
        isSearching: false,
        loading: false,
        notifications: [],
        notificationId: 0,
        currentDisk: '{{ config("storage-explorer.disk", "local") }}',

        // Upload state
        showUploadModal: false,
        uploadDir: '',
        uploadProgress: null,
        isDragging: false,

        // Config
        apiBase: '{{ $apiBase }}',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',

        // Breadcrumbs
        breadcrumbs: [],

        // Lifecycle
        async init() {
            await this.loadDirectory('');
        },

        // Tree operations
        async loadDirectory(path) {
            this.loading = true;
            try {
                const res = await fetch(`${this.apiBase}/api/tree?path=${encodeURIComponent(path)}`);
                const data = await res.json();
                if (!data.success) {
                    this.notify('error', data.message || 'Failed to load directory.');
                    return;
                }

                // Remove old children of this path
                this.nodes = this.nodes.filter(n => this.getParentPath(n.path) !== path || path === '' && n.depth !== 0 ? n.depth === 0 ? false : this.getParentPath(n.path) !== path : false);

                // Actually, let's use a cleaner approach - rebuild children
                const depth = path === '' ? 0 : path.split('/').length;
                // Remove existing children at this depth with this parent
                this.nodes = this.nodes.filter(n => {
                    if (n.parentPath === path) return false;
                    return true;
                });

                // Add new children
                const children = data.data.map(item => ({
                    ...item,
                    depth: depth,
                    parentPath: path,
                }));

                this.nodes.push(...children);
                this.sortNodes();
            } catch (e) {
                this.notify('error', 'Failed to load directory.');
            } finally {
                this.loading = false;
            }
        },

        sortNodes() {
            this.nodes.sort((a, b) => {
                const aPath = a.path || '';
                const bPath = b.path || '';
                return aPath.localeCompare(bPath);
            });
        },

        async toggleDir(path) {
            if (this.expandedDirs.has(path)) {
                this.expandedDirs.delete(path);
                // Remove all descendants
                this.removeDescendants(path);
            } else {
                this.expandedDirs.add(path);
                await this.loadDirectory(path);
            }
            this.updateBreadcrumbs(path);
        },

        removeDescendants(path) {
            const prefix = path + '/';
            // Remove nodes whose path starts with this prefix
            this.nodes = this.nodes.filter(n => !n.path.startsWith(prefix));
            // Also collapse any expanded dirs under this path
            for (const dir of this.expandedDirs) {
                if (dir.startsWith(prefix)) {
                    this.expandedDirs.delete(dir);
                }
            }
        },

        navigateTo(path) {
            if (path === '') {
                // Collapse all and reload root
                this.expandedDirs.clear();
                this.nodes = [];
                this.breadcrumbs = [];
                this.loadDirectory('');
                return;
            }

            // Expand all directories in the path
            const parts = path.split('/');
            let current = '';
            for (let i = 0; i < parts.length; i++) {
                current = i === 0 ? parts[i] : current + '/' + parts[i];
                if (!this.expandedDirs.has(current)) {
                    this.expandedDirs.add(current);
                    this.loadDirectory(current);
                }
            }
            this.updateBreadcrumbs(path);
        },

        updateBreadcrumbs(path) {
            if (!path) {
                this.breadcrumbs = [];
                return;
            }
            const parts = path.split('/');
            let current = '';
            this.breadcrumbs = parts.map((part, i) => {
                current = i === 0 ? part : current + '/' + part;
                return { name: part, path: current };
            });
        },

        visibleNodes() {
            return this.nodes.filter(node => {
                if (node.depth === 0) return true;
                // Check if all ancestors are expanded
                return this.expandedDirs.has(node.parentPath);
            });
        },

        getParentPath(path) {
            const parts = path.split('/');
            parts.pop();
            return parts.join('/');
        },

        // File operations
        async selectFile(path) {
            this.selectedFile = path;
            this.previewData = null;
            this.loading = true;

            try {
                const res = await fetch(`${this.apiBase}/api/preview?path=${encodeURIComponent(path)}`);
                const data = await res.json();
                if (data.success) {
                    this.previewData = data.data;
                } else {
                    this.notify('error', data.message || 'Failed to load preview.');
                }
            } catch (e) {
                this.notify('error', 'Failed to load preview.');
            } finally {
                this.loading = false;
            }

            this.updateBreadcrumbs(this.getParentPath(path));
        },

        async downloadFile(path) {
            try {
                const res = await fetch(`${this.apiBase}/api/download-url?path=${encodeURIComponent(path)}`);
                const data = await res.json();
                if (data.success) {
                    window.open(data.url, '_blank');
                } else {
                    this.notify('error', data.message || 'Failed to generate download URL.');
                }
            } catch (e) {
                this.notify('error', 'Failed to download file.');
            }
        },

        async deleteFile(path) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            try {
                const res = await fetch(`${this.apiBase}/file`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ path: path, is_directory: false }),
                });
                const data = await res.json();
                if (data.success) {
                    this.nodes = this.nodes.filter(n => n.path !== path);
                    if (this.selectedFile === path) {
                        this.selectedFile = null;
                        this.previewData = null;
                    }
                    this.notify('success', 'File deleted successfully.');
                } else {
                    this.notify('error', data.message || 'Failed to delete file.');
                }
            } catch (e) {
                this.notify('error', 'Failed to delete file.');
            }
        },

        async deleteDirectory(path, name) {
            if (!confirm(`Are you sure you want to delete the folder "${name}" and all its contents?`)) return;

            try {
                const res = await fetch(`${this.apiBase}/file`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ path: path, is_directory: true }),
                });
                const data = await res.json();
                if (data.success) {
                    // Remove the directory node and all descendants
                    const prefix = path + '/';
                    this.nodes = this.nodes.filter(n => n.path !== path && !n.path.startsWith(prefix));
                    // Clean up expanded dirs
                    this.expandedDirs.delete(path);
                    for (const dir of this.expandedDirs) {
                        if (dir.startsWith(prefix)) {
                            this.expandedDirs.delete(dir);
                        }
                    }
                    this.notify('success', 'Folder deleted successfully.');
                } else {
                    this.notify('error', data.message || 'Failed to delete folder.');
                }
            } catch (e) {
                this.notify('error', 'Failed to delete folder.');
            }
        },

        // Upload
        handleDrop(event) {
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
        },

        handleFileSelect(event) {
            const files = event.target.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
            event.target.value = '';
        },

        async uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('directory', this.uploadDir);

            this.uploadProgress = 0;

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', `${this.apiBase}/upload`);
                xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    this.uploadProgress = null;
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            this.notify('success', data.message);
                            this.showUploadModal = false;
                            // Refresh the target directory
                            const dir = this.uploadDir || '';
                            if (this.expandedDirs.has(dir) || dir === '') {
                                this.loadDirectory(dir);
                            }
                        } else {
                            this.notify('error', data.message || 'Upload failed.');
                        }
                    } catch (e) {
                        this.notify('error', 'Upload failed.');
                    }
                };

                xhr.onerror = () => {
                    this.uploadProgress = null;
                    this.notify('error', 'Upload failed.');
                };

                xhr.send(formData);
            } catch (e) {
                this.uploadProgress = null;
                this.notify('error', 'Upload failed.');
            }
        },

        // Create folder
        async createFolder() {
            const name = prompt('Enter folder name:');
            if (!name || !name.trim()) return;

            const folderName = name.trim();
            const currentDir = this.breadcrumbs.length > 0
                ? this.breadcrumbs[this.breadcrumbs.length - 1].path
                : '';
            const path = currentDir ? currentDir + '/' + folderName : folderName;

            try {
                const res = await fetch(`${this.apiBase}/directory`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ path: path }),
                });
                const data = await res.json();
                if (data.success) {
                    this.notify('success', data.message);
                    // Refresh the parent directory
                    if (this.expandedDirs.has(currentDir) || currentDir === '') {
                        await this.loadDirectory(currentDir);
                    }
                } else {
                    this.notify('error', data.message || 'Failed to create folder.');
                }
            } catch (e) {
                this.notify('error', 'Failed to create folder.');
            }
        },

        // Search
        async search() {
            if (this.searchQuery.length < 2) {
                this.isSearching = false;
                this.searchResults = [];
                return;
            }

            this.isSearching = true;

            try {
                const res = await fetch(`${this.apiBase}/api/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await res.json();
                if (data.success) {
                    this.searchResults = data.data;
                }
            } catch (e) {
                this.notify('error', 'Search failed.');
            }
        },

        clearSearch() {
            this.searchQuery = '';
            this.isSearching = false;
            this.searchResults = [];
        },

        async refresh() {
            this.nodes = [];
            this.expandedDirs.clear();
            this.selectedFile = null;
            this.previewData = null;
            this.breadcrumbs = [];
            await this.loadDirectory('');
        },

        // Notifications
        notify(type, message) {
            const id = ++this.notificationId;
            this.notifications.push({ id, type, message });
            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }, 4000);
        },

        // Helpers
        formatDate(timestamp) {
            if (!timestamp) return '';
            return new Date(timestamp * 1000).toLocaleString();
        },

        getIcon(node) {
            if (node.is_directory) {
                return '<svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>';
            }

            const colors = {
                'image': 'text-purple-500',
                'log': 'text-orange-500',
                'code': 'text-green-500',
                'text': 'text-blue-500',
                'pdf': 'text-red-500',
                'archive': 'text-yellow-600',
                'video': 'text-pink-500',
                'audio': 'text-indigo-500',
                'unknown': 'text-gray-400',
            };

            const color = colors[node.file_type] || 'text-gray-400';
            return `<svg class="w-4 h-4 ${color}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
        },
    };
}
</script>
