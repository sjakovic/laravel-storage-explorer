{{-- Include this partial when embedding the explorer in your own layout --}}
{{-- It provides the required CSS and JS dependencies --}}
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
</style>
