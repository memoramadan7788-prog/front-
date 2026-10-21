{{-- Apply theme before paint to avoid incorrect-theme flash. --}}
<script>
(function () {
    try {
        var stored = localStorage.getItem('hb_theme') || 'system';
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var dark = stored === 'dark' || (stored === 'system' && prefersDark);
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.dataset.theme = stored;
    } catch (e) {
        document.documentElement.classList.remove('dark');
        document.documentElement.dataset.theme = 'system';
    }
})();
</script>
