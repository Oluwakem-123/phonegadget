    </main>

    <!-- Desktop Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12 pb-16 md:pb-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-1">
                    <a href="<?php echo base_url(); ?>" class="text-xl font-bold text-primary flex items-center gap-2 mb-4">
                        <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        PhoneHub
                    </a>
                    <p class="text-sm text-gray-500">Your trusted marketplace for the latest smartphones and accessories.</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">Shop</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="<?php echo base_url('phones/'); ?>" class="hover:text-secondary">All Phones</a></li>
                        <li><a href="<?php echo base_url('phones/?latest=1'); ?>" class="hover:text-secondary">New Releases</a></li>
                        <li><a href="<?php echo base_url('phones/?deals=1'); ?>" class="hover:text-secondary">Deals & Offers</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">Support</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="#" class="hover:text-secondary">Contact Us</a></li>
                        <li><a href="#" class="hover:text-secondary">FAQs</a></li>
                        <li><a href="#" class="hover:text-secondary">Return Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">Account</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="<?php echo base_url('account/'); ?>" class="hover:text-secondary">My Profile</a></li>
                        <li><a href="<?php echo base_url('orders/'); ?>" class="hover:text-secondary">Order History</a></li>
                        <li><a href="<?php echo base_url('auth/login.php'); ?>" class="hover:text-secondary">Login / Register</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-200 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-sm text-gray-400">&copy; <?php echo date('Y'); ?> PhoneHub Marketplace. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Fixed Mobile Bottom Navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 flex justify-around items-center h-16 pb-safe">
        <a href="<?php echo base_url(); ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-secondary">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="<?php echo base_url('phones/'); ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-secondary">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span class="text-[10px] font-medium">Phones</span>
        </a>
        <a href="<?php echo base_url('phones/?latest=1'); ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-secondary">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            <span class="text-[10px] font-medium">Latest</span>
        </a>
        <a href="<?php echo base_url('cart/'); ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-secondary relative">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="text-[10px] font-medium">Cart</span>
            <?php $cart_count = get_cart_count(); if ($cart_count > 0): ?>
            <span class="absolute top-1 right-2 bg-error text-white text-[9px] font-bold rounded-full h-3.5 w-3.5 flex items-center justify-center"><?php echo $cart_count > 99 ? '99+' : $cart_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo is_logged_in() ? base_url('account/') : base_url('auth/login.php'); ?>" class="flex flex-col items-center justify-center w-full h-full text-gray-500 hover:text-secondary">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px] font-medium">Account</span>
        </a>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="custom-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeModal()"></div>
            <!-- Modal panel -->
            <div class="relative inline-block w-full max-w-sm p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8 sm:w-full sm:p-6">
                <div class="flex flex-col items-center text-center">
                    <div id="modal-icon-container" class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-gray-100 rounded-full text-gray-600">
                        <!-- Icon injected by JS -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Confirm Action</h3>
                </div>
                <div class="mt-6 sm:flex sm:flex-row-reverse sm:gap-3">
                    <button type="button" id="modal-btn-confirm" class="inline-flex justify-center w-full px-4 py-3 text-base font-bold text-white bg-error border border-transparent rounded-xl shadow-sm hover:bg-red-700 focus:outline-none sm:text-sm mb-3 sm:mb-0">
                        Confirm
                    </button>
                    <button type="button" id="modal-btn-cancel" class="inline-flex justify-center w-full px-4 py-3 text-base font-bold text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none sm:text-sm" onclick="closeModal()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-full max-w-xs md:max-w-sm pointer-events-none px-4 md:px-0">
        <?php
            // Render flash messages via PHP so they appear instantly on page load
            $flashes = [
                'success' => ['icon' => 'M5 13l4 4L19 7', 'color' => 'green'],
                'error' => ['icon' => 'M6 18L18 6M6 6l12 12', 'color' => 'red'],
                'warning' => ['icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'yellow'],
                'info' => ['icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue']
            ];
            
            foreach ($flashes as $type => $config) {
                $session_key = 'flash_' . $type;
                if (isset($_SESSION[$session_key])) {
                    $msg = h($_SESSION[$session_key]);
                    $color = $config['color'];
                    echo "
                    <div class='toast-message bg-white border-l-4 border-{$color}-500 shadow-lg rounded-r-lg p-4 flex items-start gap-3 pointer-events-auto transform transition-all duration-300 translate-x-0'>
                        <div class='flex-shrink-0 text-{$color}-500'>
                            <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='{$config['icon']}'></path></svg>
                        </div>
                        <div class='flex-1 text-sm font-medium text-gray-900 mt-0.5'>{$msg}</div>
                        <button type='button' class='flex-shrink-0 text-gray-400 hover:text-gray-600 focus:outline-none' onclick='this.closest(\".toast-message\").remove()'>
                            <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path></svg>
                        </button>
                    </div>";
                    unset($_SESSION[$session_key]);
                }
            }
        ?>
    </div>

    <!-- Main JavaScript -->
    <script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
    <script>
        // Inline JS for Sidebar functionality (in a real app this goes to main.js)
        document.addEventListener('DOMContentLoaded', () => {
            const btnOpen = document.getElementById('mobile-menu-button');
            const btnClose = document.getElementById('mobile-menu-close');
            const sidebar = document.getElementById('mobile-sidebar');
            const sidebarContent = document.getElementById('mobile-sidebar-content');

            if(btnOpen && sidebar) {
                btnOpen.addEventListener('click', () => {
                    sidebar.classList.remove('hidden');
                    // Small delay to allow display block to apply before animating transform
                    setTimeout(() => {
                        sidebarContent.classList.remove('-translate-x-full');
                    }, 10);
                });

                const closeSidebar = () => {
                    sidebarContent.classList.add('-translate-x-full');
                    setTimeout(() => {
                        sidebar.classList.add('hidden');
                    }, 300); // Matches transition duration
                };

                btnClose.addEventListener('click', closeSidebar);
                
                // Close when clicking outside
                sidebar.addEventListener('click', (e) => {
                    if (e.target === sidebar) {
                        closeSidebar();
                    }
                });
            }
        });
    </script>
</body>
</html>
