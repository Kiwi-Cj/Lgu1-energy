<header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-gray-900 shadow-md">
    <div class="flex items-center space-x-4">
        <span class="text-xl font-bold text-gray-800 dark:text-white">qc-admin</span>
    </div>
    <div class="flex items-center space-x-4">
        <!-- Notification Icon -->
        <button class="relative focus:outline-none" aria-label="Notifications">
            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 7.165 6 9.388 6 12v2.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="absolute top-0 right-0 inline-block w-2 h-2 bg-red-600 rounded-full"></span>
        </button>
        <!-- Dark Mode Toggle -->
        <button id="darkModeToggle" class="focus:outline-none" aria-label="Toggle Dark Mode">
            <svg id="darkModeIcon" class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
            </svg>
        </button>
    </div>
</header>
<script>
    // Dark mode toggle logic
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('darkModeToggle');
        toggle.addEventListener('click', function () {
            document.documentElement.classList.toggle('dark');
            // Optionally, save preference to localStorage
            if(document.documentElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
        // On load, set theme from localStorage
        if(localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    });
</script>
<?php /**PATH C:\xampp\htdocs\lgu-energy\resources\views/layouts/qc-admin-header.blade.php ENDPATH**/ ?>