  // Hamburger Menu Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.hamburger');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            const body = document.body;
            const mainContent = document.querySelector('.main-content');

            // Fungsi untuk membuka sidebar
            function openSidebar() {
                hamburger.classList.add('active');
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('active');
                body.classList.add('sidebar-open');
                mainContent.classList.add('sidebar-open');
            }

            // Fungsi untuk menutup sidebar
            function closeSidebar() {
                hamburger.classList.remove('active');
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                body.classList.remove('sidebar-open');
                mainContent.classList.remove('sidebar-open');
            }

            // Event listener untuk hamburger button
            if (hamburger) {
                hamburger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    if (sidebar.classList.contains('active')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            // Event listener untuk overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }

            // Menutup sidebar ketika resize ke desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeSidebar();
                }
            });

            // ESC key untuk menutup sidebar
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    closeSidebar();
                }
            });
        });