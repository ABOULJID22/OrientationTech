<!-- <script>
document.addEventListener('DOMContentLoaded', function () {
    const notifBtn = document.querySelector('.fi-topbar-database-notifications-btn');
    const notifDropdown = document.querySelector('.fi-dropdown-panel.notifications-list');

    if (!notifBtn || !notifDropdown) return;

    // Toggle dropdown
    notifBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown if click outside
    document.addEventListener('click', function() {
        notifDropdown.style.display = 'none';
    });

    // Prevent closing when clicking inside dropdown
    notifDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>
 -->