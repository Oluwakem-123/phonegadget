    </main>

    <script>
        // Admin Mobile Sidebar Toggle
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('admin-sidebar-overlay');
        const mobileBtn = document.getElementById('admin-mobile-menu-btn');

        if (mobileBtn && sidebar && overlay) {
            mobileBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        }
        
        // Confirmation handler
        function confirmAction(message, formId) {
            if (confirm(message)) {
                document.getElementById(formId).submit();
            }
        }
    </script>
</body>
</html>
