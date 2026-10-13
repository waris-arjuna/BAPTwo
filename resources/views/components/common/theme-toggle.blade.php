<button
    x-data="{ theme: localStorage.getItem('theme') === 'dark' ? 'dark' : 'light' }"
    x-init="
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    "
    @click="
        theme = theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    "
    class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
>
    <!-- Dark Icon -->
    <i x-show="theme === 'dark'" class="fi fi-rr-sun text-base" aria-hidden="true"></i>

    <!-- Light Icon -->
    <i x-show="theme === 'light'" class="fi fi-rr-moon text-base" aria-hidden="true"></i>
</button>
