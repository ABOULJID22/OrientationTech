<script>
    (function () {
        var storedTheme = null;

        try {
            storedTheme = localStorage.getItem('theme');
        } catch (error) {
            storedTheme = null;
        }

        var systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var shouldUseDark = storedTheme === 'dark' || ((storedTheme === null || storedTheme === 'system') && systemPrefersDark);

        document.documentElement.classList.toggle('dark', shouldUseDark);
        document.documentElement.dataset.theme = shouldUseDark ? 'dark' : 'light';
        document.documentElement.style.colorScheme = shouldUseDark ? 'dark' : 'light';
    })();
</script>
