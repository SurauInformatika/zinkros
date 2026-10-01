document.addEventListener('alpine:init', () => {});

document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const sidebar = document.getElementById('sidebar');
    const sidebarDesktop = document.getElementById('sidebar-desktop');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const mainContent = document.getElementById('main-content');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const closeMobileSidebar = document.getElementById('close-mobile-sidebar');

    // Dark mode
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        html.classList.add('dark');
    }

    window.toggleDarkMode = () => {
        html.classList.toggle('dark');
        localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    };

    // Sidebar collapse (desktop)
    const savedSidebar = localStorage.getItem('sidebar-collapsed');
    if (savedSidebar === 'true') {
        document.body.classList.add('sidebar-collapsed');
        document.body.classList.remove('sidebar-expanded');
        if (sidebarDesktop) {
            sidebarDesktop.classList.remove('w-64');
            sidebarDesktop.classList.add('w-[68px]');
        }
        if (mainContent) {
            mainContent.classList.remove('md:ml-64');
            mainContent.classList.add('md:ml-[68px]');
        }
    } else {
        document.body.classList.add('sidebar-expanded');
    }

    window.toggleSidebarGroup = (btn) => {
        const group = btn.closest('.sidebar-group');
        if (!group) return;
        const menu = group.querySelector('[data-group-menu]');
        const chevron = group.querySelector('[data-group-chevron]');
        if (!menu) return;
        menu.classList.toggle('hidden');
        if (chevron) chevron.classList.toggle('rotate-180');
    };

    window.toggleSidebar = () => {
        const collapsed = document.body.classList.toggle('sidebar-collapsed');
        document.body.classList.toggle('sidebar-expanded', !collapsed);
        localStorage.setItem('sidebar-collapsed', collapsed);
        if (sidebarDesktop) {
            sidebarDesktop.classList.toggle('w-64', !collapsed);
            sidebarDesktop.classList.toggle('w-[68px]', collapsed);
        }
        if (mainContent) {
            mainContent.classList.toggle('md:ml-64', !collapsed);
            mainContent.classList.toggle('md:ml-[68px]', collapsed);
        }
    };

    // Mobile sidebar
    window.openMobileSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        sidebarBackdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeMobileSidebarFn = () => {
        sidebar.classList.add('-translate-x-full');
        sidebarBackdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', window.closeMobileSidebarFn);
    }
});
